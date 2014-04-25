{include file="points.lock.tpl"}
{* http://jsfiddle.net/BdVB9/402/ *}
<h2>Seznam studentů</h2>
{if $studentList}
<p>
    V tabulce se můžete navigovat šipkami. Jakákoliv změna bodového hodnocení se okamžitě zapisuje
    do databáze. Kromě standardních bodových hodnocení lze do buňky zapsat i hodnotu "<strong>x</strong>"
    označující omluvu z testu a hodnotu "<strong>c</strong>" označující opis.
</p>
<table id="navigate" class="pointtable" border="1">
    <tr>
        <th class="name" style="width: 8em; text-align: left;"
                ><a href="?act=edit,points,{$lecture.id}&type=lec&order=2">Jméno</a
                >/<a href="?act=edit,points,{$lecture.id}&type=lec&order=3">login</a></th>
        <th class="name" style="width: 5em;">ID</th>
        <th class="name" style="width: 5em;">Skupina</th>
        {section name=subtaskPos loop=$subtaskList}
            <th style="width: 5ex;"><img src="throt.php?text={$subtaskList[subtaskPos].title}"
                                         title="{$subtaskList[subtaskPos].title}"
                                         alt="{$subtaskList[subtaskPos].title}"></th>
        {/section}
    </tr>
{section name=studentPos loop=$studentList}
    <tr {if $smarty.section.studentPos.iteration is even}class="rowA"{else}class="rowB"{/if}>
        <td class="name">{if $order == 3}{$studentList[studentPos].login}{else}{$studentList[studentPos].surname}&nbsp;{$studentList[studentPos].firstname}{/if}</td>
        <td class="name center">{$studentList[studentPos].dbid}</td>
        <td class="name center">{$studentList[studentPos].yearno}/{$studentList[studentPos].groupno}</td>
    {section name=subtaskPos loop=$subtaskList}
            {if $smarty.section.studentPos.iteration is even}
            <td class="subtskA">
            {else}
            <td class="subtskB">
            {/if}
            <input type="text" maxlength="5" class="pointinput"
                   name="points[{$studentList[studentPos].dbid}][{$subtaskList[subtaskPos].id}]"
                   value="{$studentList[studentPos].subpoints[subtaskPos].points}"></td>
        {/section}
    </tr>
    {/section}
</table>
<script src="js/jquery.js"></script>
<script>
{literal}
    var active = 0;
    var activeScrollTop = 0;
    //$('#navigate td input').each(function(idx){$(this).html(idx);});

    $(document).ready ( function () {
        rePosition();
    });

    $(document).keydown(function (e) {
        reCalculate(e);
        rePosition();
        //return false;
    });

    // handle mouse click on the input element
    $('input').click ( function ( event ) {
        // find the first input element in the table
        firstElement = $(this).closest('table').find('input');
        // get the position index of the clicked element
        active = firstElement.index(this);
        // remember the top of the clicked element so that the whole window will not scroll
        // away when an input has been clicked
        activeScrollTop = $(this).offset().top;
        //alert('activeScrollTop =' + activeScrollTop + '\nwindow =' + $(window).offset() );
        rePosition();
    });


    function reCalculate(e) {
        var rows = $('#navigate tr').length-1;
        var columns = $('#navigate tr:eq(1) td input').length;
        //alert ( 'e.keyCode = ' + e.keyCode + '\n' + 'Rows x cols = ' + rows + 'x' + columns );
        //alert ( 'active = ' + active + '\n' + 'rows x cols = ' + rows + 'x' + columns );

        if (e.keyCode == 37) { //move left or wrap
            active = (active > 0) ? active - 1 : active;
        }
        if (e.keyCode == 38) { // move up
            active = (active - columns >= 0) ? active - columns : active;
        }
        if (e.keyCode == 39) { // move right or wrap
            active = (active < (columns * rows) - 1) ? active + 1 : active;
        }
        if (e.keyCode == 40) { // move down
            active = (active + columns < (rows * columns) - 1) ? active + columns : active;
        }
    }

    // focus the active element
    function rePosition() {
        $('.active').removeClass('active');
        $('#navigate tr td input').eq(active).focus();
        $('#navigate tr td input').eq(active).addClass('active');
        //scrollInView();
    }

    // scroll the active element so that it is visible in the viewport
    function scrollInView() {
        var target = $('#navigate tr td input:eq(' + active + ')');
        if (target.length) {
            var top = target.offset().top;
            alert('top = ' + top + '\nnew body scrollTop = ' + (top-100));

            $('html,body').stop().animate({
                scrollTop: top - 100
            }, 400);
            return false;
        }
        return true;
    }

    // ----- AJAX input of points
    $('.pointinput').change ( function ( event ) {
        var params = {};
        var element = this;
        params[event.target.name] = event.target.value;
        //alert ( 'point input change handler on ' + params );
        $.post ( 'submitpoints.php', params, function ( data ) {
            if ( data.status == 0 )
            {
                $(element).addClass('edit_success');
            }
            else if ( data.status == 1)
            {
                $(element).addClass('edit_fail');
                $(element).attr('title',data.message);
                alert ( 'Body nelze zapsat: ' + data.message );
            }
            //$('.result').html(data)
        }, 'json').fail ( function ( request, textStatus, errorThrown ) {
            alert ( 'Body nelze zapsat, server odpověděl chybovým hlášením:\n' + request.status + ' - ' + request.statusText );
        });
    });
{/literal}
</script>
    {else}
<p>
    Tento předmět ještě nemá přiřazené žádné studenty.
</p>
{/if}

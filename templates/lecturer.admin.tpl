<p>
V položce "<strong>Učí</strong>" zaškrtněte učitele, kteří učí předmět {$lecture.code} v tomto semestru.
</p>
<p>
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
    <tr class="newobject">
        <td colspan="4">Přidat dalšího učitele</td>
        <td width="32" class="smaller" valign="middle"
        ><a href="?act=edit,lecturer,0"><img src="images/famfamfam/application_add.png" title="nový učitel" alt="[nový učitel]"
                                             width="16" height="16"></a></td>
    </tr>
    <tr class="newobject">
        <td colspan="4">Kopírovat přiřazení učitelů z minulého semestru</td>
        <td width="32" class="smaller" valign="middle"
        ><a><img id="copyactive" src="images/famfamfam/application_double.png" title="kopírovat" alt="[kopie]"
                                             width="16" height="16"></a></td>
    </tr>
    <tr>
        <th style="text-align: center;">Učí</th>
        <th style="text-align: left;">Jméno</th>
        <th style="text-align: left;">Místnost</th>
        <th style="text-align: left;">E-mail</th>
        <th>&nbsp;</th>
    </tr>
{section name=aId loop=$lecturerList}
    {if $smarty.section.aId.iteration is even}
        <tr class="rowA">
            {else}
    <tr class="rowB">
    {/if}
    <td style="text-align: center;"><input type="checkbox" class="lectactive" name="lecturer_states[{$lecturerList[aId].id}]" {$lecturerList[aId].checked}></td>
    <td>{$lecturerList[aId].firstname} {$lecturerList[aId].surname}</td>
    <td>{$lecturerList[aId].room}</td>
    <td>{$lecturerList[aId].email}</td>
    <td width="32" class="smaller" valign="middle"
            ><a href="?act=edit,lecturer,{$lecturerList[aId].id}"><img src="images/famfamfam/application_edit.png" title="změnit" alt="[edit]"
                                                                       width="16" height="16"></a
            ><a href="?act=delete,lecturer,{$lecturerList[aId].id}"><img src="images/famfamfam/application_delete.png" title="smazat"
                                                                         alt="[smazat]" width="16" height="16"></a></td>
</tr>
{/section}
</table>
</p>
<script src="js/jquery.js"></script>
<script>
{literal}
/* Change the assignment of the given lecture to this lecture in the active schoolyear. */
$('.lectactive').change ( function ( event ) {
    var params = {};
    var element = this;
    params[event.target.name] = event.target.checked;
    params['type'] = 'lectactive';
    $.post ( 'lect_lecturer.php', params, function ( data ) {
        if ( data.status == 0 )
        {
            $(element).parent().addClass('edit_success');
        }
        else if ( data.status == 1)
        {
            $(element).parent().addClass('edit_fail');
            $(element).parent().attr('title',data.message);
            alert ( 'Učitele nelze přiřadit: ' + data.message );
            // Revert the checkbox to the original state
            element.checked = !element.checked;
        }
        //$('.result').html(data)
    }, 'json').fail ( function ( request, textStatus, errorThrown ) {
        alert ( 'Učitele nelze přiřadit, server odpověděl chybovým hlášením:\n' + request.status + ' - ' + request.statusText );
    });
});
/* Copy the active lecturers from the previous term. */
$('#copyactive').click ( function ( event ) {
    var params = {};
    var element = this;
    params['type'] = 'copyactive';
    alert('Není ještě implementováno...')
    /*
    $.post ( 'lect_lecturer.php', params, function ( data ) {
        if ( data.status == 0 )
        {
            $(element).parent().addClass('edit_success');
        }
        else if ( data.status == 1)
        {
            $(element).parent().addClass('edit_fail');
            $(element).parent().attr('title',data.message);
            alert ( 'Učitele nelze přiřadit: ' + data.message );
            // Revert the checkbox to the original state
            element.checked = !element.checked;
        }
        //$('.result').html(data)
    }, 'json').fail ( function ( request, textStatus, errorThrown ) {
        alert ( 'Učitele nelze přiřadit, server odpověděl chybovým hlášením:\n' + request.status + ' - ' + request.statusText );
    });*/
});
{/literal}
</script>
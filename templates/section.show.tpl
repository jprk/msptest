{include file="admin.sec.hea.tpl"}
{if $sectionImg}
<img class="secimg" width="180" src="?act=show,files,{$sectionImg.id}" alt="{$sectionImg.description}">
{/if}
{$section.text}
{if $articleList}
<h2>{czech}Články{/czech}{english}Articles{/english}</h2>
<div class="article">
<table>
{section name=articlePos loop=$articleList}
<tr>
  <td valign="top"><img src="images/article.gif" width="16" height="16" alt="#"></td>
  <td>
  <p class="atitle"
    ><a href="?act=show,article,{$articleList[articlePos].id}"
	>{$articleList[articlePos].title}</a
	>{if $articleList[articlePos].protect}&nbsp;<img
	 src="images/key.gif" width="10" height="12" alt="přístup po registraci"
	>{/if}{include file="admin.sec.art.tpl"}
  <p class="aabstract">{$articleList[articlePos].abstract}
  </td>
</tr>
{/section}
</table>
</div>
{/if}
{if $sectionFileList}
<h2>{czech}Soubory{/czech}{english}Files{/english}</h2>
<div class="file">
    <ul id="filelist-sortable">
        {section name=filePos loop=$sectionFileList}
            <li class="{$sectionFileList[filePos].icon} ui-state-default" id="file_{$sectionFileList[filePos].id}">
                <p class="atitle"><a href="?act=show,file,{$sectionFileList[filePos].id}"
                        >{$sectionFileList[filePos].origfname}</a>{lockicon file=$sectionFileList[filePos]}{include file="admin.sec.fil.tpl"}</p>
                <p class="aabstract">{$sectionFileList[filePos].description}</p>
            </li>
        {/section}
    </ul>
</div>
{if $isAdmin || $isLecturer}
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
<script type="text/javascript">
{literal}
    $(document).ready(function() {
        $("#filelist-sortable").sortable({
            placeholder: "ui-state-highlight",
            opacity: 0.6,
            //handle : '.handle',
            update : function () {
                var params = $('#filelist-sortable').sortable('serialize');
                $.post ( 'filelist.php', params, function ( data ) {
                    if ( data.status == 0 )
                    {
                        alert ( 'Seznam byl nově seřazen.');
                    }
                    else if ( data.status == 1)
                    {
                        alert ( 'Seznam nelze seřadit: ' + data.message );
                    }
                    //$('.result').html(data)
                }, 'json').fail ( function ( request, textStatus, errorThrown ) {
                    alert ( 'Seznam nelze seřadit, server odpověděl chybovým hlášením:\n' + request.status + ' - ' + request.statusText );
                });
            }
        });
        $("#filelist-sortable").disableSelection();
    });
{/literal}
</script>
{/if}
{/if}

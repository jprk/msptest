<table class="admintable table-override" border="0" cellpadding="4" cellspacing="1">
<tr class="newobject">
<td colspan="4">Přidat novinku</td>
<td width="32" class="smaller" 
  ><a href="?act=edit,news,0"><img src="images/add.gif" alt="[přidat]" title="přidat novinku" width="16" height="16"></a></td>
</tr>
{foreach $fullNewsList as $newsitem}
    {if $newsitem@iteration is even}
        <tr class="rowA">
            {else}
        <tr class="rowB">
    {/if}
    <td>{$newsitem.title}<br/><span class="smaller">{$newsitem.text}</span></td>
    <td class="smaller"><img src="images/{$newsitem.i_src}" alt="[{$newsitem.i_alt}]" title="{$newsitem.i_alt}"></td>
    <td class="smaller">{if isset($newsitem.author.login)}{$newsitem.author.login}{/if}</td>
    <td class="smaller">{$newsitem.datefrom|date_format:"%d.%m.%Y %H:%M"}</td>
    <td width="32" class="smaller" valign="middle"
    ><a href="?act=edit,news,{$newsitem.id}"><img src="images/edit.gif" alt="[změnit]" title="změnit novinku" width="16"
                                                  height="16"></a
        ><a href="?act=delete,news,{$newsitem.id}"><img src="images/delete.gif" alt="[smazat]" title="smazat novinku"
                                                        width="16" height="16"></a></td>
    </tr>
{/foreach}
</table>

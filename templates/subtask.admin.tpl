<p>
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<tr class="newobject">
<td colspan="6">Přidat dílčí úlohu</td>
<td colspan="2" width="64" class="smaller" align="right" valign="middle"
  ><a href="?act=edit,subtask,0"><img src="images/famfamfam/report_add.png" title="přidat novou dílčí úlohu" alt="[nová dílčí úloha]" width="16" height="16"></a></td>
</tr>
<tr>
  <th>Název</th>
  <th>Kód</th>
  <th>Typ</th>
  <th>Od</th>
  <th>Do</th>
  <th>Max</th>
  <th colspan="2">&nbsp;</th>
</tr>
{foreach $subtaskList as $sui}
    {if $sui@index is even}
        <tr class="rowA">
            {else}
        <tr class="rowB">
    {/if}
    <td title="pozice {$sui.position}">{$sui.title}</td>
    <td class="center">{$sui.ttitle}</td>
    <td>
        <div title="{$sui.typestr}"
             style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis; width: 200px;"
        >{$sui.typestr}</div>
    </td>
    <td class="center">
        {if not isset($sui.datefrom)}
            nezadáno
        {elseif $sui.datefrom == '-'}
            -
        {else}
            {$sui.datefrom|date_format:"%d.%m.%Y"}
        {/if}
    </td>
    <td class="center">
        {if not isset($sui.dateto)}
            nezadáno
        {elseif $sui.dateto == '-'}
            -
        {else}
            {$sui.dateto|date_format:"%d.%m.%Y"}
        {/if}
    </td>
    <td class="center">{$sui.maxpts}</td>
    {if isset($sui.datefrom) and ($sui.datefrom == '-')}
        <td width="16" class="smaller" valign="middle">&nbsp;</td>
    {else}
        <td width="16" class="smaller" valign="middle"
        ><a href="?act=edit,subtaskdates,{$sui.id}"><img src="images/famfamfam/calendar.png" alt="[změna data]"
                                                         title="změna data" width="16" height="16"></a></td>
    {/if}
    <td width="48" class="smaller" valign="middle"
    ><a href="?act=admin,extension,{$sui.id}&mode=1"><img src="images/famfamfam/bell_add.png" alt="[prodloužit]"
                                                          title="prodloužit" width="16" height="16"></a
        ><a href="?act=edit,subtask,{$sui.id}"><img src="images/famfamfam/report_edit.png" alt="[změnit]" title="změnit"
                                                    width="16" height="16"></a
        ><a href="?act=delete,subtask,{$sui.id}"><img src="images/famfamfam/report_delete.png" alt="[smazat]"
                                                      title="smazat" width="16" height="16"></a></td>
    </tr>
{/foreach}
</table>

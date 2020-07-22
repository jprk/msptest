<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<tr class="newobject">
<td colspan="5">Přidat další úlohu</td>
<td width="48" class="smaller" align="right" valign="middle" colspan="2"
  ><a href="?act=edit,task,0"><img src="images/famfamfam/report_add.png" title="přidat novou úlohu" alt="[nová úloha]" width="16" height="16"></a></td>
</tr>
<tr>
  <th>Název</th>
  <th>Typ</th>
  <th>Od</th>
  <th>Do</th>
  <th>Min</th>
  <th colspan="2">&nbsp;</th>
</tr>
{foreach $taskList as $tsk}
    {if $tsk@iteration is even}
        <tr class="rowA">
            {else}
        <tr class="rowB">
    {/if}
    <td>{$tsk.title}</td>
    <td>{$tsk.typestr}</td>
    <td class="center">
        {if not isset($tsk.datefrom)}
            nutno zadat
        {elseif $tsk.datefrom == '-'}
            -
        {else}
            {$tsk.datefrom|date_format:"%d.%m.%Y"}
        {/if}
    </td>
    <td class="center">
        {if not isset($tsk.dateto)}
            nutno zadat
        {elseif $tsk.dateto == '-'}
            -
        {else}
            {$tsk.dateto|date_format:"%d.%m.%Y"}
        {/if}
    <td class="center">{$tsk.minpts}</td>
    </td>
    {if isset($tsk.datefrom) and ($tsk.datefrom == '-')}
        <td width="16" class="smaller" valign="middle">&nbsp;</td>
    {else}
        <td width="16" class="smaller" valign="middle"
        ><a href="?act=edit,taskdates,{$tsk.id}"><img src="images/famfamfam/calendar.png" alt="[změna data]"
                                                      title="změna data" width="16" height="16"></a></td>
    {/if}
    <td width="32" class="smaller" valign="middle"
    ><a href="?act=edit,task,{$tsk.id}"><img src="images/famfamfam/report_edit.png" alt="[změnit]" title="změnit"
                                             width="16" height="16"></a
        ><a href="?act=delete,task,{$tsk.id}"><img src="images/famfamfam/report_delete.png" alt="[smazat]"
                                                   title="smazat" width="16" height="16"></a></td>
    </tr>
{/foreach}
<tr class="newobject">
<td colspan="5">Vazba na dílčí úkoly</td>
<td width="48" class="smaller" align="right" valign="middle" colspan="2"
  ><a href="?act=edit,tsksub,{$lecture.id}"><img src="images/article.gif" alt="[vazba na dílčí úlohy]" width="16" height="16"></a></td>
</tr>
</table>

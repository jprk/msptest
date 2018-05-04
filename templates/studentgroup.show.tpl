<table class="admintable" border="0" cellpadding="2" cellspacing="1">
<thead>
<th>ID skupiny</th>
<th>Jméno</th>
<th>Ročník</th>
<th>Skupina</th>
<th>Zapsáno</th>
<th>Zrušeno</th>
</thead>
    <tbody>
    {foreach from=$groupList item=group}
    <tr class="{cycle values="rowA,rowB"}">
        {assign var=num_students value=$group.students|@count}
        {if $num_students}
        <td rowspan="{$num_students}" style="vertical-align: baseline;">{$group.name}</td>
        {foreach from=$group.students item=student name=gloop}
        {if !$smarty.foreach.gloop.first}
            {cycle values="rowA,rowB" print=0}
            <tr class="{cycle values="rowA,rowB"}">
        {/if}
        {if $student.cancel_stamp}
            {assign var="cancelled" value=' style="text-decoration-line: line-through;"'}
        {else}
            {assign var="cancelled" value=""}
        {/if}
        <td{$cancelled}>{$student.surname} {$student.firstname}</td>
        <td align="center"{$cancelled}>{$student.yearno}</td>
        <td align="center"{$cancelled}>{$student.groupno}</td>
        <td align="center"{$cancelled}>{$student.entry_stamp}</td>
        <td align="center"{$cancelled}>{$student.cancel_stamp}</td>
        </tr>
        {/foreach}
        {else}
            {* empty group *}
            <td>{$group.name}</td>
            <td colspan="5"></td>
        {/if}
    {/foreach}
    </tbody>
</table>

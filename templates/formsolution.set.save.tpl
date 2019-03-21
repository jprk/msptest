<p>
Na disk a do databáze byla uložena ručně nahraná řešení následujících úloh:
<p>
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<thead>
<tr class="newobject">
  <th class="left">Název úlohy</th>
  <th>Číslo zadání</th>
  <th>Část řešení</th>
  <th>Stav</th>
</tr>
</thead>
<tbody>
{section name=aId loop=$saveSet}
{section name=pId loop=$saveSet[aId].parts}
{if $smarty.section.aId.iteration is even}
<tr class="rowA">
{else}
<tr class="rowB">
{/if}
  <td>{$saveSet[aId].subtask.title}</td>
  <td>{if $saveSet[aId].assignmentId > 0}{$saveSet[aId].assignmentId}{else}n/a{/if}</td>
  <td>{$saveSet[aId].parts[pId].part+1}</td>
  <td>{$saveSet[aId].parts[pId].status|fcodes}</td>
</tr>
{/section}
{/section}
</tbody>
</table>

{if $excersiseList}
<table class="admintable" cellpadding="2" cellspacing="1">
<thead>
<tr class="newobject">
<th>Den</th>
<th>Od-do</th>
<th>Místnost</th>
<th>Cvičící</th>
<th style="width: 6ex;">&nbsp;</th>
</tr>
</thead>
<tbody>
{section name=excPos loop=$excersiseList}
{if $smarty.section.excPos.iteration is even}
<tr class="rowA">
{else}
<tr class="rowB">
{/if}
<td class="center">{$excersiseList[excPos].day.name}</td>
<td class="center">{$excersiseList[excPos].from|date_format:"%H:%M"}&nbsp;-&nbsp;{$excersiseList[excPos].to|date_format:"%H:%M"}</td>
<td class="center">{$excersiseList[excPos].room}</td>
<td class="center">{$excersiseList[excPos].lecturer.firstname} {$excersiseList[excPos].lecturer.surname}</td>
<td class="center" style="height: 3.2ex;"
  ><a href="?act=show,excersise,{$excersiseList[excPos].id}"
    ><img src="images/famfamfam/application_view_detail.png" alt="[ukázat]"
          title="ukázat detail cvičení"></a></td>
</tr>
{/section}
</tbody>
</table>
{else}
<p>Tento předmět ještě nemá přiřazena žádná cvičení.</p>
{/if}


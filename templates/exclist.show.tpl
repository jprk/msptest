{if $exerciseList}
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<thead>
<tr class="newobject">
<th>Den</th>
<th>Od-do</th>
<th>Místnost</th>
<th>Cvičící</th>
<th{if $isStudent || $isAnonymous} style="width: 6ex;"{/if}>&nbsp;</th>
</tr>
</thead>
<tbody>
{foreach from=$exerciseList item=exercise name=exl}
{if $smarty.foreach.exl.iteration is even}
<tr class="rowA">
{else}
<tr class="rowB">
{/if}
<td class="center">{$exercise.day.name}</td>
<td class="center">{$exercise.from|date_format:"%H:%M"}&nbsp;-&nbsp;{$exercise.to|date_format:"%H:%M"}</td>
<td class="center">{$exercise.room}</td>
<td class="center">
  {* current storage of tutors, ordered list of persons per exercise*}
  {strip}
  {foreach from=$exercise.tutors item=tutor name=tul}
    {if $smarty.foreach.tul.index > 0}, {/if}
    {$tutor.firstname} {$tutor.surname}
  {/foreach}
  {/strip}
  {* legacy storage of tutors, a single person per exercise*}
  {$exercise.lecturer.firstname} {$exercise.lecturer.surname}</td>
<td class="center" style="height: 3.2ex;"
  ><a href="?act=show,exercise,{$exercise.id}"
    ><img src="images/famfamfam/application_view_detail.png" alt="[ukázat]"
          title="ukázat detail cvičení"></a
{if $isAdmin || $isLecturer}
  > <a href="?act=show,exercise,{$exercise.id}&displaynames=true"
    ><img src="images/famfamfam/group.png" alt="[seznam]"
          title="ukázat seznam studentů na cvičení"></a
  > <a href="?act=edit,points,{$exercise.id}&type=exc"
    ><img src="images/famfamfam/award_star_add.png" alt="[body]"
          title="bodování studentů"></a
{/if}
  ></td>
</tr>
{/foreach}
</tbody>
</table>
{else}
<p>Tento předmět ještě nemá přiřazena žádná cvičení.</p>
{/if}


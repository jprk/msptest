<form name="solutionform" action="?act=save,solution,{$files.id}" method="post">
<input type="hidden" name="order" value="{$order}">
<input type="hidden" name="student_id" value="{$student.id}">
<input type="hidden" name="subtask_id" value="{$subtask.id}">
<table class="admintable table-override" border="0" cellpadding="4" cellspacing="1">
<tr class="rowA">
  <td>Úloha:</td>
  <td>{$subtask.title} ({$subtask.ttitle})</td>
</tr>
<tr class="rowB">
{if $lecture.do_groups && $subtask.is_group_task}
  <td>Studentská skupina:</td>
  <td>{if $group_data}{$group_data.name}{else}nepřiřazen{/if}</td>
</tr>
  <tr class="rowA">
    <td>Studenti ve skupině:</td>
    <td>{foreach from=$group_students item=grps name=grp_students}{$grps.firstname} {$grps.surname}{if not $smarty.foreach.grp_students.last}, {/if}{/foreach}</td>
  <tr class="rowB">
    <td>Odevzdal(a):</td>
    {else}
    <td>Student:</td>
{/if}
  <td>{$student.firstname} {$student.surname} ({$student.yearno}/{$student.groupno})</td>
</tr>
<tr class="rowA">
  <td>Zadání:</td>
  <td>
  	{if $filea}
  	<a href="?act=show,file,{$filea.id}">{$filea.origfname}</a> ({$filea.fname})
  	{else}
  	Individuální zadání není k dispozici.
  	{/if}
  </td>
</tr>
<tr class="rowB">
  <td>Řešení:</td>
  <td><a href="?act=show,file,{$files.id}">{$files.origfname}</a> ({$files.fname})</td>
</tr>
<tr class="rowA">
  <td>Body:</td>
  <td><input name="points" value="{$points.points}" size="3"></td>
</tr>
<tr class="rowB">
  <td>Komentář:</td>
  <td>
    <textarea name="comment" rows="4" style="width: 100%;">{$points.comment}</textarea>
  </td>
</tr>
<tr class="rowA">
<td>&nbsp;</td>
<td>
<input type="submit" value="Uložit">
<input type="reset" value="Vymazat">
</td>
</tr>
</table>
</form>

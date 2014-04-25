<form action="?act=save,stuexe,{$lecture.id}" method="post">
<table class="admintable" border="0" cellpadding="2" cellspacing="1">
{section name=studentPos loop=$studentList}
{if $smarty.section.studentPos.iteration % 16 == 1}
<tr>
<th>Příjmení</th>
<th>Jméno</th>
<th>Ročník / Skupina</th>
{section name=excersisePos loop=$excersiseList}
<th>{$excersiseList[excersisePos].day.name}<br/><small class="stuexeroom">{$excersiseList[excersisePos].room}</small><br/>&nbsp;&nbsp;<small>{$excersiseList[excersisePos].from|date_format:"%H:%M"}-&nbsp;<br/>&nbsp;-{$excersiseList[excersisePos].to|date_format:"%H:%M"}&nbsp;&nbsp;</small></th>
{/section}
<th style="width: 6ex;">-</th>
</tr>
{/if}
{if $smarty.section.studentPos.iteration is even}
<tr class="rowA">
{else}
<tr class="rowB">
{/if}
<td>{$studentList[studentPos].surname}</td>
<td>{$studentList[studentPos].firstname}</td>
<td class="center">{$studentList[studentPos].yearno}/{$studentList[studentPos].groupno}</td>
{section name=excersisePos loop=$excersiseList}
<td class="center"><input type="radio" name="se_rel[{$studentList[studentPos].id}]" value="{$excersiseList[excersisePos].id}"{$studentList[studentPos].checked[excersisePos]}}></td>
{/section}
<td class="center"><input type="radio" name="se_rel[{$studentList[studentPos].id}]" value="0"{$studentList[studentPos].checked[excersisePos]}}></td>
</tr>
{/section}
<tr class="rowA"><td colspan="12" class="center"><input type="submit" value="Uložit"></td></tr>
</table>
</form>

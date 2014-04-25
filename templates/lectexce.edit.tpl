<h1>Vazba uèitel-cvièení pro pøedmìt {$lecture.code}</h1>
<form action="?act=save,stuexe,42" method="post">
<table border="1" cellpadding="4" cellspacing="1">
<tr>
<th>Cvièící</th>
{section name=excersisePos loop=$excersiseList}
<th>{$excersiseList[excersisePos].day}<br>&nbsp;&nbsp;<small>{$excersiseList[excersisePos].from|date_format:"%H:%M"}-&nbsp;<br>&nbsp;-{$excersiseList[excersisePos].to|date_format:"%H:%M"}&nbsp;&nbsp;</small></th>
{/section}
</tr>
{section name=lPos loop=$lecturerList}
{if $smarty.section.lPos.iteration is even}
<tr class="rowA">
{else}
<tr class="rowB">
{/if}
<td>{$lecturerList[lPos].firstname} {$lecturerList[lPos].surname}</td>
{section name=ePos loop=$excersiseList}
<td class="center"><input type="radio" name="le_rel[{$excersiseList[ePos].id}]" value="{$lecturerList[lPos].id}"{$lecturerList[lPos].checked[ePos]}}></td>
{/section}
</tr>
{/section}
<tr><td colspan="12" class="center"><input type="submit" value="Uložit"></td></tr>
</table>
</form>
</p>
<hr size="1" color="black"/>
<p>
|&nbsp;&nbsp;<a href="?act=admin,exclist,1">zpìt na seznam cvièení</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/predmety/msp/">zpìt na stránky MSP</a>
</p>

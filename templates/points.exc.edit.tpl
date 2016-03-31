{include file="points.lock.tpl"}
<h2>Údaje o cvičení</h2>
<p>
<table class="exetable" border="0" cellpadding="2" cellspacing="1">
<tr class="rowA">
<td><strong>Den:</strong></td>
<td>{$excersise.day.name}</td>
</tr>
<tr class="rowA">
<td><strong>Hodina:</strong></td>
<td>{$excersise.from|date_format:"%H:%M"}&nbsp;-&nbsp;{$excersise.to|date_format:"%H:%M"}</td>
</tr>
<tr class="rowA">
<td><strong>Místnost:</strong></td>
<td>{$excersise.room}</td>
</tr>
</table>
</p>
<h2>Cvičící</h2>
<p>
<table class="exetable" border="0" cellpadding="2" cellspacing="1">
<tr class="rowA">
<td><strong>Jméno:</strong></td>
<td>{$lecturer.firstname} {$lecturer.surname}</td>
</tr>
<tr class="rowA">
<td><strong>E-mail:</strong></td>
<td><a href="mailto:{$lecturer.email}">{$lecturer.email}</a></td>
</tr>
<tr class="rowA">
<td><strong>Místnost:</strong></td>
<td>{$lecturer.room}</td>
</tr>
</table>
</p>
<h2>Seznam studentů</h2>
{if $studentList}
<p>
    Kromě standardních bodových hodnocení lze do buňky zapsat i hodnotu "<strong>x</strong>"
    označující omluvu z testu a hodnotu "<strong>c</strong>" označující opis.
</p>
<form action="?act=save,points,{$excersise.id}" method="post">
<input type="hidden" name="type" value="exc">
<table class="pointtable" border="1">
<tr>
<th style="width: 8em; text-align: left;">Příjmení</th>
<th style="width: 5em; text-align: left;">Jméno</th>
<th style="width: 5em;">Ročník</th>
<th style="width: 5em;">Skupina</th>
{section name=subtaskPos loop=$subtaskList}
<th style="width: 5em;"><img src="throt.php?text={$subtaskList[subtaskPos].title}" title="{$subtaskList[subtaskPos].title}" alt="{$subtaskList[subtaskPos].title}"></th>
{/section}
</tr>
{section name=studentPos loop=$studentList}
{if $smarty.section.studentPos.iteration is even}
<tr class="rowA">
{else}
<tr class="rowB">
{/if}
<td class="name">{$studentList[studentPos].surname}</td>
<td class="name">{$studentList[studentPos].firstname}</td>
<td class="center">{$studentList[studentPos].yearno}</td>
<td class="center">{$studentList[studentPos].groupno}</td>
{section name=subtaskPos loop=$subtaskList}
{if $smarty.section.studentPos.iteration is even}<td class="subtskA">{else}<td class="subtskB">{/if}<input type="text"  size="5" maxlength="5" style="width: 3em; text-align: center;" name="points[{$studentList[studentPos].dbid}][{$subtaskList[subtaskPos].id}]" value="{$studentList[studentPos].subpoints[subtaskPos].points}"><input type="hidden" name="comments[{$studentList[studentPos].dbid}][{$subtaskList[subtaskPos].id}]" value="{$studentList[studentPos].subpoints[subtaskPos].comment}"></td>
{/section}
</tr>
{/section}
<tr class="newobject"><td colspan="{$smarty.section.subtaskPos.index+4}" class="center" style="padding: 4px;"><input type="submit" value="Uložit" style="border: solid 1px #666666; padding: 0px 4px;"></td></tr>
</table>
</form>
{else}
<p>
Toto cvičení nemá ještě přiřazen seznam studentů.
</p>
{/if}

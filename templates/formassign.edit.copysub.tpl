<p>
Vyberte dílčí úlohu, jejíž přiřazení úkolů chcete zkopírovat k této dílčí úloze.
</p>
<form name="subtaskeditform" action="" method="get">
<input type="hidden" name="act" value="show,formassign,{$subtask.id}">
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<tr class="rowA">
<td class="itemtitle">Název dílčí úlohy</td>
<td>
<select name="copysub" style="width: 100%;">
{section name=aId loop=$studentSubtaskList}
{if $studentSubtaskList[aId].id != $subtask.id}
    <option label="{$studentSubtaskList[aId].ttitle}" value="{$studentSubtaskList[aId].id}">{$studentSubtaskList[aId].title} ({$studentSubtaskList[aId].ttitle})</option>
{/if}
{/section}
</select>
</td>
</tr>
<tr class="rowB">
<td>&nbsp;</td>
<td>
<input type="submit" value="Pokračovat">
<input type="reset" value="Vymazat">
</td>
</tr>
</table>
</form>

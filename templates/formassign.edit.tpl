<form name="fileeditform" action="?act=save,formassign,{$subtask.id}" method="post" enctype="multipart/form-data">
<input type="hidden" name="subtask_id" value="{$subtask.id}">
<input type="hidden" name="MAX_FILE_SIZE" value="8000000">
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<tr class="rowA">
<td class="itemtitle">Soubor s řešeními</td>
<td>
<input type="file" name="assignfile" size="100%" style="background-color: white;">
</td>
</tr>
<tr class="rowB">
<td>&nbsp;</td>
<td>
<input type="submit" value="Odeslat">
<input type="reset" value="Vymazat">
</td>
</tr>
</table>
</form>

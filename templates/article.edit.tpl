<form action="?act=save,article,{$article.id}" method="post">
<input type="hidden" name="id" value="{$article.id}">
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1" width="100%">
<tr class="rowB">
<td class="itemtitle">Rodičovská sekce</td>
<td>
<select name="parent" style="width: 100%;">
{html_options options=$section_parents selected=$article.parent}
</select>
</td>
</tr>
<tr class="rowA">
<td class="itemtitle">Typ článku</td>
<td>
<select name="type" style="width: 100%;">
{html_options options=$articleTypes selected=$article.type}
</select>
</td>
</tr>
    <tr class="rowB">
        <td class="itemtitle">Název článku</td>
        <td><input type="text" name="title" style="width: 100%;" value="{$article.title|escape:"html"}"></td>
    </tr>
    <tr class="rowA">
        <td colspan="2">
        <textarea id="edcTextArea" name="text" style="width: 100%; height: 420px;">{$article.text}</textarea>
        </td>
    </tr>
    <tr class="rowA">
        <td class="itemtitle">Pozice</td>
        <td><input type="text" name="position" maxlength="3" size="3" value="{$article.position}"></td>
    </tr>
    <tr class="rowB">
        <td>&nbsp;</td>
        <td><input type="submit" value="Uložit"> <input type="reset" value="Vymazat"></td>
    </tr>
</table>
</form>
<p><small>
<script language="javascript">
  document.write(document.compatMode);
</script>
</small></p>


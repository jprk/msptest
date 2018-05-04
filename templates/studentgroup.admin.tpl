<form action="?act=save,studentgroup,{$lecture.id}" method="post">
    <input type="hidden" name="id" value="{$lecture.id}">
    <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
        <tr class="rowA">
            <td class="itemtitle">Počet skupin studentů:</td>
            <td width="80%"><input type="text" name="num_groups" maxlength="3" size="3"></td>
        </tr>
        <tr class="rowB">
            <td>&nbsp;</td>
            <td>
                <input type="submit" value="Uložit">
                <input type="reset" value="Vymazat">
            </td>
        </tr>
    </table>
</form>

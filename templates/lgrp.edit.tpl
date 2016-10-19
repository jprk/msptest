<form name="lgrpForm" action="?act=save,lgrp,{$lgrp.id}" method="post">
    <input type="hidden" name="id" value="{$lgrp.id}">
    <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
        <tr class="rowA">
            <td class="itemtitle">Číslo skupiny</td>
            <td width="80%"><input type="text" name="group_id" maxlength="2" size="2" value="{$lgrp.group_id}"></td>
        </tr>
        <tr class="submitrow">
            <td>&nbsp;</td>
            <td>
                <input type="submit" value="Uložit">
                <input type="reset" value="Vymazat">
            </td>
        </tr>
    </table>
</form>

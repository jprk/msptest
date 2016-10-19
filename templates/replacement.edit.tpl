<form name="replForm" action="?act=save,replacement,{$lecture.id}" method="post">
    <input type="hidden" name="manual_term" value="1">
    <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
        <tr class="rowA">
            <td class="itemtitle">Cvičení</td>
            <td>
                <select name="exercise_id" style="width: 100%;">
                {html_options options=$exerciseSelect}
                </select>
            </td>
        </tr>
        <tr class="rowB">
            <td class="itemtitle">Datum</td>
            <td><input type="text" name="date" maxlength="10" size="10">&nbsp;&nbsp;<img src="images/calendar.gif" alt="[kalendář]" onClick="openCalendar('replForm','date');"></td>
        </tr>
        <tr class="rowA">
            <td class="itemtitle">Od</td>
            <td><input type="text" name="mfrom" maxlength="8" size="8"></td>
        </tr>
        <tr class="rowB">
            <td class="itemtitle">Do</td>
            <td><input type="text" name="mto" maxlength="8" size="8"></td>
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

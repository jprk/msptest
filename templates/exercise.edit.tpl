<form name="exerciseForm" action="?act=save,exercise,{$exercise.id}" method="post">
    <input type="hidden" name="id" value="{$exercise.id}">
    <p>
    <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
        <tr class="rowA">
            <td class="itemtitle">Předmět</td>
            <td>
                <select name="lecture_id" style="width: 100%;" disabled="disabled">
                    {html_options options=$lectureSelect selected=$exercise.lecture_id}
                </select>
            </td>
        </tr>
        <tr class="rowB">
            <td class="itemtitle">Školní rok</td>
            <td>
                <select name="schoolyear" style="width: 100%;" disabled="disabled">
                    {html_options options=$yearSelect selected=$exercise.schoolyear}
                </select>
            </td>
        </tr>
        <tr class="rowA">
            <td class="itemtitle">Den</td>
            <td>
                <select name="day" style="width: 100%;">
                    {html_options options=$daySelect selected=$exercise.day.num}
                </select>
            </td>
        </tr>
        <tr class="rowB">
            <td class="itemtitle">Od</td>
            <td>
                <input type="text" name="from" maxlength="5" size="5" value="{$exercise.from|date_format:"%H:%M"}">
                <img src="images/famfamfam/time.png" alt="[hodiny]" title="začátky vyučovacích hodin"
                     onClick="getTimeSelectFor(document.exerciseForm.from);">
            </td>
        </tr>
        <tr class="rowA">
            <td class="itemtitle">Do</td>
            <td><input type="text" name="to" maxlength="5" size="5" value="{$exercise.to|date_format:"%H:%M"}">
                <img src="images/famfamfam/time.png" alt="[hodiny]" title="konce vyučovacích hodin"
                     onClick="getTimeSelectFor(document.exerciseForm.to);"></td>
        </tr>
        <tr class="rowB">
            <td class="itemtitle">Skupina</td>
            <td><input type="text" name="groupno" maxlength="2" size="2" value="{$exercise.groupno}"></td>
        </tr>
        <tr class="rowA">
            <td class="itemtitle">Místnost</td>
            <td><input type="text" name="room" maxlength="16" size="8" value="{$exercise.room}"></td>
        </tr>
        <tr class="rowB">
            <td class="itemtitle">Cvičící</td>
            <td>
                {if $lectureLecturers}
                    <select name="tutor_ids[]" style="width: 100%" multiple="multiple">
                        {html_options options=$lectureLecturers selected=$exercise.tutor_ids}
                    </select>
                {else}
                    K tomuto předmětu nejsou v tomto semestru zatím přiřazeni žádní pedagogové.
                {/if}
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

<form action="?act=save,lecture,{$lectureInfo.id}" method="post">
    <input type="hidden" name="id" value="{$lectureInfo.id}">
    <input type="hidden" name="rootsection" value="{$lectureInfo.rootsection}">
    <table class="admintable" border="0" cellpadding="2" cellspacing="1">
        <tr class="rowA">
            <td class="itemtitle">Kód předmětu</td>
            <td><input type="text" name="code" maxlength="255" size="30" value="{$lectureInfo.code}"></td>
        </tr>
        <tr class="rowB">
            <td class="itemtitle">Název</td>
            <td><input type="text" name="title" maxlength="255" size="50" value="{$lectureInfo.title}"></td>
        </tr>
        <tr class="rowA">
            <td class="itemtitle">Semestr</td>
            <td><input type="text" name="term" maxlength="1" size="1" value="{$lectureInfo.term}"></td>
        </tr>
        <tr class="rowB">
            <td class="itemtitle">Jazyk</td>
            <td><input type="text" name="locale" maxlength="2" size="2" value="{$lectureInfo.locale}"></td>
        </tr>
        <tr class="rowA">
            <td class="itemtitle">Docvičující studenti</td>
            <td><input type="text" name="repl_students" maxlength="1" size="1" value="{$lectureInfo.repl_students}">
                (0 pokud není docvičení)</td>
        </tr>
        <tr class="rowB">
            <td class="itemtitle">Počet docvičení</td>
            <td><input type="text" name="repl_count" maxlength="1" size="1" value="{$lectureInfo.repl_count}">
                (pro jednoho studenta)</td>
        </tr>
        <tr class="rowA">
            <td class="itemtitle">Sylabus</td>
            <td>
                <textarea id="edcTextArea" name="syllabus" style="width: 100%; height: 420px;">
                {$lectureInfo.syllabus|escape:"html"}
                </textarea>
            </td>
        </tr>
        <tr class="rowB">
            <td class="itemtitle">Poděkování</td>
            <td><input type="text" name="thanks" maxlength="255" size="50" value="{$lectureInfo.thanks}"></td>
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

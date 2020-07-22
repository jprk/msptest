<p>
    Údaje o parametrech předmětu <i>{$lecture.title} ({$lecture.code})</i> byly změněny.
</p>
<p>
    Nyní máte nastaveno:
<ul>
    <li>Rozřazování studentů do skupin od {$termParam.group_open_from|date_format:"%d.%m.%Y %H:%M"}
        do {$termParam.group_open_to|date_format:"%d.%m.%Y %H:%M"}</li>
</ul>
</p>
<form action="" method="get">
    {* lecture.id is the id of currently active lecture, not of the edited one *}
    <input type="hidden" name="act" value="admin,lecture,{$lecture.id}">
    <input type="submit" value="Pokračovat">
</form>

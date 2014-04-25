<p>
    Ručně jste vložili následující termín docvičení:
</p>
<ul>
    <li><strong>Datum:</strong> {$manual_term.date|date_format:"%d.%m.%Y"}</li>
    <li><strong>Čas:</strong> {$manual_term.mfrom|date_format:"%H:%M"}&nbsp;-&nbsp;{$manual_term.mto|date_format:"%H:%M"}</li>
    <li><strong>Místnost:</strong> {$excersise.room}</li>
    <li><strong>Cvičící:</strong> {$lecturer.firstname} {$lecturer.surname}</li>
</ul>
<p>
    Pokračujte zpět na <a href="?act=admin,replacement,{$lecture.id}">seznam termínů docvičení</a>.
</p>

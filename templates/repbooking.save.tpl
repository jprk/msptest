<p>
    Pro docvičení skupiny úloh S{$lgrpid} byl zvolen následující termín:
</p>
<ul>
    <li><strong>Datum:</strong> {$replacement.date|date_format:"%d.%m.%Y"}</li>
    <li><strong>Čas:</strong> {$excersise.from|date_format:"%H:%M"}&nbsp;-&nbsp;{$excersise.to|date_format:"%H:%M"}</li>
    <li><strong>Místnost:</strong> {$excersise.room}</li>
    <li><strong>Cvičící:</strong> {$lecturer.firstname} {$lecturer.surname}</li>
</ul>

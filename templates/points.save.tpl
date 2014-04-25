<p>
Bodové ohodnocení studentů navštěvujících cvičení z&nbsp;předmětu
{$lecture.title} ({$lecture.code}) v&nbsp;{$excersise.day.name}
od {$excersise.from|date_format:"%H:%M"} do {$excersise.to|date_format:"%H:%M"}
bylo změneno.
</p>
<form action="?act=edit,points,{$excersise.id}&type=exc" method="post">
<input type="submit" value="Zpět na body studentů tohoto cvičení">
</form>
<form action="?act=admin,exclist,{$excersise.lecture_id}" method="post">
<input type="submit" value="Zpět na administraci cvičení">
</form>

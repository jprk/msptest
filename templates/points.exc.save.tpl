<p>
Body studentů navštěvujících cvičení {$excersise.title} z předmětu
{$lecture.title} ({$lecture.code})
byly uloženy do databáze a zámek bodového hodnocení byl uvolněn.
</p>
<form action="?act=edit,points,{$excersise.id}&type=exc" method="post">
<input type="submit" value="Zpět na body studentů tohoto cvičení">
</form>
<form action="?act=admin,exclist,{$lecture.id}" method="post">
<input type="submit" value="Zpět na administraci cvičení">
</form>

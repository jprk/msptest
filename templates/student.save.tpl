<p>
Údaje o studentovi
<i>{$student.firstname} {$student.surname}</i>
({$student.yearno}/{$student.groupno}) byly změněny.
</p>
<form action="" method="get">
<input type="hidden" name="act" value="admin,student,{$lecture.id}">
<input type="submit" value="Pokračovat">
</form>

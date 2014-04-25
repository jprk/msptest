<p>
Cvičení z předmětu <i>{$lecture.title}</i> ({$lecture.code})
cvičené v <i>{$excersise.day.name}</i>, od <i>{$excersise.from}</i> do <i>{$excersise.to}</i>
v místnosti <i>{$excersise.room}</i>, jehož cvičícím je <i>{$lecturer.firstname} {$lecturer.surname}</i>
bylo smazáno z databáze. 
</p>
<form action="?act=admin,excersise,{$excersise.lecture_id}" method="post">
<input type="submit" value="Pokračovat">
</form>

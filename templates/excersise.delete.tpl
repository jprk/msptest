<form action="?act=realdelete,excersise,{$excersise.id}" method="post">
<input type="hidden" name="id" value="{$excersise.id}">
<p>
Opravdu si přejete smazat cvičení z předmětu <i>{$lecture.title}</i> ({$lecture.code})
cvičené v <i>{$excersise.day.name}</i>, od <i>{$excersise.from}</i> do <i>{$excersise.to}</i>
v místnosti <i>{$excersise.room}</i>, jehož cvičícím je <i>{$lecturer.firstname} {$lecturer.surname}</i>?
</p>
<input type="submit" value="Ano">
</form>

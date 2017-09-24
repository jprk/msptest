<p>
Opravdu si přejete opustit studentskou skupinu <tt>{$student_group.name}</tt>?
</p>
<form style="display: inline;" action="" method="get">
    <input type="hidden" name="act" value="realdelete,studentgroup,{$student_group.id}">
    <input type="submit" value="Ano">
</form>
<form style="display: inline;" action="" method="get">
    <input type="hidden" name="act" value="show,student,{$uid}">
    <input type="submit" value="Ne">
</form>

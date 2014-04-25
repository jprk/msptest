<p>
    Student <i>{$student.firstname} {$student.surname}</i> ({$student.yearno}/{$student.groupno}) byl
    ručně přiřazen jako studující předmětu <em>{$lecture.title} ({$lecture.code})</em> ve školním roce
    {$schoolyear}.
</p>
<form method="get" action="">
    <input type="hidden" name="act" value="admin,stulec,{$lecture.id}"/>
    <button type="submit">Zpět na seznam studentů</button>
</form>


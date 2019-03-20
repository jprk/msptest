{if $unassigned_students}
<p>
    Do volných skupin budou umístěni následující studenti:
</p>
<ul>
{foreach from=$unassigned_students item="student"}
    <li>{$student.surname} {$student.firstname} ({$student.yearno}/{$student.groupno})</li>
{/foreach}
</ul>
<p>
    <form action="" method="post">
        <input type="hidden" name="force_confirm" value="1">
        <input type="submit" value="Přiřadit">
    </form>
</p>
{else}
<p>
    Všichni studenti jsou již rozřazení do skupin, není třeba nikoho nuceně přiřazovat.
</p>
{/if}
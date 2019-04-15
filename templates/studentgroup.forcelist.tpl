{if $unassigned_students}
<p>
    Do volných skupin budou umístěni následující studenti:
</p>
<ul>
{foreach from=$unassigned_students item="student"}
    <li>{$student.surname} {$student.firstname} ({$student.yearno}/{$student.groupno})</li>
{/foreach}
</ul>
<form action="" method="post">
    <p>
        Přiřadit pouze studenty z následujících studijních skupin:
    </p>
    <select multiple="multiple" name="groups[]" size="10">
        {html_options values=$groupList output=$groupList selected=$groupList}
    </select>
    <input type="hidden" name="force_confirm" value="1">
    <p>
    <input type="submit" value="Přiřadit">
    </p>
</form>
{else}
<p>
    Všichni studenti jsou již rozřazení do skupin, není třeba nikoho nuceně přiřazovat.
</p>
{/if}
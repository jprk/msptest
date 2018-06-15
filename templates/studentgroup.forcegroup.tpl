<p>
    Do volných skupin byli umístěni následující studenti:
</p>
<ul>
{foreach from=$forced_groups item="group"}
    <li>{$group.group.name}:
        {foreach from=$group.students item="student"}
            <br/>{$student.surname} {$student.firstname} ({$student.yearno}/{$student.groupno})
        {/foreach}
    </li>
{/foreach}
</ul>


<p>
    Pro docvičení skupiny úloh S{$lgrp.group_id} byl zvolen následující termín:
</p>
<ul>
    <li><strong>Datum:</strong> {$replacement.date|date_format:"%d.%m.%Y"}</li>
{if $replacement.mfrom}
    <li><strong>Čas:</strong> {$replacement.mfrom|date_format:"%H:%M"}&nbsp;-&nbsp;{$replacement.mto|date_format:"%H:%M"}</li>
{else}
    <li><strong>Čas:</strong> {$exercise.from|date_format:"%H:%M"}&nbsp;-&nbsp;{$exercise.to|date_format:"%H:%M"}</li>
{/if}
    <li><strong>Místnost:</strong> {$exercise.room}</li>
    <li><strong>Cvičící:</strong>
        {* current storage of tutors, ordered list of persons per exercise*}
        {strip}
            {foreach from=$exercise.tutors item=tutor name=tul}
                {if $smarty.foreach.tul.index > 0}, {/if}
                {$tutor.firstname} {$tutor.surname}
            {/foreach}
        {/strip}
        {* legacy storage of tutors, a single person per exercise*}
        {$lecturer.firstname} {$lecturer.surname}</li>
</ul>

{if $exerciseList}
<p>
    Takto vypadá předzpracovaná informace načtená z vašeho CSV:
</p>
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
    <tr>
        <th>Den</th>
        <th>Od</th>
        <th>Do</th>
        <th>Místnost</th>
        <th>Skupina</th>
        <th>Cvičící</th>
    </tr>
    {foreach from=$exerciseList item=exercise name=exl}
        {if $smarty.foreach.exl.iteration is even}
            <tr class="rowA">
                {else}
            <tr class="rowB">
        {/if}
        <td class="center">{$exercise.day.name}</td>
        <td class="center">{$exercise.from|date_format:"%H:%M"}</td>
        <td class="center">{$exercise.to|date_format:"%H:%M"}</td>
        <td class="center">{$exercise.room}</td>
        <td class="center">{if $exercise.groupno > 0}{$exercise.groupno}{else}&ndash;{/if}</td>
        <td class="center">
            {* current storage of tutors, ordered list of persons per exercise*}
            {strip}
                {foreach from=$exercise.tutors item=tutor name=tul}
                    {if $smarty.foreach.tul.index > 0}, {/if}
                    {$tutor.firstname} {$tutor.surname}
                {/foreach}
            {/strip}
            {* legacy storage of tutors, a single person per exercise*}
            {$exercise.lecturer.firstname} {$exercise.lecturer.surname}</td>
        </tr>
    {/foreach}
</table>
<p>
<form name="exerciseCSVForm" action="?act=save,exercise,0" method="post">
    <input type="hidden" name="csv" value="true">
    <input type="submit" value="Nahrát seznam">
</form>
</p>
{else}
<p>
    V importovaném soubouru nebyla nalezena žádná cvičení. Ujistěte se, že soubor je ve správném formátu.
</p>
{/if}

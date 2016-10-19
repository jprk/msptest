{if $failstudents}
<p>
    Seznam studentů, kteří na docvičení nedorazili, by asi měl obsahovat i počty absencí
    a být seskupený podle jména. V současné době je co řádek to záznam z databáze,
    řadí se to podle jména studenta a datumu docvičení.
</p>
    <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
        <thead>
        <tr class="newobject">
            <th>Jméno</th>
            <th>Ročník / Skupina</th>
            <th>Počet</th>
            <th>Datum</th>
            <th>Skupina úloh</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$failstudents item=student name=rs}
            {if $smarty.foreach.rs.iteration is even}
            <tr class="rowA" style="vertical-align: baseline;">
            {else}
            <tr class="rowB" style="vertical-align: baseline;">
            {/if}
            <td rowspan="{$student.numitems}">{$student.surname} {$student.firstname}</td>
            <td rowspan="{$student.numitems}" align="center">{$student.yearno} / {$student.groupno}</td>
            <td rowspan="{$student.numitems}" align="center">{$student.numitems}</td>
            {foreach from=$student.replacements item=failure name=rf}
                {if $smarty.foreach.rf.iteration > 1}
                    {if $smarty.foreach.rs.iteration is even}
        <tr class="rowA"  style="vertical-align: baseline;">
                    {else}
        <tr class="rowB" style="vertical-align: baseline;">
                    {/if}
                {/if}
            <td>{$failure.fromtime|date_format:"%d.%m.%Y %H:%M"}</td>
            <td>S{$failure.group_id}</td>
        </tr>
            {/foreach}
        {/foreach}
        </tbody>
    </table>
</form>
{else}
<p>
    Seznam studentů je zatím prázdný.
</p>
{/if}
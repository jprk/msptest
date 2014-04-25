{if $failstudents}
<p>
    Seznam studentů, kteří na docvičení nedorazili, by asi měl obsahovat i počty absencí
    a být seskupený podle jména. V současné době je co řádek to záznam z databáze,
    řadí se to podle jména studenta a datumu docvičení.
</p>
    <table class="admintable" border="0" cellpadding="2" cellspacing="1">
        <thead>
        <tr class="newobject">
            <th>Jméno</th>
            <th>Ročník / Skupina</th>
            <th>Datum</th>
            <th>Skupina úloh</th>
        </tr>
        </thead>
        <tbody>
        {section name=rs loop=$failstudents}
            {if $smarty.section.rs.iteration is even}
            <tr class="rowA">
                {else}
            <tr class="rowB">
            {/if}
            <td>{$failstudents[rs].surname} {$failstudents[rs].firstname}</td>
            <td align="center">{$failstudents[rs].yearno} / {$failstudents[rs].groupno}</td>
            <td>{$failstudents[rs].fromtime|date_format:"%d.%m.%Y %H:%M"}</td>
            <td>{$failstudents[rs].lab_id}</td>
        </tr>
        {/section}
        </tbody>
    </table>
</form>
{else}
<p>
    Seznam studentů je zatím prázdný.
</p>
{/if}
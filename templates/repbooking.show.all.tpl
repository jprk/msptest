{if $bookedstudents}
<p>
    Toto je seznam studentů, kteří si v nějakém okamžiku rezervovali docvičení, ať už na něj potom dorazili
    nebo ne, včetně zrušených rezervací.
</p>
    <table class="admintable" border="0" cellpadding="2" cellspacing="1">
        <thead>
        <tr class="newobject">
            <th>Jméno</th>
            <th>Ročník /<br/>Skupina</th>
            <th>Počet</th>
            <th>Termín</th>
            <th>Skupina<br/>úloh</th>
            <th>Rezervace</th>
            <th>Výsledek</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$bookedstudents item=student name=rs}
            {if $smarty.foreach.rs.iteration is even}
            <tr class="rowA" style="vertical-align: baseline;">
            {else}
            <tr class="rowB" style="vertical-align: baseline;">
            {/if}
            <td rowspan="{$student.numitems}">{$student.surname} {$student.firstname}</td>
            <td rowspan="{$student.numitems}" align="center">{$student.yearno} / {$student.groupno}</td>
            <td rowspan="{$student.numitems}" align="center">{$student.numitems}</td>
            {foreach from=$student.replacements item=replacement name=rf}
                {if $smarty.foreach.rf.iteration > 1}
                    {if $smarty.foreach.rs.iteration is even}
        <tr class="rowA"  style="vertical-align: baseline;">
                    {else}
        <tr class="rowB" style="vertical-align: baseline;">
                    {/if}
                {/if}
            <td>{$replacement.fromtime|date_format:"%d.%m.%Y %H:%M"}</td>
            <td align="center">S{$replacement.group_id}</td>
            <td>{$replacement.datefrom|date_format:"%d.%m.%Y %H:%M"}</td>
                {if $replacement.confirmed}
            <td>ANO</td>
                {elseif $replacement.dateto}
            <td>Z&nbsp;{$replacement.dateto|date_format:"%d.%m.%Y %H:%M"}</td>
                {elseif $replacement.finished}
            <td>NE</td>
                {else}
            <td>&nbsp;</td>
                {/if}
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
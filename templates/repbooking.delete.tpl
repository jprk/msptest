{if $booking}
<p>
{if $booking.candelete}
    Potvrďte prosím, že si přejete opravdu zrušit Vaši rezervací tohoto docvičení:
{elseif $booking.dateto}
    Tuto rezervaci jste již jednou zrušili.
{else}
    Tuto rezervaci již bohužel nelze zrušit, je příliš pozdě. Rezervaci bylo možno
    zrušit do {$booking.fromtime|date_format:"%d.%m.%Y %H:%M"}.
{/if}
</p>
<ul>
    <li><strong>Datum:</strong> {$booking.date|date_format:"%d.%m.%Y"}</li>
    <li><strong>Čas:</strong> {$booking.from|date_format:"%H:%M"}&nbsp;-&nbsp;{$booking.to|date_format:"%H:%M"}</li>
    <li><strong>Místnost:</strong> {$booking.room}</li>
    <li><strong>Cvičící:</strong>
        {* current storage of tutors, ordered list of persons per exercise*}
        {strip}
            {foreach from=$booking.tutors item=tutor name=tul}
                {if $smarty.foreach.tul.index > 0}, {/if}
                {$tutor.firstname} {$tutor.surname}
            {/foreach}
        {/strip}
        {* legacy storage of tutors, a single person per exercise*}
        {$booking.surname} {$booking.firstname}</li>
    <li><strong>Skupina úloh:</strong> S{$booking.grpid} <em>(Úlohy: {foreach from=$lgrpList item=lab name=lab}{$lab.ival1}{if not $smarty.foreach.lab.last}&nbsp;+&nbsp;{/if}{foreachelse}-{/foreach})</em></li>
</ul>
{if $booking.candelete}
<form action="?act=realdelete,repbooking,{$lecture.id}" method="post">
    <input type="hidden" name="datefrom" value="{$booking.datefrom}">
    <input type="hidden" name="replid" value="{$booking.replacement_id}">
    <p>
    <input type="submit" value="Opravdu smazat rezervaci">
    </p>
</form>
{/if}
{else}
{* No booking exists *}
<p>
    Rezervace, kterou se snažíte zrušit, neexistuje.
</p>
{/if}
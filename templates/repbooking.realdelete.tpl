{if $booking}
<p>
    Vaše rezervace docvičení v termínu:
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
<p>
    byla zrušena.
</p>
{else}
{* No booking exists *}
<p>
    Rezervace, kterou se snažíte zrušit, neexistuje.
</p>
{/if}
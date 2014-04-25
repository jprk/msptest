<p>
    Vaše rezervace docvičení v termínu:
</p>
<ul>
    <li><strong>Datum:</strong> {$booking.date|date_format:"%d.%m.%Y"}</li>
    <li><strong>Čas:</strong> {$booking.from|date_format:"%H:%M"}&nbsp;-&nbsp;{$booking.to|date_format:"%H:%M"}</li>
    <li><strong>Místnost:</strong> {$booking.room}</li>
    <li><strong>Cvičící:</strong> {$booking.surname} {$booking.firstname}</li>
    <li><strong>Skupina úloh:</strong> S{$booking.grpid} <em>(Úlohy: {foreach from=$lgrpList item=lab name=lab}{$lab.ival1}{if not $smarty.foreach.lab.last}&nbsp;+&nbsp;{/if}{foreachelse}-{/foreach})</em></li>
</ul>
<p>
    již byla zrušena.
</p>

{if $replacements}
<p>
    Pro docvičení skupiny úloh S{$lgrp.group_id} jsou v tento okamžik dostupné následující termíny
    (některé z vypsaných termínů se v seznamu nemusí objevit &ndash; jedná se o termíny, na něž je již
    přihlášen maximální počet docvičujících studentů, o termíny, kdy již docvičujete, a o termíny, na nichž
    si už někdo zablokoval docvičení Vámi zvolené úlohy S{$lgrp.group_id}):
</p>
<form id="replForm" name="replform" action="?act=save,repbooking,{$lecture.id}" method="post">
    <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
        <thead>
        <tr class="newobject">
            <th width="1ex" style="height: 24px;">&nbsp;</th>
            <th>Datum</th>
            <th>Od-do</th>
            <th>Místnost</th>
            <th>Cvičící</th>
            <th>Volno</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$replacements name=rpos item=repl}
        {if $smarty.foreach.rpos.iteration is even}
        <tr class="rowA">
        {else}
        <tr class="rowB">
        {/if}
            <td width="5%" align="center"><input type="radio" name="replid" value="{$repl.id}"></td>
            <td class="center">{$repl.date|date_format:"%d.%m.%Y"}</td>
            <td class="center">{$repl.from|date_format:"%H:%M"}&nbsp;-&nbsp;{$repl.to|date_format:"%H:%M"}</td>
            <td class="center">{$repl.room}</td>
            <td class="center">
                {* current storage of tutors, ordered list of persons per exercise*}
                {strip}
                    {foreach from=$repl.tutors item=tutor name=tul}
                        {if $smarty.foreach.tul.index > 0}, {/if}
                        {$tutor.firstname} {$tutor.surname}
                    {/foreach}
                {/strip}
                {* legacy storage of tutors, a single person per exercise*}
                {$repl.lecturer.firstname} {$repl.lecturer.surname}</td>
            <td class="center">{$repl.avail_count}</td>
        </tr>
        {/foreach}
        <tr class="submitrow">
            <td>&nbsp;</td>
            <td colspan="4">
                <input type="submit" value="Vybrat termín">
                <input type="reset" value="Vymazat">
            </td>
        </tr>
        </tbody>
    </table>
</form>
    {else}
<p>
    Litujeme, ale v tento okamžik není k dispozici žádný volný termín pro docvičení úlohy.
</p>
{/if}

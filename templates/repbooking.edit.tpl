{if $replacements}
<p>
    Pro docvičení skupiny úloh S{$lgrp.group_id} jsou v tento okamžik dostupné následující termíny
    (některé z vypsaných termínů se v seznamu nemusí objevit &ndash; jedná se o termíny, na něž je již
    přihlášen maximální počet docvičujících studentů, a o termíny, na nichž si už někdo zablokoval
    docvičení Vámi zvolené úlohy S{$lgrp.group_id}):
</p>
<form id="replForm" name="replform" action="?act=save,repbooking,{$lecture.id}" method="post">
    <table class="admintable" border="0" cellpadding="2" cellspacing="1">
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
        {section name=rpos loop=$replacements}
        {if $smarty.section.rpos.iteration is even}
        <tr class="rowA">
        {else}
        <tr class="rowB">
        {/if}
            <td width="5%" align="center"><input type="radio" name="replid" value="{$replacements[rpos].id}"></td>
            <td class="center">{$replacements[rpos].date|date_format:"%d.%m.%Y"}</td>
            <td class="center">{$replacements[rpos].from|date_format:"%H:%M"}&nbsp;-&nbsp;{$replacements[rpos].to|date_format:"%H:%M"}</td>
            <td class="center">{$replacements[rpos].room}</td>
            <td class="center">{$replacements[rpos].lecturer.firstname} {$replacements[rpos].lecturer.surname}</td>
            <td class="center">{$replacements[rpos].avail_count}</td>
        </tr>
        {/section}
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

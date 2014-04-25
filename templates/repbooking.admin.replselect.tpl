{if $replacements}
<p>
    Zvole termín docvičení:
</p>
<form id="replForm" name="replform" action="?act=admin,repbooking,{$lecture.id}" method="post">
    <table class="admintable" border="0" cellpadding="2" cellspacing="1">
        <thead>
        <tr class="newobject">
            <th width="1ex" style="height: 24px;">&nbsp;</th>
            <th>Datum</th>
            <th>Od-do</th>
            <th>Místnost</th>
            <th>Cvičící</th>
            <th>Volno</th>
            <th>Poznámka</th>
        </tr>
        </thead>
        <tbody>
        {section name=rpos loop=$replacements}
            {if $smarty.section.rpos.iteration is even}
            <tr class="rowA">
                {else}
            <tr class="rowB">
            {/if}
            <td width="5%" align="center"><input type="radio"
                                                 name="replid"
                                                 value="{$replacements[rpos].id}"
                                                 onclick="this.form.submit();"{$replacements[rpos].checked}/></td>
            <td class="center">{$replacements[rpos].date|date_format:"%d.%m.%Y"}</td>
            <td class="center">{$replacements[rpos].from|date_format:"%H:%M"}
                &nbsp;-&nbsp;{$replacements[rpos].to|date_format:"%H:%M"}</td>
            <td class="center">{$replacements[rpos].room}</td>
            <td class="center">{$replacements[rpos].lecturer.firstname} {$replacements[rpos].lecturer.surname}</td>
            <td class="center">{$replacements[rpos].avail_count}</td>
            <td class="center">{if $replacements[rpos].manual_term}(nepravidelný){/if}</td>
        </tr>
        {/section}
        <tr class="submitrow">
            <td>&nbsp;</td>
            <td colspan="5">
                <input type="submit" value="Vybrat">
                <input type="reset" value="Vymazat">
            </td>
        </tr>
        </tbody>
    </table>
</form>
{else}
<p>
    Vypadá to, že pro tento předmět ještě nebyly zvoleny žádné termíny docvičení.
    Prosím, vygenerujte je nejprve <a href="?act=admin,replacement,{$lecture.id}">zde</a>.
</p>
{/if}
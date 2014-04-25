<script language="javascript">

var allChecked = false;

{literal}
function markAll(formName) {
    var formObj = document.getElementById(formName);
    if (formObj) {
        allChecked = !allChecked;
        if (formObj.elements && formObj.elements.length) {
            var length = formObj.elements.length;
            for (i = 0; i < length; i++) {
                var elem = formObj.elements[i];
                if (elem.type == 'checkbox' && elem.id.substring(0, 12) == 'replacements') {
                    elem.checked = allChecked;
                }
            }
        }
    }
}
</script>
{/literal}
{if $replacements}
<p>
    Níže vypisujeme všechny možné termíny docvičení v semestru. V seznamu označte
    ta data, kdy bude při probíhajícím cvičení možné docvičení, odznačte rektorské
    a děkanské dny a prázdniny.
</p>
<p>
    V případě přesunů výuky (náhrady za výpadky při rektorských a děkanských dnech,
    technické problémy a podobně) bude třeba, abyste termíny doplnili ručně. Pokud
    v níže uvedeném seznamu tyto termíny odznačíte jako použité, automaticky se smažou.
</p>
<table class="admintable" border="0" cellpadding="2" cellspacing="1">
    <tr class="newobject" style="height: 24px;">
        <td>Vložit ručně termín docvičení</td>
        <td width="16" class="smaller" valign="middle"
                ><a href="?act=edit,replacement,{$lecture.id}"><img src="images/famfamfam/application_edit.png"
                                                                    title="editovat" alt="[editovat]" width="16"
                                                                    height="16"></a
                ></td>
    </tr>
</table>
<p>
    Aktuální seznam možných termínů docvičení včetně ručně vložených termínů je tento:
</p>
<form id="replForm" name="replform" action="?act=save,replacement,{$lecture.id}" method="post">
    <table class="admintable" border="0" cellpadding="2" cellspacing="1">
        <thead>
        <tr class="newobject">
            <th width="1ex" style="height: 24px;">&nbsp;</th>
            <th>Datum</th>
            <th>Od-do</th>
            <th>Místnost</th>
            <th>Cvičící</th>
            <th>Poznámka</th>
        </tr>
        </thead>
        <tbody>
        <tr class="newobject">
            <td width="5%" align="center"><input id="markallTop" type="checkbox" name="markallTop"
                                                 onclick="markAll('replForm');"></td>
            <td colspan="5">Označit / odznačit vše</td>
        </tr>
            {section name=rpos loop=$replacements}
                {if $smarty.section.rpos.iteration is even}
                <tr class="rowA">
                    {else}
                <tr class="rowB">
                {/if}
                <td width="5%" align="center"><input type="checkbox" id="replacements{$smarty.section.rpos.index}"
                                                     name="replacements[{$smarty.section.rpos.index}]"
                                                     value="{$replacements[rpos].id}"{$replacements[rpos].checked}/></td>
                <td class="center">{$replacements[rpos].date|date_format:"%d.%m.%Y"}</td>
                <td class="center">{$replacements[rpos].from|date_format:"%H:%M"}
                    &nbsp;-&nbsp;{$replacements[rpos].to|date_format:"%H:%M"}</td>
                <td class="center">{$replacements[rpos].room}</td>
                <td class="center">{$replacements[rpos].lecturer.firstname} {$replacements[rpos].lecturer.surname}</td>
                <td class="center">{if $replacements[rpos].manual_term}(nepravidelný){/if}</td>
            </tr>
            {/section}
        <tr class="newobject">
            <td width="5%" align="center"><input id="markallBot" type="checkbox" name="markallBot"
                                                 onclick="markAll('replForm');"></td>
            <td colspan="5">&nbsp;Označit / odznačit vše</td>
        </tr>
        <tr class="submitrow">
            <td>&nbsp;</td>
            <td colspan="5">
                <input type="submit" value="Uložit">
                <input type="reset" value="Vymazat">
            </td>
        </tr>
        </tbody>
    </table>
</form>
    {else}
<p>Tento předmět ještě nemá přiřazena žádná cvičení.</p>
{/if}

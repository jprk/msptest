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
    Níže vypisujeme všechny možné termíny docvičení v semestru. V seznamu označte ta data, kdy bude při probíhajícím
    cvičení možné docvičení, odznačte rektorské a děkanské dny a prázdniny.
</p>
<p>
    V případě přesunů výuky (náhrady za výpadky při rektorských a děkanských dnech, technické problémy a podobně)
    bude třeba, abyste termíny doplnili ručně. Pokud v níže uvedeném seznamu takovýto nepravidelný termín odznačíte
    jako použitý, automaticky se smaže bez možnosti termín znovu aktivovat &ndash; jedinou možností je vložit
    takový termín znovu.
</p>
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
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
    <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
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
            {foreach from=$replacements item=repl name=rpos}
                {if $smarty.foreach.rpos.iteration is even}
                <tr class="rowA">
                    {else}
                <tr class="rowB">
                {/if}
                <td width="5%" align="center"><input type="checkbox" id="replacements{$smarty.foreach.rpos.index}"
                                                     name="replacements[{$smarty.foreach.rpos.index}]"
                                                     value="{$repl.id}"{$repl.checked}/></td>
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
                    {if not empty($repl.lecturer)}{$repl.lecturer.firstname} {$repl.lecturer.surname}{/if}</td>
                <td class="center">{if $repl.manual_term}(nepravidelný){/if}</td>
            </tr>
            {/foreach}
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

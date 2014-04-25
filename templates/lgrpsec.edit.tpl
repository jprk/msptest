<p>
    Vyberte ze seznamu laboratorních úloh ty úlohy, které chcte přiřadit do skupiny úloh č. {$lgrp.id}
    ve školním roce {$schoolyear}. Pokud v seznamu nějaká úloha chybí, je to proto, že ve stránkách
    předmětu chybí sekce s popisem úlohy.
</p>
<form id="lgrpForm" name="lgrpform" action="?act=save,lgrpsec,{$lgrp.id}" method="post">
<table class="admintable" border="0" cellpadding="2" cellspacing="1">
<tbody>
{foreach name=lab from=$labList item=labData key=labKey }
{if $smarty.foreach.lab.index is even}
<tr class="rowA">
{else}
<tr class="rowB">
{/if}
<td width="5%" align="center" valign="center" style="height: 24px;"><input type="checkbox" name="labtask[]" value="{$labData.id}"{$labData.checked}/></td>
<td>{$labData.title}</td>
</tr>
{/foreach}
<tr class="submitrow">
<td>&nbsp;</td>
<td>
<input type="submit" value="Vybrat">
<input type="reset" value="Vymazat">
</td>
</tr>
</tbody>
</table>
</form>

<p>
    Zvolte si skupinu úloh, kterou potřebujete docvičit:
</p>
<form id="replForm" name="replform" action="?act=edit,repbooking,{$lecture.id}" method="post">
<table class="admintable" border="0" cellpadding="2" cellspacing="1">
<tbody>
{foreach name=lab from=$lgrpList item=lgrp key=labKey }
{if $smarty.foreach.lab.index is even}
<tr class="rowA">
{else}
<tr class="rowB">
{/if}
<td width="5%" align="center" valign="center" style="height: 24px;"><input type="radio" name="lgrpid" value="{$lgrp.id}"/></td>
<td>Skupina S{$lgrp.group_id}
    <em>(Úlohy: {foreach from=$labtaskList[$lgrp.id] item=lab name=lab}{$lab.ival1}{if not $smarty.foreach.lab.last}&nbsp;+&nbsp;{/if}{foreachelse}-{/foreach})</em></td>
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

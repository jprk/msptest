<table class="menu" border="0" cellspacing="0" cellpadding="1">
<tr>
<td>&nbsp;</td>
<td colspan="{$colspan}">&nbsp;</td>
</tr>
{* empty string that will be used for the intent: trick *}
{assign var="empty" value=""}
{section name=mId loop=$menuHierList}
<tr class="mlevel{$menuHierList[mId].level}">
{$empty|indent:$menuHierList[mId].level:"<td>&nbsp;</td>"}
{if $menuHierList[mId].hilit}
<td style="width: 1em; text-align: right; vertical-align: top;"><strong>&raquo;</strong>&nbsp;</td>
<td colspan="{$menuHierList[mId].colspan}"><strong><a href="?act=show,section,{$menuHierList[mId].id}">{$menuHierList[mId].mtitle}</a></strong></td>
</tr>
{else}
<td style="width: 1em; text-align: right; vertical-align: top;">-&nbsp;</td>
<td colspan="{$menuHierList[mId].colspan}"><a href="?act=show,section,{$menuHierList[mId].id}">{$menuHierList[mId].mtitle}</a></td>
</tr>
{/if}
{/section}
{if $isAdmin || $isLecturer}
<tr>
<td>&nbsp;</td>
<td colspan="{$colspan}">&nbsp;</td>
</tr>
<tr>
<td>-&nbsp;</td>
<td colspan="{$colspan}"><a href="?act=admin,section,{$lecture.id}">administrace</a></td>
</tr>
{/if}
</table>

<table class="admintable" border="0" cellpadding="2" cellspacing="1">
    <tr class="newobject">
        <td colspan="2">Přidat další skupinu úloh</td>
        <td width="50" class="smaller" valign="middle"
                ><a href="?act=edit,lgrp,0"><img src="images/famfamfam/report_add.png" title="nová skupina úloh" alt="[nová skupina úloh]"
                                                 width="16" height="16"></a></td>
    </tr>
    <tr>
        <th style="text-align: left; width: 8em;">Skupina úloh</th>
        <th style="text-align: left;">Úlohy</th>
        <th>&nbsp;</th>
    </tr>
{section name=aId loop=$lgrpList}
    {assign var="grpid" value=$lgrpList[aId].id}
    {if $smarty.section.aId.iteration is even}
    <tr class="rowA">
    {else}
    <tr class="rowB">
    {/if}
    <td>S{$lgrpList[aId].group_id}</td>
    <td>{foreach from=$labtaskList[$grpid] item=lab name=lab}{$lab.ival1}{if not $smarty.foreach.lab.last}&nbsp;+&nbsp;{/if}{foreachelse}-{/foreach}</td>
    <td width="40" class="smaller" valign="middle"
            ><a href="?act=edit,lgrpsec,{$lgrpList[aId].id}"><img src="images/famfamfam/page_edit.png" title="změnit přiřazené úlohy" alt="[změnit přiřazené úlohy]"
                                                                 width="16" height="16"></a
            ><a href="?act=edit,lgrp,{$lgrpList[aId].id}"   ><img src="images/famfamfam/report_edit.png" title="změnit" alt="[edit]"
                                                                 width="16" height="16"></a
            ><a href="?act=delete,lgrp,{$lgrpList[aId].id}" ><img src="images/famfamfam/report_delete.png" title="smazat" alt="[smazat]"
                                                                 width="16" height="16"></a></td>
</tr>
{/section}
</table>

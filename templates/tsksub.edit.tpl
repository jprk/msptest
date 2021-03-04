{*
<form action="?act=save,tsksub,{$lecture.id}" method="post">
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<input type="hidden" name="year" value="{$year}">
<tr>
<th align="left">Název úkolu</th>
{section name=sId loop=$subtaskList}
<th style="width: 5ex;" title="{$subtaskList[sId].title}">{$subtaskList[sId].ttitle}<br><small>({$subtaskList[sId].maxpts}b.)</small></th>
{/section}
</tr>
{section name=tId loop=$taskList}
{if $smarty.section.tId.iteration is even}
<tr class="rowA">
{else}
    <tr class="rowB">
{/if}
    <td>{$taskList[tId].title}</td>
    {section name=sId loop=$subtaskList}
        <td class="center"><input type="radio" name="st_rel[{$subtaskList[sId].id}]"
                                  value="{$taskList[tId].id}"{$taskList[tId].checked[sId]}></td>
    {/section}
    </tr>
{/section}
    <tr class="submitrow">
        <td colspan="{$smarty.section.sId.loop+1}" class="center">
            <input type="submit" value="Uložit">
        </td>
    </tr>
</table>
</form>
*}
<form action="?act=save,tsksub,{$lecture.id}" method="post">
    <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
        <input type="hidden" name="year" value="{$year}">
        <tr>
            <th align="left">Podúloha</th>
            <th align="left">Typ podúlohy</th>
            {* loop over tasks for the rest of the columns *}
            {section name=tId loop=$taskList}
                {*<th style="width: 5ex;">{$taskList[tId].title}<br><small>(min. {$taskList[tId].minpts}b.)</small></th>*}
                <th class="rotated"><span>{$taskList[tId].title} <small>(min. {$taskList[tId].minpts}b.)</small></span>
                </th>
            {/section}
        </tr>
        {section name=sId loop=$subtaskList}
            {if $smarty.section.sId.iteration is even}
                <tr class="rowA">
                    {else}
                <tr class="rowB">
            {/if}
            <td title="{$subtaskList[sId].title}">{$subtaskList[sId].ttitle} <small>({$subtaskList[sId].maxpts}
                    b.)</small></td>
            <td><small>{$subtaskList[sId].typestr}</small></td>
            {section name=tId loop=$taskList}
                <td class="center"><input type="radio" name="st_rel[{$subtaskList[sId].id}]"
                                          value="{$taskList[tId].id}"{$taskList[tId].checked[sId]}></td>
            {/section}
            </tr>
        {/section}
        <tr class="submitrow">
            <td colspan="{$smarty.section.tId.loop+2}" class="center">
                <input type="submit" value="Uložit">
            </td>
        </tr>
    </table>
</form>

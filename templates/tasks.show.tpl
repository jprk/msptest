{include file="admin.sec.hea.tpl"}
{$section.text}
<h2>{czech}Seznam úloh{/czech}{english}Task list{/english}</h2>
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<tr class="newobject">
<th>{czech}Název úlohy{/czech}{english}Task title{/english}</th>
<th colspan="2">{czech}Odevzdání od&ndash;do{/czech}{english}Hand in from&ndash;to{/english}</th>
<th>{czech}Aktivní?{/czech}{english}Active?{/english}</th>
</tr>
{section name=aId loop=$studentSubtaskList}
    {if $smarty.section.aId.iteration is even}
        <tr class="rowA">
            {else}
        <tr class="rowB">
    {/if}
    <td>&nbsp;{$studentSubtaskList[aId].title}</td>
    <td class="date"
        style="width: 18ex;">{if isset($studentSubtaskList[aId].datefrom)}{$studentSubtaskList[aId].datefrom|date_format:"%d.%m.%Y %H:%M"}{else}nezadáno{/if}</td>
    <td class="date"
        style="width: 18ex;">{if isset($studentSubtaskList[aId].dateto)}{$studentSubtaskList[aId].dateto|date_format:"%d.%m.%Y %H:%M"}{else}nezadáno{/if}</td>
    <td class="center">{if $studentSubtaskList[aId].active}{czech}aktivní{/czech}{english}active{/english}{else}{czech}neaktivní{/czech}{english}inactive{/english}{/if}</td>
    </tr>
{/section}
</table>
{if $sectionFileList}
<h2>{czech}Soubory a prémiové úlohy{/czech}{english}Files and premium tasks{/english}</h2>
<div class="file">
<table>
{section name=filePos loop=$sectionFileList}
<tr>
  <td valign="top"><img src="images/{$sectionFileList[filePos].icon}.gif" width="16" height="16" alt="[{$sectionFileList[filePos].icon} file]"></td>
  <td>
  <p class="atitle"
    ><a href="?act=show,file,{$sectionFileList[filePos].id}"
	>{$sectionFileList[filePos].origfname}</a>{include file="admin.sec.fil.tpl"}</p>
  <p class="aabstract">{$sectionFileList[filePos].description}
  </td>
</tr>
{/section}
</table>
</div>
{/if}

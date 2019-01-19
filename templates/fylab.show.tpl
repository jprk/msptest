{include file="admin.sec.hea.tpl"}
{if $sectionImg}
<img class="secimg" width="180" src="?act=show,files,{$sectionImg.id}" alt="{$sectionImg.description}">
{/if}
<h2>{$section.title}</h2>
{$section.text}
{if $sectionFileList}
    <h2>{czech}Soubory k úloze ke stažení{/czech}{english}Downloads{/english}</h2>
    <div class="file">
        <ul id="filelist-sortable">
            {section name=filePos loop=$sectionFileList}
                <li class="{$sectionFileList[filePos].icon} ui-state-default" id="file_{$sectionFileList[filePos].id}">
                    <p class="atitle"><a href="?act=show,file,{$sectionFileList[filePos].id}"
                        >{$sectionFileList[filePos].origfname}</a>{lockicon file=$sectionFileList[filePos]}{include file="admin.sec.fil.tpl"}</p>
                    <p class="aabstract">{$sectionFileList[filePos].description}</p>
                </li>
            {/section}
        </ul>
    </div>
    {* <div class="file">
        <table>
            {section name=filePos loop=$sectionFileList}
                <tr>
                    <td valign="top"><img src="images/{$sectionFileList[filePos].icon}.gif" width="16" height="16" alt="[{$sectionFileList[filePos].icon} file]"></td>
                    <td>
                        <p class="atitle"
                        ><a href="ctrl.php?act=show,file,{$sectionFileList[filePos].id}"
                            >{$sectionFileList[filePos].origfname}</a>{if $adminMode}&nbsp;<a href="ctrl.php?act=edit,file,{$sectionFileList[filePos].id}"
                            ><img style="float: none;" src="images/edit.gif" alt="[edit]" width="16" height="16" align="texttop"  ></a>{/if}</p>
                        <p class="aabstract">{$sectionFileList[filePos].description}
                    </td>
                </tr>
            {/section}
        </table>
    </div> *}
{/if}
{if $articleList}
    <h2>{czech}Fotografie úlohy{/czech}{english}Photographs of the setup{/english}</h2>
    <div class="article" style="margin-top: 1em;">
    <table class="sectiontable" border="0" cellpadding="2" cellspacing="1">
    <thead>
    <tr>
        <th style="width: 10%;">Obrázek</th>
        <th>Popis</th>
    </tr>
    </thead>
    <tbody>
    {section name=articlePos loop=$articleList}
        <tr class="rowA">
        <td valign="top"
        ><a href="?act=show,file,{$articleList[articlePos].lab_image.id}" target="_blank"
            ><img src="?act=show,file,{$articleList[articlePos].lab_thumb.id}"
                  alt="náhled"></a></td>
        <td>
        <p class="aabstract" style="margin-left: 5px;">
        {* Editing toolbar if in edit mode *}
        {include file="admin.art.hea.tpl"}
        {* Description of the photograph *}
        {$articleList[articlePos].text}
        {if $adminMode}
            <div class="file">
            <table>
            {section name=filePos loop=$articleList[articlePos].articleFileList}
                <tr>
                    <td valign="top"><img src="images/{$articleList[articlePos].articleFileList[filePos].icon}.gif" width="16" height="16" title="{$articleList[articlePos].articleFileList[filePos].icon}" alt="[{$articleList[articlePos].articleFileList[filePos].icon} file]"></td>
                    <td>
                <p class="atitle"
                ><a href="?act=show,file,{$articleList[articlePos].articleFileList[filePos].id}"
                    >{$articleList[articlePos].articleFileList[filePos].origfname|escape:"html"}</a
                    >{if $adminMode}&nbsp;<a href="?act=edit,file,{$articleList[articlePos].articleFileList[filePos].id}&returntoparent=1"
                ><img style="float: none;" src="images/edit.gif" alt="[edit]" width="16" height="16" align="texttop"></a
                ><a href="?act=delete,file,{$articleList[articlePos].articleFileList[filePos].id}&returntoparent=1"
                    ><img style="float: none;" src="images/delete.gif" alt="[smazat]" width="16" height="16" align="texttop"></a
                    >{/if}</p>
                <p class="aabstract">{$articleList[articlePos].articleFileList[filePos].description}</p>
                </td>
                </tr>
            {/section}
            </table>
            </div>
        {/if}
        </p>
        </td>
        </tr>
    {/section}
    </tbody>
    </table>
    </div>
{/if}

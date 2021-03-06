{if $studentList}
<p>
Pro školní rok {$schoolyear} budou do seznamu studentů předmětu
<em>{$lecture.title}</em> přidáni tito studenti:
</p>
<form action="?act=save,import,{$lecture.id}" method="post" name="kosimport1" id="kosimport1">
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1" style="table-layout: fixed;">
<col style="width: 15ex;">
<col style="width: 10ex;">
<col style="width: 8ex;">
<col style="width: 7ex;">
<col style="width: 8ex;">
<col style="width: 15ex;">
<col style="width: 7ex;">
<thead>
<tr>
<th>Příjmení</th>
<th>Jméno</th>
<th class="center">Rok/<br/>Skupina</th>
<th>ČVUT ID</th>
<th>Login</th>
<th>E-mail</th>
<th class="center">Hash</th>
</tr>
</thead>
{section name=sId loop=$studentList}
{if $smarty.section.sId.iteration is even}
<tr class="rowA">
{else}
<tr class="rowB">
{/if}
<td
  ><input type="text" name="surname[{$smarty.section.sId.index}]"
          readonly="readonly" value="{$studentList[sId].surname}"
          style="width: 100%;"></td>
<td
  ><input type="text" name="firstname[{$smarty.section.sId.index}]"
          readonly="readonly" value="{$studentList[sId].firstname}"
          style="width: 100%;"></td>
<td class="center"
  ><input type="text" name="yearno[{$smarty.section.sId.index}]"
          readonly="readonly" value="{$studentList[sId].yearno}"
          style="width: 3ex; text-align: center; padding: 0pt;">&nbsp;/&nbsp;
   <input type="text" name="groupno[{$smarty.section.sId.index}]"
          readonly="readonly" value="{$studentList[sId].groupno}"
          style="width: 4ex; text-align: center; padding: 0pt;"></td>
<td
  ><input type="text" name="cvutid[{$smarty.section.sId.index}]"
          readonly="readonly" value="{$studentList[sId].cvutid}"
          style="width: 100%; text-align: center; padding: 0pt;"></td>
<td
  ><input type="text" name="login[{$smarty.section.sId.index}]"
          readonly="readonly" value="{$studentList[sId].login}"
          style="width: 100%;"></td>
<td
  ><input type="text" name="email[{$smarty.section.sId.index}]"
          readonly="readonly" value="{$studentList[sId].email}"
          style="width: 100%;"></td>
<td class="center"
  ><input type="text" name="hash[{$smarty.section.sId.index}]"
          readonly="readonly" value="{$studentList[sId].hash}"
          style="width: 100%;"></td>
</tr>
{/section}
</table>
{if $errors}
<p>
Při zpracování importu došlo k následujícím chybám, které byly ignorovány:
</p>
<p><tt>{$errors}</tt></p>
{/if}
<p>
Importovat pouze studenty následujících studijních skupin:
</p>
<select multiple="multiple" name="groups[]" size="10">
{html_options values=$groupList output=$groupList selected=$groupList}
</select>
<p>
V případě, že zadaná osoba již v naší databázi existuje, bude pouze přiřazena
k předmětu a případně jí bude změněn údaj o studijní skupině a studijním roce.
</p>
<input type="submit" name="Submit" value="Přidat studenty">
</form>
{else}
<p>
Seznam studentů je prázdný.
</p>
{/if}
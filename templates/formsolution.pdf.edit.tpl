<p>
{czech}
  Vložte PDF soubor (respektive soubory, pokud má úloha více částí) s vypracovaným řešením zadání. Odpovídat můžete pouze jednou.
{/czech}
{english}
  Upload the PDF file (resp. files in case that the task has multiple parts) containing the solution to the
  given problem. You may upload the solution only once.
{/english}
</p>
<form name="solutionform" action="?act=save,formsolution,{$subtask.id}" method="post" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="16000000">
{section name=pId loop=$parts}
  {* the <h2> size is influenced by bootstrap even when the rest of the page is not ported to bootstrap yet *}
  <h2 style="font-size: 1.3em; font-weight: bold;">{czech}Úloha{/czech}{english}Task{/english} {$subtask.ttitle}
    -{$assignment.assignmnt_id|string_format:"%05d"}{$parts[pId].part}</h2>
  <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
    <tr class="rowA">
      <td class="itemtitle">{czech}Soubor s popisem řešení úlohy (.pdf){/czech}{english}Document containing the solution to this task (.pdf){/english}
      </td>
      <td>
        <input type="file" name="pdf[{$parts[pId].part}]" size="100%" style="background-color: white;">
      </td>
    </tr>
  </table>
{/section}
  <p style="margin-top: 1em;">
    <input type="submit" value="{czech}Odeslat řešení{/czech}{english}Submit{/english}">
    <input type="reset" value="Vymazat">
  </p>
</form>

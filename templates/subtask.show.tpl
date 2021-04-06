<h2>{czech}Informace o úloze{/czech}{english}Information about the task{/english}</h2>
<p>
    <strong>{czech}Aktivní od:{/czech}{english}Active from:{/english}</strong> {$subtask.datefrom|date_format:"%d.%m.%Y %H:%M"}
    <br>
    <strong>{czech}Aktivní do:{/czech}{english}Deadline:{/english}</strong> {$subtask.dateto|date_format:"%d.%m.%Y %H:%M"}
    <br>
    <strong>{czech}Bodové maximum:{/czech}{english}Maximum points:{/english}</strong> {$subtask.maxpts}
</p>
{if $subtask.assignment}
    <h3>{czech}Text zadání{/czech}{english}Problem formulation{/english}</h3>
    {$subtask.assignment}
{/if}
{if isset($assignment.file_id)}
    <p>
        {czech}Soubor se zadáním si stáhněte{/czech}{english}Download the text from{/english}
        <a href="?act=show,file,{$assignment.file_id}">{czech}zde{/czech}{english}here{/english}</a>.
    </p>
{/if}
<h3>{czech}Odevzdání{/czech}{english}Submission{/english}</h3>
<p>
{if $subtask.active}
{if $subtask.isformassignment}
Formulář pro odevzdání úlohy je <a href="?act=edit,formsolution,{$subtask.id}">zde</a>.
{elseif $subtask.issimuassignment}
Při odevzdání simulinkového modelu postupujte následujícím způsobem:
<ol>
    <li>Vytvořte Simulinkový model a uložte jej jako .mdl soubor.
    <li>Tento soubor nahrajte pomocí formuláře pod tímto textem.
</ol>
Formulář pro odevzdání úlohy je <a href="?act=edit,formsolution,{$subtask.id}">zde</a>.
{elseif $subtask.ispdfassignment}
    {czech}Při odevzdání PDF souboru postupujte následujícím způsobem:
        <ol>
    <li>Vytvořte ve Vašem oblíbeném textovém procesoru soubor odpovídající
        požadavkům zadání.
            <li>Tento soubor zkonvertujte do PDF (MS Word, OpenOffice nebo Lyx to umí rovnou,
        jinak použijte virtuální tiskárnu do PDF, jako je například PDFCreator).
    <li>Tento soubor nahrajte pomocí formuláře pod tímto textem.
        </ol>
        Formulář pro odevzdání úlohy je <a href="?act=edit,formsolution,{$subtask.id}">zde</a>.{/czech}
    {english}When submitting a PDF file, please follow the following rules:
        <ol>
            <li>Use your favorite text editor to create a document containing the solution of the task.
            <li>Convert this file into PDF format (all major text editors are now able to do so, in the
                case of problems you may try to use a virtual PDF printer as PDFCreator).
            <li>Upload this PDF file using the form that is available by clicking on the link below.
        </ol>
        The form for submitting the solution to this task is <a href="?act=edit,formsolution,{$subtask.id}">here</a>.{/english}
{elseif $subtask.islpdfassignment}
<p>
    {czech}Vložte PDF soubor s vypracovaným řešením zadání. Odpovídat můžete pouze jednou.{/czech}
    {english}Submit a PDF file containing the solution of the taks. You may submit the file only once.{/english}
</p>
<form name="solutionform" action="?act=save,formsolution,{$subtask.id}" method="post" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="16000000">
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<tr class="rowA">
  <td class="itemtitle" width="100%">{czech}Soubor s popisem řešení úlohy{/czech}{english}File containing your report{/english} (.pdf)</td>
  <td>
    <input type="file" name="pdf[1]" size="70%"><br>
  </td>
</tr>
<tr class="rowB">
  <td>&nbsp;</td>
  <td>
    <input type="submit" value="Odeslat řešení">
    <input type="reset"  value="Vymazat">
  </td>
</tr>
</table>
</form>
{else}
{* Pokud Vaše semestrální práce sestává pouze z jednoho PDF souboru, nahrajte
jej prosím na náš server pomocí formuláře pod tímto textem.
<p>
V opačném prípadě prosím postupujte následujícím způsobem: *}
<p>
{czech}Postupujte prosím následujícím způsobem:
    <ol>
        <li>Podle pokynů uvedených výše připravte ZIP, RAR, či 7Z archív se všemi soubory své
            práce. Soubory je třeba správným způsobem pojmenovat, archív také,
            vše by mělo být uvedeno výše.
        <li>Tento archív nesmí být větší, než 8MB. Pokud tomu tak je, patrně
            jste přibalili zcela nepotřebný balast (protokoly kompilátoru,
            spustitelné soubory, fotografie z dovolené a podobně).
        <li>Tento archív nahrajte pomocí formuláře pod tímto textem.
        <li>Po úspěšném vložení vašich výsledků do databáze se tato úloha zobrazí
            na Vaší domovské stránce jako odevzdaná.
    </ol>
    <p>
    Odpovídat můžete pouze jednou. Úlohy se opravují ručně, opravu typicky
    nezvládáme v čase kratším, než jeden týden.{/czech}
    {english}Please follow these steps:
    <ol>
        <li>Follow the instructions above (if any) to prepare a ZIP archive with all
            your work files. The files must be named correctly, the archive also,
            everything should be documented above.
        <li>The archive cannot be larger than 8MB. If this is the case, you may have
            included a completely unnecessary ballast (compiler logs, executables,
            and so on).
        <li>Upload this archive using the form below.
        <li>After successful upload of your results to the database, the task will appear
            on as "turned in" on your dashboard.
    </ol>
    <p>
    You can only respond once. Tasks are corrected manually, typically we cannot handle the
    review in less than a week.{/english}
    </p>
    <form name="solutionform" action="?act=save,formsolution,{$subtask.id}" method="post" enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="16000000">
        <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
            <tr class="rowA">
                <td class="itemtitle" style="width: 1%; white-space: nowrap;">Soubor s řešením:</td>
                <td style="width: auto;"><input type="file" name="zip[1]"
                                                style="width: 100%; background-color: white; padding-left: 0px;"><br>
                </td>
            </tr>
            <tr class="rowB">
                <td>&nbsp;</td>
  <td>
    <input type="submit" value="Odeslat řešení">
    <input type="reset"  value="Vymazat">
  </td>
</tr>
</table>
</p>
</form>
{/if}
{else}
    {czech}Úloha není aktivní a není ji možno odevzdat.{/czech}
    {english}The task is not active and therefore its solution cannot be submitted.{/english}
{/if}
</p>

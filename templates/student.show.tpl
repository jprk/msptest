{*
probably not needed now ...
<script type="text/javascript" src="js/lightwindow/prototype.js"></script>
<script type="text/javascript" src="js/lightwindow/scriptaculous.js?load=effects"></script>
<script type="text/javascript" src="js/lightwindow/lightwindow.js"></script>
*}
<p>
{english}
    Welcome to the private web pages of the lecture {$lecture.title}. Here you
    have the possibility to submit your homeworks (in case that the submission
    period is active) and check your score.
{/english}
{czech}
    Vítejte v neveřejné části webové prezentace předmětu {$lecture.title}. Na této
    stránce máte možnost odevzdávat samostatně vypracované úlohy
    (pokud je k tomu ten správný čas) a podívat se na bodové ohodnocení jak vašich
    samostatných úloh, tak i testů a aktivity na cvičeních.
{/czech}
</p>
<p>
{czech}
    Pokud přejedete myší přes bodové hodnocení úloh, může se se otevřít tooltip
    s poznámkou k danému bodovému ohodnocení. Pokud se nic nezobrazí, poznámka
    je patrně prázdná.
{/czech}
{english}
    If you move your mouse pointer over your score in the table below, you may
    see a tooltip containing a comment to the score provided by your lecturer.
    If the tooltip does not open, the comment is empty.
{/english}
</p>
<h2>{czech}Údaje o Vás{/czech}{english}Your data{/english}</h2>
<p style="display: inline;">
{if $student.id > 100000 }
    ČVUT id: {$student.id}<br>
{/if}
    id (pouze na zolotarev.fd.cvut.cz): {$student.twistid|string_format:"%010u"}<br>
    {czech}uživatelské jméno{/czech}{english}user name{/english}: {$student.login}<br>
    {czech}jméno a příjmení{/czech}{english}given name and surname{/english}: {$student.firstname} {$student.surname}<br>
    email: {$student.email}
{if $lecture.do_groups}
    <br>{czech}studentská skupina v tomto předmětu{/czech}{english}student group id in this lecture{/english}:
    {if $group_data}
    {$group_data.name}
    {if $group_open}
    <form style="display: inline;" action="?act=delete,studentgroup,{$group_data.id}" method="post">
        <input type="submit" value="{czech}Zrušit členství{/czech}{english}Remove{/english}">
        {czech}(lze změnit do {$group_open_to|date_format:"%d.%m.%Y %H:%M"}){/czech}{english}(can be altered until {$group_open_to}){/english}
    </form>
    {else}
    {czech}(již nelze změnit, šlo pouze od {$group_open_from|date_format:"%d.%m.%Y %H:%M"} do {$group_open_to|date_format:"%d.%m.%Y %H:%M"}){/czech}{english}(cannot be altered anymore){/english}
    {/if}
<br>{czech}studenti ve skupině{/czech}{english}members of your group{/english}:
    {foreach from=$group_students item=grps name=grp_students}{$grps.firstname} {$grps.surname}{if not $smarty.foreach.grp_students.last}, {/if}{/foreach}
    {else}
    {if $group_open}
        {czech}nepřiřazena, vyberte si{/czech}{english}not yet assigned, select{/english}:&nbsp;
        <form style="display: inline;" action="?act=edit,studentgroup,{$lecture.id}" method="post">
            <select name="group_id">{html_options options=$free_group_options}</select>
            <input type="submit" value="{czech}Uložit{/czech}{english}Save{/english}">
        </form>
    {else}
        {if $group_past}
            {czech}volba skupiny byla možná pouze do {$group_open_to|date_format:"%d.%m.%Y %H:%M"}, bude vám připřazena automaticky{/czech}{english}group selection was possible only until {$group_open_to}; we will select one group for you soon{/english}
        {else}
            {czech}přiřazování bude aktivní od {$group_open_from|date_format:"%d.%m.%Y %H:%M"} do {$group_open_to|date_format:"%d.%m.%Y %H:%M"}{/czech}{english}group selection opens on {$group_open_from} and closes on {$group_open_to}{/english}
        {/if}
    {/if}
    {/if}
{/if}
</p>
<h2>Diskusní fórum</h2>
{if $lecture.show_forum}
    <p>
        Budeme rádi, když nám dotazy nebudete zasílat mailem, ale k diskusi využijete diskusní fórum předmětu.
        Má to i tu nezanedbatelnou výhodu, že vám kromě pedagogů mohou poradit i kolegové a kolegyně.
    </p>
    <p>
        Na diskusní fórum se dostante pouze odsud a to proklikem <a href="/chat/t/{$lecture.code|lower}">zde</a>.
    </p>
{else}
    <p>
        Lokální diskusní forum není v tomto předmětu aktivní.
    </p>
{/if}
<h2>{czech}Samostatné úlohy{/czech}{english}Assignments{/english}</h2>
{if $studentSubtaskList}
    <p>
        {czech}
            Je-li úloha v tabulce označena jako aktivní, je přes ikonu
            <img src="images/famfamfam/report_add.png" alt="[zadání/odevzdat]" title="[zadání/odevzdat]" width="16"
                 height="16">
            či přímo proklikem odkazu "aktivní" přístupné zadání a odevzdávání úloh.
        {/czech}
        {english}
            In case that an assignment is marked as active, it is possible to click on
            <img src="images/famfamfam/report_add.png" alt="[instructions/submit]" title="instructions/submit" width="16"
                 height="16">
            or directly on "active" and access the instructions and submission
            of that particular assignment.
        {/english}
</p>
    <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
        <tr class="newobject">
            <th>{czech}Název úlohy{/czech}{english}Assignment title{/english}</th>
            <th colspan="2" style="width: 34ex">{czech}Odevzdání od-do{/czech}{english}Submission from-to{/english}</th>
            <th>{czech}Aktivní?{/czech}{english}Active?{/english}</th>
            <th>&nbsp;</th>
            <th>{czech}Odevzdáno?{/czech}{english}Sumbitted?{/english}</th>
            <th>{czech}Body/Max{/czech}{english}Points/Max{/english}</th>
            <th>{czech}Komentář{/czech}{english}Comments{/english}</th>
        </tr>
        {foreach name=ssl from=$studentSubtaskList item=subtask}
            {if $smarty.foreach.ssl.iteration is even}
                <tr class="rowA">
                    {else}
                <tr class="rowB">
            {/if}
            <td>&nbsp;{$subtask.title}</td>
            <td class="date"
                style="width: 17ex">{if empty($subtask.datefrom)}{czech}nezadáno{/czech}{english}n/a{/english}{else}{$subtask.datefrom|date_format:"%d.%m.%Y %H:%M"}{/if}</td>
            <td class="date"
                style="width: 17ex">{if empty($subtask.datefrom)}{czech}nezadáno{/czech}{english}n/a{/english}{else}{$subtask.dateto|date_format:"%d.%m.%Y %H:%M":""}{/if}</td>
            <td class="center">
                {if $subtask.active}
                    <a href="?act=show,subtask,{$subtask.id}">{czech}aktivní{/czech}{english}active{/english}</a>
                {else}
                    {czech}neaktivní{/czech}{english}inactive{/english}
                {/if}
            </td>
            <td class="center" style="width: 20px;">
                {if $subtask.active}
                    <a href="?act=show,subtask,{$subtask.id}"><img src="images/famfamfam/report_add.png"
                                                                   alt="[zadání/odevzdat]"
                                                                   title="zobrazit / odevzdat zadání"
                                                                   width="16" height="16"></a>
                {else}
                    <img src="images/famfamfam/report.png" alt="[neaktivní]" title="neaktivní" width="16" height="16">
                    </a>
                {/if}
            </td>
            <td class="center">{if $subtask.haveSolution == 1}{czech}ano{/czech}{english}
                    yes{/english}{else}{czech}ne{/czech}{english}no{/english}{/if}</td>
            <td class="center" title="{$subtask.comment}">{$subtask.pts}
                &nbsp;/&nbsp;{$subtask.maxpts}</td>
            <td class="center">{if $subtask.comment}
                    <a href="comment.php?student_id={$student.id}&subtask_id={$subtask.id}"
                       class="lightwindow" title="Komentář k hodnocení"
                    >{czech}zobrazit{/czech}{english}show{/english}</a>{else}-{/if}</td>
            </tr>
        {/foreach}
</table>
{else}
<p>
    Tento předmět nemá žádné samostatné úlohy.
</p>
{/if}
{if $subtaskList}
<h2>{czech}Výsledky{/czech}{english}Results{/english}</h2>
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
    <tr class="newobject">
        {section name=subtaskPos loop=$subtaskList}
            <th class="smaller" style="width: 4em;"
                title="{$subtaskList[subtaskPos].title}">{$subtaskList[subtaskPos].ttitle}</th>
        {/section}
        {section name=taskPos loop=$taskList}
            <th class="smaller" style="width: 4em;">{$taskList[taskPos].title}</th>
        {/section}
        <th style="width: 5em;">{czech}Body ke zkoušce{/czech}{english}Points for exam{/english}</th>
        <th style="width: 5em;">{czech}Celkem{/czech}{english}Total{/english}</th>
        {if $evaluation.do_grades}
            {assign var="evalHdr" value="Známka"}
        {else}
            {assign var="evalHdr" value="Zápočet"}
        {/if}
        <th style="width: 5em;">{$evalHdr}</th>
    </tr>
    <tr class="rowA">
        {section name=sPos loop=$studentList[0].subpoints}
            <td class="subtskA"
                title="{$studentList[0].subpoints[sPos].comment}">{$studentList[0].subpoints[sPos].points}</td>
        {/section}
        {section name=taskPos loop=$taskList}
            <td class="tskA{$studentList[0].taskclass[taskPos]}">{$studentList[0].taskpoints[taskPos]}</td>
        {/section}
        <td class="sumA{$studentList[0].sumclass}">{$studentList[0].exmpoints}</td>
        <td class="sumA{$studentList[0].sumclass}">{$studentList[0].sumpoints}</td>
        <td class="sumA{$studentList[0].sumclass}">{$studentList[0].gotcredit}
        </td>
    </tr>
</table>
    {* offer classification *}
    {if $lecture.id == 1}
        <h2>Klasifikace</h2>
        {if $studentList[0].grade_confirmed}
            <p>
                Máme zaznamenáno, že v čase {$studentList[0].grade_confirmed} byl z tohoto účtu
                zaslán souhlas s klasifikací {$studentList[0].gotcredit}.
            </p>
        {elseif not empty($studentList[0].sumclass)}
            {* sumclass is either 'p' or 'n' *}
            <p>
                Pokud Vám hodnocení vyhovuje, můžete přijmout navrženou známku a my Vám ji zapíšeme do KOSu:
            </p>
            <p class="center">
                <button id="accept_score">Přijímám známku {$studentList[0].gotcredit}</button>
            </p>
            <p>
                Toto přijetí známky již nejde jednoduše odvolat, rozmyslete se proto důkladně před tím, než tento
                krok učiníte.
            </p>
        {else}
            <p>
                <strong>Upozornění:</strong> V případě, že je hodnocení uzavřeno a výsledná známka je podbarvena
                červenou nebo zelenou barvou,
                objeví se zde možnost takto navržené hodnocení rovnou přijmout.
            </p>
        {/if}
    {/if}
{/if}

{if $taskList}
<h2>{czech}Minimální počty bodů{/czech}{english}Minimal score{/english}</h2>
<p>
{section name=taskPos loop=$taskList}
    {czech}Minimální počet bodů za {/czech}
    {english}Minimal score for the task {/english}
    <strong>{$taskList[taskPos].title}</strong>
    {if $taskList[taskPos].minpts > 0 }
        {czech}je{/czech}{english}is{/english} <strong>{$taskList[taskPos].minpts}</strong>.
    {else}
        {czech}nebyl stanoven.{/czech}
        {english}has not been specified.{/english}
    {/if}<br>
{/section}
</p>
<p>
{if $evaluation.do_grades}
    {czech}
        Minimální počet bodů nutný pro absolvování předmětu je <strong>{$evaluation.pts_E}</strong>.
    {/czech}
    {english}
        Minimal passing score for the exam is <strong>{$evaluation.pts_E}</strong>.
    {/english}
{else}
    {czech}
        Minimální počet bodů nutný pro získání zápočtu je <strong>{$evaluation.pts_E}</strong>.
    {/czech}
    {english}
        Minimal score needed for obtaining an assesment is <strong>{$evaluation.pts_E}</strong>.
    {/english}
{/if}
</p>
{/if}

{if $lecture.do_replacements}
<h2>Docvičení</h2>
<p>
    Následující tabulka podává přehled Vašich termínů docvičení. Rezervace termínů docvičení, pokud je to ještě
    možné, lze zrušit proklikem přes ikonu
    <img src="images/famfamfam/application_delete.png" alt="[smazat]" title="smazat rezervaci" width="16"
         height="16">.
</p>
<p>
    Maximální počet docvičení v jednom semestru je {$lecture.repl_count}. Znamená to, že si najednou nemůžete
    rezervovat více, než {$lecture.repl_count} termínů docvičení a že po každém proběhlém docvičení vám jeden
    termín ubyde.
</p>
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
    <thead>
    <tr class="newobject">
        <th>Datum</th>
        <th>Od-do</th>
        <th>Místnost</th>
        <th>Cvičící</th>
        <th>Skupina úloh</th>
        <th>Rezervováno / Odhlášeno / Potvrzeno</th>
        <th>&nbsp</th>
    </tr>
    </thead>
    <tbody>
    <tr class="newobject">
        <td colspan="6">Rezervovat docvičení</td>
        <td width="16" class="smaller" valign="middle"
        ><a href="?act=edit,repbooking,{$lecture.id}"
            ><img src="images/famfamfam/application_add.png"
                  alt="[rezervovat]" title="rezervovat docvičení"
                  width="16" height="16"></a
            ></td>
    </tr>
    {section name=bk loop=$bookings}
        {if $smarty.section.bk.iteration is even}
            <tr class="rowA">
                {else}
            <tr class="rowB">
        {/if}
        <td>{$bookings[bk].date|date_format:"%d.%m.%Y"}</td>
        <td>{$bookings[bk].from|date_format:"%H:%M"}&nbsp;-&nbsp;{$bookings[bk].to|date_format:"%H:%M"}</td>
        <td>{$bookings[bk].room}</td>
        <td>{$bookings[bk].surname} {$bookings[bk].firstname}</td>
        <td>S{$bookings[bk].grpid}</td>
        <td><small>
                rez. {$bookings[bk].datefrom|date_format:"%d.%m.%Y %H:%M"}<br/>
                {if $bookings[bk].dateto}odhl. {$bookings[bk].dateto|date_format:"%d.%m.%Y %H:%M"}<br/>{/if}
                {if $bookings[bk].failed}neúspěch v&nbsp;testu{/if}
                {if $bookings[bk].passed}docvičeno{/if}
            </small></td>
        <td width="16" class="smaller" valign="middle"
        >{if $bookings[bk].candelete}<a
                href="?act=delete,repbooking,{$lecture.id}&replid={$bookings[bk].replacement_id}&datefrom={$bookings[bk].datefrom|escape:"url"}"
                ><img src="images/famfamfam/application_delete.png"
                      alt="[smazat]" title="smazat rezervaci"
                      width="16" height="16"></a
                >{/if}</td>
        </tr>
    {/section}
    </tbody>
</table>
{/if}

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    {literal}
    $("#accept_score").click(function () {
        {/literal}
        let grade = "{$studentList[0].gotcredit}";
        let r = confirm("Potvrďte prosím, že si opravdu přejete přijmout známku " + grade + " z předmětu {$lecture.code}.");
        {literal}
        if (r) {
            alert('Po odkliknutí Váš souhlas zaznamenáme a zašleme Vám na školní e-mail potvrzení.');
            let params = {"grade": grade};
            let element = this;
            $.post('submitconfirmation.php', params, function (data) {
                if (data.status == 0) {
                    location.reload();
                } else if (data.status == 1) {
                    alert('Body nelze zapsat: ' + data.message);
                }
                //$('.result').html(data)
            }, 'json').fail(function (request, textStatus, errorThrown) {
                alert('Body nelze zapsat, server odpověděl chybovým hlášením:\n' + request.status + ' - ' + request.statusText);
            });
        }
    });
    {/literal}
</script>

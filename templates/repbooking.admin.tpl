{if $replstudents}
<p>
    V seznamu přihlášených studentů prosím označte ve sloupci "Docvičeno" studenty, kteří se na docvičení dostavili
    a napsali vstupní test. V případě, že studenti ve vstupním testu neuspějí, zvolte "Test špatně".
    Pokud se student nedostavil, ale omluvil se, označte to prosím ve sloupci "Omluveno".
</p>
<form id="replForm" name="replform" action="?act=save,repbooking,{$lecture.id}" method="post">
    <input type="hidden" name="studentlist" value="1">
    <input type="hidden" name="replid" value="{$replacement.id}">
    <table class="admintable" border="0" cellpadding="2" cellspacing="1">
        <thead>
        <tr class="newobject">
            <th>Jméno</th>
            <th style="width: 8ex;">Ročník / Skupina</th>
            <th>Skupina úloh</th>
            <th style="width: 10ex;">Docvičeno</th>
            <th style="width: 10ex;">Test špatně</th>
            <th style="width: 10ex;">Omluveno</th>
            <th style="width: 10ex;">Zameškáno</th>
        </tr>
        </thead>
        <tbody>
        {section name=rs loop=$replstudents}
            {assign var="lgrpid" value=$replstudents[rs].lgrp_id}
            {if $smarty.section.rs.iteration is even}
            <tr class="rowA">
                {else}
            <tr class="rowB">
            {/if}
            <td>{$replstudents[rs].firstname} {$replstudents[rs].surname}</td>
            <td align="center">{$replstudents[rs].yearno} / {$replstudents[rs].groupno}</td>
            <td>
                S{$lgrpList[$lgrpid].group_id}
                <em>(Úlohy: {foreach from=$labtaskList[$lgrpid] item=lab name=lab}{$lab.ival1}{if not $smarty.foreach.lab.last}&nbsp;+&nbsp;{/if}{foreachelse}-{/foreach})</em>
            </td>
            <td align="center"><input type="radio"
                                      name="replstatus[{$replstudents[rs].id}]"
                                      value="2"{$replstudents[rs].passed}/></td>
            <td align="center"><input type="radio"
                                      name="replstatus[{$replstudents[rs].id}]"
                                      value="3"{$replstudents[rs].failed}/></td>
            <td align="center"><input type="radio"
                                      name="replstatus[{$replstudents[rs].id}]"
                                      value="1"{$replstudents[rs].excused}/></td>
            <td align="center"><input type="radio"
                                      name="replstatus[{$replstudents[rs].id}]"
                                      value="0"{$replstudents[rs].noshow}/></td>
        </tr>
        {/section}
        <tr class="submitrow">
            <td colspan="5">
                <input type="submit" value="Zapsat prezenci">
                <input type="reset" value="Vymazat">
            </td>
        </tr>
        </tbody>
    </table>
</form>
{else}
<p>
    Na toto docvičení se nepřihlásili žádní studenti.
</p>
{/if}
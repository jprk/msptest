<table class="menu" border="0" cellspacing="0" cellpadding="1">
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2"><strong>administrace</strong></td>
    </tr>
    <tr class="mlevel1">
        <td colspan="2">[{$schoolyear}]</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td valign="top">-&nbsp;</td>
        <td>{adminlink act="admin" obj="lecture" id=$lecture.id text="Správa předmětů"}</td>
    </tr>
    <tr>
        <td valign="top">-&nbsp;</td>
        <td><a class="link_yellow" href="?act=admin,schoolyear,{$lecture.id}">Změna školního roku</a></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>-&nbsp;</td>
        <td><a class="link_yellow" href="?act=admin,section,{$lecture.id}">Sekce</a></td>
    </tr>
    <tr>
        <td>-&nbsp;</td>
        <td><a class="link_yellow" href="?act=admin,article,{$lecture.id}">Články</a></td>
    </tr>
    <tr>
        <td>-&nbsp;</td>
        <td><a class="link_yellow" href="?act=admin,file,{$lecture.id}">Soubory</a></td>
    </tr>
    <tr>
        <td>-&nbsp;</td>
        <td><a class="link_yellow" href="?act=admin,urls,{$lecture.id}">Odkazy</a></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
{* Users and lecturers *}
    <tr>
        <td>-&nbsp;</td>
        <td><a href="?act=admin,user,{$lecture.id}">Uživatelé</a></td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td>{adminlink act="admin" obj="lecturer" id=$lecture.id text="Správa seznamu učitelů pro vyučované předměty"}</td>
    </tr>
{* Students *}
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>-&nbsp;</td>
        <td><a href="?act=admin,student,{$lecture.id}">Studenti</a></td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=show,stulec,{$lecture.id}">Seznam všech výsledků studentů {$lecture.code}</a></td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=show,stulec,{$lecture.id}&restype=2">Seznam studentů s nárokem na zápočet</a></td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=show,stulec,{$lecture.id}&restype=3">Seznam studentů bez nároku na zápočet</a></td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td>{adminlink act="admin" obj="import" id=42          text="Import seznamu studentů"}</td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td>{adminlink act="admin" obj="stulec" id=$lecture.id text="Přiřadit ručně k předmětu"}</td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td>{adminlink act="edit" obj="stupass" id=$lecture.id text="Hesla studentů"}</td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=edit,points,{$lecture.id}&type=lec">Bodovat celý ročník</a></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>-&nbsp;</td>
        <td><a href="?act=admin,news,{$lecture.id}">Novinky</a></td>
    </tr>
    <tr>
        <td>-&nbsp;</td>
        <td><a href="?act=admin,note,{$lecture.id}">Správa poznámek</a> pro učitele {$lecture.code}.</td>
    </tr>
{* Exercises *}
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>-&nbsp;</td>
        <td><a href="?act=admin,exclist,{$lecture.id}">Cvičení</a></td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=admin,stuexe,{$lecture.id}">Přiřazení studentů na cvičení {$lecture.code}</a></td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td>{adminlink act="admin" obj="exercise" id=$lecture.id text="Správa termínů cvičení"}</td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td>{adminlink act="edit" obj="labtask" id=$lecture.id text="Aktivní laboratorní úlohy"}</td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td>{adminlink act="admin" obj="lgrp" id=$lecture.id text="Skupiny laboratorních úloh"}</td>
    </tr>
{* Replacements *}
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>-&nbsp;</td>
        <td>{condlink act="admin" obj="replacement" id=$lecture.id condition=$lecture.do_replacements text="Docvičení"}</td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td>{condlink act="admin" obj="repbooking" id=$lecture.id condition=$lecture.do_replacements text="Prezence docvičení"}</td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td>{condlink act="show" obj="repbooking" id=$lecture.id condition=$lecture.do_replacements text="Seznam docvičujících"}</td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td>{condlink act="show" obj="repbooking" id=$lecture.id getstr="&bookingtype=1" condition=$lecture.do_replacements text="Seznam propadlých docvičení"}</td>
    </tr>
{* Task and subtasks *}
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td valign="top">-&nbsp;</td>
        <td>{condlink act="admin" obj="studentgroup" id=$lecture.id condition=$lecture.group_type text="Generovat studentské skupiny"}</a></td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=admin,studentgroup,{$lecture.id}&forcegroup=1">Nucené přidělení skupiny</a></td>
    </tr>
    <tr class="mlevel1">
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=show,studentgroup,{$lecture.id}">Výpis studentských skupin</a></td>
    </tr>
    <tr>
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=admin,subtask,{$lecture.id}">Dílčí úkoly</a></td>
    </tr>
    <tr>
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=admin,task,{$lecture.id}">Úkoly</a></td>
    </tr>
    <tr>
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=edit,tsksub,{$lecture.id}">Vazba dílčích úkolů na úkoly</a></td>
    </tr>
    <tr>
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=admin,evaluation,{$lecture.id}">Vyhodnocení</a></td>
    </tr>
    <tr>
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=admin,solution,{$lecture.id}">Řešení</a></td>
    </tr>
    <tr>
        <td valign="top">-&nbsp;</td>
        <td><a href="?act=admin,formassign,{$lecture.id}">Nahrát zadání úloh</a></td>
    </tr>
</table>
<br/>

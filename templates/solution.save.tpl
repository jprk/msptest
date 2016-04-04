{if $lecture.do_groups}
    <p>
        Hodnocení úlohy číslo {$subtask.id} s názvem <i>{$subtask.title}</i>
        za studentskou skupinu <i>{$group_data.name}</i> odevzdané studentem/studentkou <i>{$student.firstname} {$student.surname}</i> (login {$student.login})
        v souboru <i>{$file.origfname}</i> bylo uloženo do systému.
    </p>
    <p>
        Za úlohu bylo přiděleno bodové hodnocení {$points} bod/body/bodů studentům
        <ul>
            {foreach from=$group_students item=grps name=grp_students}<li>{$grps.firstname} {$grps.surname} (login {$grps.login})</li>{/foreach}
        </ul>
    </p>
{else}
    <p>
        Hodnocení úlohy číslo {$subtask.id} s názvem <i>{$subtask.title}</i>
        za studenta <i>{$student.firstname} {$student.surname}</i> (login {$student.login})
        v souboru <i>{$file.origfname}</i> bylo uloženo do systému.
    </p>
{/if}
<form action="#{$student.login}" method="get">
<input type="hidden" name="act" value="show,solution,{$subtask.id}">
<input type="hidden" name="order" value="{$order}">
<input type="submit" value="Zpět na seznam odevzdaných řešení úlohy {$subtask.ttitle}">
</form>

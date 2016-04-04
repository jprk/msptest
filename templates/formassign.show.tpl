{if $formassignment.catalogue}
<p>
V adresáři s generovanými soubory zadání byl vytvořen soubor s řešeními.
</p>
{elseif $formassignment.regenerate}
<p>
Zadání pro studenty byla ze zdrojových souborů vygenerována znovu,
přiřazení úkolů se nezměnilo.
</p>
{elseif $formassignment.copysub}
{if $template_found}
<p>
Zkopírováno přiřazení úloh z úlohy {$copysub.title} a vygenerována zadání pro studenty.
</p>
{else}
    <p>
        Zkopírováno přiřazení úloh z úlohy {$copysub.id} <i>&bdquo;{$copysub.title}&ldquo;</i>, vzhledem k absenci šablony
        pro generování úloh byla pro tuto úlohu použita původní zadání.
    </p>
    {if $ignored_students}
        <p>
            Následujícím {$ignored_students|@count} studentům nebylo přiřazeno žádné zadání, protože nemají přiřazeno ani žádné zadání úlohy {$copysub.id}:
            <ul>
                {foreach from=$ignored_students item=igns name=ign_students}<li>{$igns.firstname} {$igns.surname} (kruh {$igns.yearno}/{$igns.groupno}, login <tt>{$igns.login}</tt>){/foreach}</li>
            </ul>
        </p>
    {/if}
{/if}
{elseif $formassignment.onlynew}
<p>
Byla vygenerována zadání pro následující studenty:
</p>
<ul>
{section name=sId loop=$studentList}
<li>{$studentList[sId].surname} {$studentList[sId].firstname} 
    ({$studentList[sId].yearno}/{$studentList[sId].groupno})</li>
{/section}
</ul>
<p>
Zadání pro ostatní studenty zůstala nezměněna.
</p>
{else}
<p>
Zadání pro studenty byla úspěšně vygenerována.
</p>
{/if}
<form action="?act=admin,formassign,{$lecture.id}" method="post">
<input type="submit" value="Pokračovat v administraci zadání">
</form>

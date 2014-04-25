{if $addedList}
<p>
    Byla přidána tato docvičení:
</p>
<ul>
    {section name=al loop=$addedList}
        <li>{$addedList[al].date|date_format:"%d.%m.%Y"}, {$addedList[al].from|date_format:"%H:%M"}
            &nbsp;-&nbsp;{$addedList[al].to|date_format:"%H:%M"} v {$addedList[al].room}</li>
    {/section}
</ul>
{/if}
{if $deletedList}
<p>
    Byla smazána tato docvičení:
</p>
<ul>
    {section name=dl loop=$deletedList}
        <li>{$deletedList[dl].date|date_format:"%d.%m.%Y"}, {$deletedList[dl].from|date_format:"%H:%M"}
            &nbsp;-&nbsp;{$deletedList[dl].to|date_format:"%H:%M"} v {$deletedList[dl].room}</li>
    {/section}
</ul>
{/if}
{if ! $addedList and ! $deletedList}
<p>
    Nedošlo k žádné změně.
</p>
{/if}
<p>
    Pokračujte nějakou akcí vlevo v menu
    nebo jděte zpět na <a href="?act=admin,replacement,{$lecture.id}">seznam termínů docvičení</a>.
</p>

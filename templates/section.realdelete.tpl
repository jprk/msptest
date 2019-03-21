<p>
Sekce s názvem <i>{$section.title}</i> byla z databáze vymazána.
</p>
{if $section.returntoparent}
<form action="?act=show,section,{$section.parent}" method="post">
<input type="submit" value="Zpět na rodičovskou sekci">
</form>
{else}
<form action="?act=admin,section,42" method="post">
<input type="submit" value="Pokračovat na administraci sekcí">
</form>
{/if}

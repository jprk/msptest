<p>
Článek s názvem <i>{$article.title}</i> byl změněn.
</p>
{if $article.returntoparent}
<form action="?act=show,section,{$article.parent}" method="post">
<input type="submit" value="Zpět na sekci">
</form>
{else}
<form action="?act=admin,article,42" method="post">
<input type="submit" value="Pokračovat v administraci článků">
</form>
{/if}
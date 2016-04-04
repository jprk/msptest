<form action="?act=realdelete,article,{$article.id}" method="post">
<input type="hidden" name="id" value="{$article.id}">
<input type="hidden" name="returntoparent" value="{$article.returntoparent}">
<p>Opravdu si přejete smazat článek s názvem <i>"{$article.title}"</i>?</p>
<input type="submit" value="Ano">
</form>


<form action="?act=realdelete,lgrp,{$lgrp.id}" method="post">
    <input type="hidden" name="id" value="{$lpgrp.id}">
    <p>Opravdu si přejete smazat skupinu úloh <i>S{$lgrp.group_id}</i>?
    </p>
    <input type="submit" value="Ano">
</form>

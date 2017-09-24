<p>
    Zadání pro danou úlohu jsou spárována se studenty resp. studentskými skupinami a uložena v databázi.
</p>
<p>
    Pokud se některý ze studentů přihlásí do studentské skupiny poté, co byla zadání přiřazena, své zadání neuvidí
    a je třeba celý proces přiřazení provést znovu (původní záznamy se tím nepřepíšou).
</p>
<form action="?act=admin,formassign,{$lecture.id}" method="post">
<input type="submit" value="Pokračovat v administraci zadání">
</form>

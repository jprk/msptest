<form name="studentForm" action="?act=save,student,{$student.id}" method="post">
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<tr class="rowA">
<td class="itemtitle">Jméno</td>
<td><input type="text" name="firstname" maxlength="64" size="32" value="{$student.firstname}"></td>
</tr>
<tr class="rowB">
<td class="itemtitle">Příjmení</td>
<td><input type="text" name="surname" maxlength="96" size="32" value="{$student.surname}"></td>
</tr>
<tr class="rowA">
<td class="itemtitle">E-mail</td>
<td><input type="text" name="email" maxlength="255" size="24" value="{$student.email}"></td>
</tr>
<tr class="rowB">
<td class="itemtitle">Identifikátor</td>
<td><input type="text" name="id" value="{$student.id}"><br
  ><small>Typicky ČVUT ID shodné s Usermapem, pouze v odůvodněných případech volte
  ID s hodnotou pod 50000 (systém se Vám pokusí vhodnou hodnotu nalézt a navrhnout &ndash;
  pokud byste zvolili duplicitní hodnotu, systém Vám při ukládání studenta vynadá).
  Pokud nevkládáte údaje studenta, zjištěné z Usermapu, neměňte ručně hodnoty ID na takové,
  které by do budoucna mohly být přepsané importem z Usermapu. Pokud tak učiníte, ponesete si
  následky.</small></td>
</tr>
<tr class="rowA">
<td class="itemtitle">Login</td>
<td><input type="text" name="login" maxlength="32" size="32" value="{$student.login}"><input type="hidden" name="id" value="{$student.id}"></td>
</tr>
<tr class="rowB">
<td class="itemtitle">Heslo</td>
<td
  ><input type="password" name="pass1" maxlength="32" size="32" value="{$student.password}"
  ><br/><input type="password" name="pass2" maxlength="32" size="32" value="{$student.password}"
  ><br/><small>Heslo je třeba zadat dvakrát. Nepřejete-li si heslo změnit, 
  ponechte obě políčka tak, jak jsou.</small>
</td>
</tr>
<tr class="rowA">
<td class="itemtitle">Ročník</td>
<td><input type="text" name="yearno" maxlength="1" size="1" value="{$student.yearno}"></td>
</tr>
<tr class="rowB">
<td class="itemtitle">Skupina</td>
<td><input type="text" name="groupno" maxlength="2" size="2" value="{$student.groupno}"></td>
</tr>
<tr class="rowA">
<td class="itemtitle">Hash</td>
<td><input type="text" name="hash" maxlength="64" size="64" value="{$student.hash}"></td>
</tr>
<tr class="rowB">
<td class="itemtitle">Koeficient</td>
<td><input type="text" name="coeff" maxlength="5" size="5" value="{$student.coeff}"></td>
</tr>
<tr class="rowA">
<td class="itemtitle">Kalendářní rok</td>
<td><input type="text" name="calendaryear" maxlength="4" size="4" value="{$student.calendaryear}"></td>
</tr>
<tr class="rowB">
<td>&nbsp;</td>
<td>
<input type="submit" value="Uložit">
<input type="reset" value="Vymazat">
</td>
</tr>
</table>
</form>

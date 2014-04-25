<h3>Formát iKOSu</h3>
<p>
    Zadejte prosím cestu k CSV souboru se jmény a kompletními identifikačními údaji studentů. Tento soubor
    lze vyexportovat z iKOSu následujícím postupem:
</p>
<ul>
    <li>Přihlašte se do iKOSu a zvolte roli RSK (referent pro studium katedry) nebo, pokud to není možné, tak alespoň ROZ (rozvrhář katedry)</li>
    <li>Vpravo vyberte formulář č. 85102 (vypisování a klasifikace předmětů), je to přes <i>studium &gt; evidence a vypisování předmětů</i></li>
    <li>Ve formuláři zvolte správný semestr (zaškrtávací políčko pod seznamem) a dvojklikem otevřete přehled zvoleného předmětu</li>
    <li>Podle role zvolte a uložte seznam studentů:
        <dl>
            <dt>RSK</dt>
            <dd>Kliknout na záložku "Zápis studenta na předmět", vybrat všechny studenty (tlačítko dole) a exportovat do Excelu (tlačítko nahoře v menu)</dd>
            <dt>ROZ</dt>
            <dd>Kliknout na záložku "Zápis na předmět", vybrat studenty a exportovat do Excelu</dd>
        </dl>
    </li>
    <li>Výsledný CSV soubor vybrat v políčku dole a označit správný typ výpisu</li>
</ul>
<form action="?act=edit,import,42" method="post" enctype="multipart/form-data" name="kosimport" id="kosimport">
    <p>
        <input type="file" name="kosfile">
        <select name="format">
            <option value="3" selected="selected">Role RSK</option>
            <option value="4">Role ROZ</option>
        </select>
    </p>
    <p>
        <input type="checkbox" name="addyear" value="1">
        Zvýšit ročník studentů o jedna (je to
        potřeba při importu studentů v září, kdy ještě nedošlo ke kompletnímu
        překlopení KOSu na nový školní rok a čerstvě zapsaní studenti mají ročník
        nastaven na nula).
    </p>
    <p>
        <input type="submit" name="Submit" value="Ověřit formát">
    </p>
</form>
<h3>Webové rozhraní KOSu</h3>
<p>
    Zadejte prosím cestu k CSV souboru se jmény a ČVUT ID studentů. Tento soubor
    lze vyexportovat z WebKOSu následujícím postupem:
</p>
<ul>
    <li>zvolte zobrazení prezenčních seznamů přes volby <em>Předměty / Prezenční
            seznamy</em>,</li>
    <li>zvolte semestr a předmět,</li>
    <li>klikněte na volbu <em>Exportovat</em>,</li>
    <li>soubor si z prohlížeče uložte a do vstupního pole, uvedeného níže, k němu
        zadejte cestu.</li>
</ul>
<form action="?act=edit,import,42" method="post" enctype="multipart/form-data" name="kosimport" id="kosimport">
    <input type="hidden" name="format" value="2">
    <p>
        <input type="file" name="kosfile">
    </p>
    <p>
        <input type="checkbox" name="addyear" value="1">
        Zvýšit ročník studentů o jedna (je to
        potřeba při importu studentů v září, kdy ještě nedošlo ke kompletnímu
        překlopení KOSu na nový školní rok a čerstvě zapsaní studenti mají ročník
        nastaven na nula).
    </p>
    <p>
        <input type="submit" name="Submit" value="Doplnit e-maily a loginy z LDAP serveru FD">
    </p>
</form>
<h3>Stará (terminálová) verze KOSu</h3>
<p>
Zadejte prosím cestu k textovému souboru se jmény a rodnými čísly studentů
v kódování CP1250. Tento soubor lze vyexportovat z&nbsp;terminálové verze KOSu
následujícím postupem:
</p>
<ul>
<li>zvolte požadovaný předmět přes volby <em>Studium / zap. Předmětů - studenti
   / Zapsaní stud. </em>,</li>
<li>po vybrání předmětu si nechte mailem na svoji adresu v kódování 'W' poslat
   seznam studentů volbou <em>Vytisknout / ... / Seznam studentů + email
   adresy</em> a poslat,</li>
<li>výsledný soubor si z mailu uložte a do vstupního pole, uvedeného níže,
   k němu zadejte cestu.</li>
</ul>
<form action="?act=edit,import,42" method="post" enctype="multipart/form-data" name="kosimport" id="kosimport">
<input type="hidden" name="format" value="1">
  <p>
    <input type="file" name="kosfile">
  </p>
  <p>
    <input type="checkbox" name="addyear" value="1">
    Zvýšit ročník studentů o jedna (je to
    potřeba při importu studentů v září, kdy ještě nedošlo ke kompletnímu
    překlopení KOSu na nový školní rok a čerstvě zapsaní studenti mají ročník
    nastaven na nula).
  </p>
  <p>
    <input type="submit" name="Submit" value="Ověřit formát">
  </p>
</form>

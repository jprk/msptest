<p>
    Vygenerujte prosím všechna zadání tak, aby názvy výsledných souborů odpovídaly vzoru
    <pre>&lt;kód předmětu na zolotarevovi&gt;-YYYY-NNNNN.pdf</pre>
    kde <tt>YYYY</tt> označuje začátek školního roku a <tt>NNNNN</tt> je číslo úlohy resp. studentské skupiny, pro
    niž je zadání určeno, uvozené nulami.
</p>
<p>
    Kód předmětu {$lecture.title} je {$lecture.code}.
</p>
<p>
    Všechna zadání potom zabalte do jednoho ZIP souboru, soubor pojmenujte
    <tt>{$lecture.code}-{$lecture.schoolyear}-{$subtask.id}.zip</tt> a uložte jej na server do adresáře
    <tt>/static/upload/{$lecture.code}</tt>. Pak pokračujte v importu.
</p>
<p>
    <form action="?act=save,formassign,{$subtask.id}" method="post">
    <input type="hidden" name="pdfimport" value="1">
    <input type="submit" value="Pokračovat">
    </form>
</p>
<p>
    <em>Příklad:</em> <tt>17TDL-{$lecture.schoolyear}-00041.pdf</tt> je 41. zadání pro předmět <tt>17TEDL</tt> (kód na FD se při akreditaci změnil,
    na zolotarevovi jsme jej nechali stejný) pro školní rok {$schoolyear}.
</p>

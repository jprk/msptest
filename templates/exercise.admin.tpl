<p>
  Nahrát CSV se seznamem cvičení a cvičených skupin:
  <form action="?act=edit,exercise,0" method="post" enctype="multipart/form-data">
    <input type="file" name="csv_exercises">
    <input type="submit" value="Nahrát">
  </form>
  Seznam cvičení je CSV soubor v následujícím formátu:
</p><pre>
groupno;room;from;to;week;day;turor_surname;tutor_initials
97;B106;9:45;11:15;-;st;Kárná;L
50;B106;11:30;13:00;-;st;Kárná;L
51;B106;13:15;14:45;-;st;Kárná;L
83;B101;11:30;13:00;-;ct;Kovář;B
61;B106;13:15;14:45;-;ct;Kárná;L
99;B101;9:45;11:15;-;ct;Kovář;B
60;B106;11:30;13:00;-;ct;Kárná;L
80;B106;15:00;16:30;-;ct;Alexeeva;E
-81;B106;16:45;18:15;-;ct;Alexeeva;E
</pre><p>
  Cvičení lze samozřejmě ale i přidat ručně:
</p><p>
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<tr class="newobject">
<td colspan="6">&nbsp;Přidat cvičení</td>
<td width="40" class="smaller" valign="middle"
  ><a href="?act=edit,exercise,0&lecture_id={$lecture.id}"><img src="images/add.gif" title="přidat cvičení" alt="[nové cvičení]" width="16" height="16"></a></td>
</tr>
{if $exerciseList}
<tr>
  <th>Den</th>
  <th>Od</th>
  <th>Do</th>
  <th>Místnost</th>
  <th>Skupina</th>
  <th>Cvičící</th>
  <th>&nbsp;</th>
</tr>
{foreach from=$exerciseList item=exercise name=exl}
{if $smarty.foreach.exl.iteration is even}
<tr class="rowA">
{else}
<tr class="rowB">
{/if}
<td class="center">{$exercise.day.name}</td>
<td class="center">{$exercise.from|date_format:"%H:%M"}</td>
<td class="center">{$exercise.to|date_format:"%H:%M"}</td>
  <td class="center">{$exercise.room}</td>
  <td class="center">{if $exercise.groupno > 0}{$exercise.groupno}{else}&ndash;{/if}</td>
  <td class="center">
    {* current storage of tutors, ordered list of persons per exercise *}
    {strip}
      {foreach from=$exercise.tutors item=tutor name=tul}
        {if $smarty.foreach.tul.index > 0}, {/if}
        {$tutor.firstname} {$tutor.surname}
      {/foreach}
    {/strip}
    {* legacy storage of tutors, a single person per exercise *}
    {if not empty($exercise.lecturer)}{$exercise.lecturer.firstname} {$exercise.lecturer.surname}{/if}</td>
<td width="40" class="smaller" valign="middle"
  ><a href="?act=edit,exercise,{$exercise.id}"><img src="images/edit.gif" alt="[edit]" width="16" height="16"></a
  ><a href="?act=delete,exercise,{$exercise.id}"><img src="images/delete.gif" alt="[smazat]" width="16" height="16"></a></td>
</tr>
{/foreach}
{/if}
</table>

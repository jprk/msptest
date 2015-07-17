<script type="text/javascript" src="https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
<p>
    Před odevzdáním řešení úlohy si prosím zkontrolujte, že jsme vámi vložené hodnoty rozpoznali správně a že jste se
    nikde neuklepli ve znaménku či při zadávání stability systému.
</p>
{foreach from=$parts item=part_group}
    {assign var=idx value=$part_group.part}
    {assign var=pz value=$varList[$idx]}
<h3>Úloha {$subtask.ttitle}-{$assignment.assignmnt_id|string_format:"%05d"}{$idx}</h3>
<p>
    Vámi zadané řešení úlohy je ve tvaru
    \[
        H(z) = {literal}\frac{1}{{/literal}{rootfactor var=$pz re=$a[$idx] im=$b[$idx]} \cdot {rootfactor var=$pz re=$c[$idx] im=$d[$idx]} \cdot {rootfactor var=$pz re=$e[$idx] im=$f[$idx]}{literal}}{/literal}
    \]
</p>
<p>
    Systém byl ozačen jako <strong>{$g[$part_group.part]}</strong>.
</p>
{/foreach}
<p>
{if count($parts) == 1}
Zkontrolujte si prosím, že výše uvedené řešení je správně.
{else}
Zkontrolujte si prosím, že výše uvedená řešení jsou správně.
{/if}
</p>
<form name="solutionform" action="?act=save,formsolution,{$subtask.id}" method="post" >
{foreach from=$sol_data key=var_name item=tsk_ids}
    {foreach from=$tsk_ids key=tsk_id item=value}
<input type="hidden" name="{$var_name}[{$tsk_id}]" value="{$value}">
    {/foreach}
{/foreach}
<p>
<input type="submit" value="Opravdu odevzdat">
</p>
</form>

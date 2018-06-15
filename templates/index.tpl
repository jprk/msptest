<ul>
{foreach from=$lectureList item=li}
    <li><a href="/{$li.url}">{$li.code}</a> ({$li.title})</li>
{/foreach}
</ul>
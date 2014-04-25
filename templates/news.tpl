{section name=nId loop=$newsList}
<div class="newsitem">
<div class="newshead">{$newsList[nId].title}</div>
<div class="newsbody">
<span class="newstime">[&nbsp;{$newsList[nId].datefrom|date_format:"%d.%m.%Y %H:%M"}&nbsp;/&nbsp;{$newsList[nId].author.firstname}&nbsp;{$newsList[nId].author.surname}&nbsp;]</span><br/>
{$newsList[nId].text}
</div>
</div>
{/section}

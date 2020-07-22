<!DOCTYPE html>
<html>
<head>
    <title>Video player:{$prefix}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="style.css" rel="stylesheet" title="formal" type="text/css">
    {* <link href="ex/{$lecture.id}/metadata.css" rel="stylesheet" title="formal" type="text/css"> *}
    <link href="stylist.css" rel="stylesheet" title="formal" type="text/css">
</head>
<body style="margin-left: 1em">
{if $prefix}
    <h1>
        Přehráváme {$prefix}.mp4 ...
    </h1>
    <p>
        Původní AVI video si můžete stáhnout [<a href="/static/{$prefix}.avi">zde</a>] a titulky [<a
                href="/static/{$prefix}.srt">zde</a>].
    </p>
    <p>
        <video controls width="576" preload="metadata">
            <source src="/static/{$prefix}.mp4" type="video/mp4">
            <source src="/static/{$prefix}.webm" type="video/webm">
            <track src="/static/{$prefix}.vtt" kind="captions" srclang="cs" label="České titulky">
            Je nám líto, zdá se, že váš prohlížeč nepodporuje vkládání videa do stránek.
        </video>
    </p>
{else}
    <h1>Něco je špatně ...</h1>
    <p>
        Je nám líto, ale nezadali jste žádný soubor k přehrání. Zkuste to znovu a lépe ;-).
    </p>
{/if}
</body>
</html>
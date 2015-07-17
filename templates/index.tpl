<!DOCTYPE HTML>
<html>
<head>
    <title>{$server}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!-- styles for the application -->
    <link href="style.css" rel="stylesheet" title="formal" type="text/css">
    <link href="stylist.css" rel="stylesheet" title="formal" type="text/css">
</head>
<body>
{if $error}
    <h1>Interní chyba aplikace</h1>
    <p>{$error}</p>
{else}
    <h1>{$server}: Seznam předmětů</h1>
    <ul>
{section name=lectureId loop=$lectureList}
        <li><a href="/{$lectureList[lectureId].code}">{$lectureList[lectureId].code}</a> - {$lectureList[lectureId].title}</li>
{/section}
    </ul>
{/if}
</body>
</html>

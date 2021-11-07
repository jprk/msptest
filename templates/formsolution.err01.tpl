<p>
    V databázi již existuje záznam o odevzdání Vaší úlohy {if isset($uploader) and isset($uploader.login)}uživatelem <tt>{$uploader.login}</tt>
    ({$uploader.firstname} {$uploader.surname}, {$uploader.email}) {/if}s časovým razitkem {$timestamp}.
    Každou úlohu lze odevzdat pouze jednou.
<p>

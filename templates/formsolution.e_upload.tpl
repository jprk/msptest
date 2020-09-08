<p>
    Při nahrávání souborů došlo k chybě:
</p>
{if $upload_status}
    <samp>{$upload_status}</samp>
{else}
    <p>
        Soubor: <var>{$upload_err_file_name}</var><br/>
        Chyba: <samp>{$upload_err_errno|fcodes}</samp>
    </p>
{/if}
<p>
    Opravte prosím chybu a nahrajte soubor(y) znovu.
</p>

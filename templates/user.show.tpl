<p>
    Role v systému: {$user.roleName}<br/>
    Poslední přihlášení: {$user.lastlogin}<br/>
    Počet neúspěšných pokusů o přihlášení: {$user.failcount}<br/>
</p>
<p>
    Beta: {if $lecture.show_forum}<a href="/chat/t/{$lecture.code|lower}">Diskusní
        fórum</a>{else}Diskusní fórum není v tomto předmětu aktivní.{/if}
</p>

{if $isAnonymous}
    <p>
        Vaše odhlášení z neveřejné oblasti stránek předmětu <i>{$lecture.title}</i>
        proběhlo úspěšně. Od tohoto okamžiku v systému pracujete jako anonymní uživatel.
    </p>
{else}
    <p>
        Vaše odhlášení z převzaté role na stránkách předmětu <i>{$lecture.title}</i>
        proběhlo úspěšně. Od tohoto okamžiku v systému pracujete opět jako uživatel <tt>{$login}</tt>.
    </p>
{/if}
<?php
/**
 * Display lock icon if the file is private.
 *
 * @param $params
 * @param $smarty
 * @return string
 */
function smarty_function_lockicon($params, &$smarty)
{
    if ( ! isset ( $params['file'] ))
    {
        $smarty->trigger_error ( "lockicon: missing 'file' parameter" );
        return;
    }

    if ( $params['file']['type'] == 253 )
    {
        return '<img class="icon_lock" src="images/famfamfam/lock.png" title="Private" alt="[private file]"/>';
    }

    return '';
}
?>

<?php
/**
 * Display lock icon if the file is private.
 *
 * @param $params
 * @param $smarty
 * @return string
 */
function smarty_function_lockicon($params, Smarty_Internal_Template $template)
{
    if (!isset ($params['file']))
    {
        trigger_error("lockicon: missing 'file' parameter");
        return null;
    }

    if ($params['file']['type'] == 253)
    {
        return '<img class="icon_lock" src="images/famfamfam/lock.png" title="Private" alt="[private file]"/>';
    }

    return '';
}

?>

<?php
/**
 * Display lock icon if the file is private.
 *
 * @param $params
 * @param $smarty
 * @return string
 */
function smarty_function_html_checked($params, &$smarty)
{
    if (!isset ($params['value']))
    {
        $smarty->trigger_error("html_checked: missing 'value' parameter");
        return;
    }

    if ($params['value'])
    {
        return 'checked="checked"';
    }

    return '';
}

?>

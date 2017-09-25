<?php
/**
 * Conditional link, active only in case that the parameter `condition` is true.
 *
 * @param $params
 * @param $smarty
 * @return string
 */
function smarty_function_condlink($params, &$smarty)
{
    /* This is the list of mandatory parameters. */
    $mparams = [
        'text' => null,
        'condition' => null,
        'obj' => null,
        'act' => null,
        'id' => null
    ];

    /* Merge the parameter array with the mandatory parameters.
       Use `mparams` as the base so that any entry with an identical
       key in `params` will overwrite it. */
    $params = array_merge($mparams, $params);

    //var_dump($params);

    /* Check that all mandatory arguments have been specified. */
    foreach (array_keys($mparams) as $kval)
    {
        if (!isset ($params[$kval]))
        {
            trigger_error("condlink: missing '" . $kval . "' parameter");
            return null;
        }
    }

    /* Check for optional parameter `getstr` holding a GET request string that will
       be added to the URL. */
    if (!array_key_exists('getstr', $params))
    {
        $params['getstr'] = '';
    }

    if ($params['condition'])
    {
        $text = '<a href="?act=' .
            $params['act'] . ',' .
            $params['obj'] . ',' .
            $params['id'] . $params['getstr'] . '">' . $params['text'] . '</a>';
    }
    else
    {
        $text = '<span class="inactive">' . $params['text'] . '</span>';
    }

    return $text;
}

?>

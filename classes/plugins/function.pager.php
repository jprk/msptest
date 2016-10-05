<?php
/**
 * Conditional link, active only in case that the parameter `condition` is true.
 *
 * @param $params
 * @param $smarty
 * @return string
 */
function smarty_function_pager($params, &$smarty)
{
    /* This is the list of mandatory parameters. */
    $mparams = array('obj' => NULL, 'act' => NULL, 'id' => NULL,
        'name' => NULL, 'offset' => NULL);
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
            $smarty->trigger_error("condlink: missing '" . $kval . "' parameter");
            return NULL;
        }
    }

    $actStr = $params['act'] . "," . $params['obj'] . "," . $params['id'];
    $text = '';

    return $text;
}

?>

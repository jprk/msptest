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
    $mparams = array ( 'text' => NULL, 'condition' => NULL,
                        'obj'=> NULL, 'act'=> NULL, 'id'=> NULL );
    /* Merge the parameter array with the mandatory parameters.
       Use `mparams` as the base so that any entry with an identical
       key in `params` will overwrite it. */
    $params = array_merge ( $mparams, $params );

    //var_dump($params);

    /* Check that all mandatory arguments have been specified. */
    foreach ( array_keys($mparams) as $kval )
    {
        if ( ! isset ( $params[$kval] ))
        {
            $smarty->trigger_error ( "condlink: missing '" . $kval . "' parameter" );
            return;
        }
    }

    if ( $params['condition'] )
    {
        $text = '<a href="?act=' .
                $params['act'] . ',' .
                $params['obj'] . ',' .
                $params['id'] . '">' . $params['text'] . '</a>';
    }
    else
    {
        $text = '<span class="inactive">' . $params['text'] . '</span>';
    }

return $text;
}
?>

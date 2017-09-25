<?php
/**
 * Typeset rational function root factor.
 * The root factor is (x+x_infty) or x.
 */
function smarty_function_rootfactor($params, Smarty_Internal_Templae $template)
{
    /* Get the variable name, i.e. "p" or "s" or "z". */
    $var = $params['var'];

    /* Check that it is not empty. */
    if (empty($var))
    {
        trigger_error("rootfactor: missing 'var' parameter");
        return null;
    }

    /* In case that the value of root is equal to zero, we will not typeset parentheses around the variable. */
    if (empty($params['re']) && empty($params['im']))
    {
        $par_open = $par_close = '';
    }
    else
    {
        $par_open = '(';
        $par_close = ')';
    }

    return $par_open . $var . $params['re'] . $params['im'] . $par_close;
}

?>

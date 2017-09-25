<?php
function smarty_function_throt($params, Smarty_Internal_Template $template)
{
    /* Get the text parameter. */
    $text = $params['text'];

    /* Check that it is not empty. */
    if (empty ($text))
    {
        trigger_error("throt: missing 'text' parameter");
        return null;
    }

    return '<img src="throt.php?text=' . $text .
    '" title="' . $text . '" alt="' . $text . '">';
}

?>

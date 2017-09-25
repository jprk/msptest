<?php
function smarty_block_czech($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    /* This is the string that will be returned to Smarty. */
    $text = '';

    /* Handle only closing tags. */
    if ($content != NULL)
    {
        /* Closing tag, do not reevaluate output. */
        $repeat = false;

        /* Return the text only in case that the application runs in the
           Czech language. */
        if ($template->displayLocale('cs')) $text = $content;
    }

    return $text;
}

?>

<?php
function smarty_block_english($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    /* This is the string that will be returned to Smarty. */
    $text = '';

    /* Handle only closing tags. */
    if ($content != NULL)
    {
        /* Closing tag, do not reevaluate output. */
        $repeat = false;

        /* Return the text only in case that the application runs in the
           English language. */
        if ($template->displayLocale('en')) $text = $content;
    }

    return $text;
}

?>

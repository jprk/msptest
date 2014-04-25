<?php
function smarty_block_czech ( $params, $content, &$smarty, &$repeat )
{
	/* This is the string that will be returned to Smarty. */
	$text = '';
	
	/* Handle only closing tags. */
	if ( $content != NULL )
	{
		/* Closing tag, do not reevaluate output. */
		$repeat = false;
		
		/* Return the text only in case that the application runs in the
		   Czech language. */
		if ( $smarty->_locale == 'cs' ) $text = $content;
	}

	return $text;
}
?>

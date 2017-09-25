<div id="comment" style="line-height: 12px; font-size: 12px;">
<p>
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prikryl
 * Date: 8.11.13
 * Time: 16:42
 * To change this template use File | Settings | File Templates.
 */

/* Global configuration */
require ( 'config.php' );

/* Fetch / initialize session.
   In order to prevent mixing of sessions for different base URLS (live
   and testing web applications on the same machine, or different applications
   on the same machine), we will use a named session. The name of the
   session will be identical to the base directory of the application. */
session_name ( BASE_DIR );
session_start ();

header ("Content-Type: text/html; charset=utf-8");

$subtaskId = $_GET['subtask_id'];
if ( isset ( $subtaskId ))
{
    echo $_SESSION['comment'][$subtaskId];
}
else
{
    echo 'No comment.';
}
?>
</p>

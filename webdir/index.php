<?php
    $server = $_SERVER["SERVER_NAME"];
	$base   = dirname ($_SERVER["REQUEST_URI"])."msaptest/";
	header ("Location: http://".$server.$base."ctrl.php?act=show,home,1");
	exit;
?>

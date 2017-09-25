<?php
// $base = "/var/www/lectbase/";
header("Content-type: image/png");
$string = $_GET["text"];
//$string = iconv ( "windows-1250", "utf-8", $string );
//
//$font  = $base . "fonts/futura/FuMeAT.TTF";
$font  = __DIR__ . DIRECTORY_SEPARATOR . "MyriadPro-Regular.otf";
$size  = 15;
$angle = 0;
// distance between the baseline and "small letter top" line
$bbox = imagettfbbox ( $size, $angle, $font, "x" );
$hx = $bbox[1] - $bbox[7];
// distance from baseline to the upper bbox line
$bbox = imagettfbbox ( $size, $angle, $font, "b" );
$hb = $bbox[1] - $bbox[7];
// distance from the "small letter top" line to the bottom of the bbox
$bbox = imagettfbbox ( $size, $angle, $font, "p" );
$hp = $bbox[1] - $bbox[7];
// but the height is derived from $hx, $hb, $hp
$height = $hb + $hp - $hx + 3;
// width of the image is the width of the text
// $string = $string . " hx=" . $hx . " hb=" . $hb . " hp=" . $hp;
$bbox = imagettfbbox ( $size, $angle, $font, $string );
$width  = $bbox[2] - $bbox[0] + 6;
$im = @imagecreate( $width, $height )
     or die("Cannot Initialize new GD image stream");
$white  = imagecolorallocate($im, 255, 255, 255);
$orange = imagecolorallocate($im, 0xAC, 0x61, 0x09 );
imagettftext($im, $size, $angle, 0, $hb+1, $orange, $font, $string);
imagepng($im);
imagedestroy($im);
?>

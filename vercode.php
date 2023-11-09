<?php
//开启session一般都是在最上方
session_start();

function randstr($length = 4)
{
    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    // 随机打乱$chars的内容
    $str = str_shuffle($chars);
    return substr($str, 0, $length);
}

$width = 80;
$height = 35;

$font = "D:\\phpstudy_pro\\WWW\\PHP\\company\\assets\\font\\OpenSans.ttf";

$img = imagecreatetruecolor($width, $height);

$bgr = mt_rand(0, 255);
$bgg = mt_rand(0, 255);
$bgb = mt_rand(0, 255);
$bgcolor = imagecolorallocate($img, $bgr, $bgg, $bgb);
imagefilledrectangle($img, 0, 0, $width, $height, $bgcolor);
$str = randstr();

$_SESSION['vercode'] = $str;
for ($i = 0; $i < strlen($str); $i++) {
    $textr = mt_rand(0, 255);
    $textg = mt_rand(0, 255);
    $textb = mt_rand(0, 255);
    $textcolor = imagecolorallocate($img, $textr, $textg, $textb);

    $x = 20 * $i;
    $y = 27;
    imagettftext($img, 20, mt_rand(-30, 30), $x, $y, $textcolor, $font, $str[$i]);
}


for ($i = 0; $i < 100; $i++) {
    //随机颜色
    $r = mt_rand(0, 255);
    $g = mt_rand(0, 255);
    $b = mt_rand(0, 255);
    $color = imagecolorallocate($img, $r, $g, $b);

    $x = mt_rand(0, $width);
    $y = mt_rand(0, $height);

    imagesetpixel($img, $x, $y, $color);
}

for ($i = 0; $i < 5; $i++) {
    $r = mt_rand(0, 255);
    $g = mt_rand(0, 255);
    $b = mt_rand(0, 255);
    $color = imagecolorallocate($img, $r, $g, $b);

    $x1 = mt_rand(0, $width);
    $y1 = mt_rand(0, $height);
    $x2 = mt_rand(0, $width);
    $y2 = mt_rand(0, $height);

    imageline($img, $x1, $y1, $x2, $y2, $color);
}


header("Content-Type:image/png");

imagepng($img);

?>
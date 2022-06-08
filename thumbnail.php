<?php
$path = '/var/www/home/post/';
$id = '0YodtrWVD94';
$sku = '0091';
$png = imagecreatefrompng('https://zeroaqua.com/asset/tmp/overlay-min.png');
$jpeg = imagecreatefromjpeg('https://img.youtube.com/vi/'.$id.'/0.jpg');
list($width, $height) = getimagesize('https://img.youtube.com/vi/'.$id.'/0.jpg');
list($newwidth, $newheight) = getimagesize('https://zeroaqua.com/asset/tmp/overlay-min.png');
$out = imagecreatetruecolor($newwidth/2, $newheight/2);
imagecopyresampled($out, $jpeg, 0, 0, 0, 0, $newwidth/2, $newheight/2, $width, $height);
imagecopyresampled($out, $png, 0, 0, 0, 0, $newwidth/2, $newheight/2, $newwidth, $newheight);
imagejpeg($out, $path.'files/'.$sku.'-youtube-thumbnail.jpg', 100);
?>
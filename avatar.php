<?php

$name = isset($_GET['name']) ? trim($_GET['name']) : 'A';


$letter = strtoupper(mb_substr($name, 0, 1));

$imgWidth = 100;
$imgHeight = 100;
$colors = [
    '#1abc9c', '#2ecc71', '#3498db', '#9b59b6',
    '#34495e', '#16a085', '#27ae60', '#2980b9',
    '#8e44ad', '#2c3e50', '#f39c12', '#d35400',
    '#c0392b', '#7f8c8d'
];

$colorIndex = (ord($letter) - 65) % count($colors);
$bgColorHex = $colors[$colorIndex];


function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 3) {
        $r = hexdec(str_repeat(substr($hex,0,1),2));
        $g = hexdec(str_repeat(substr($hex,1,1),2));
        $b = hexdec(str_repeat(substr($hex,2,1),2));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return [$r, $g, $b];
}

$bgColor = hexToRgb($bgColorHex);

$im = imagecreatetruecolor($imgWidth, $imgHeight);

$bg = imagecolorallocate($im, $bgColor[0], $bgColor[1], $bgColor[2]);
imagefill($im, 0, 0, $bg);

$white = imagecolorallocate($im, 255, 255, 255);

$fontPath = __DIR__ . '/fonts/OpenSans-Bold.ttf';

if (!file_exists($fontPath)) {
    $fontSize = 5;
    $textBox = imageftbbox(20, 0, $fontPath, $letter);
    $x = ($imgWidth - imagefontwidth($fontSize) * strlen($letter)) / 2;
    $y = ($imgHeight - imagefontheight($fontSize)) / 2;
    imagestring($im, $fontSize, $x, $y, $letter, $white);
} else {
    $fontSize = 50;

    $bbox = imagettfbbox($fontSize, 0, $fontPath, $letter);
    $textWidth = $bbox[2] - $bbox[0];
    $textHeight = $bbox[7] - $bbox[1];
    $x = ($imgWidth / 2) - ($textWidth / 2);
    $y = ($imgHeight / 2) - ($textHeight / 2);
    $y = $y - $bbox[7]; 

    imagettftext($im, $fontSize, 0, $x, $y, $white, $fontPath, $letter);
}

header('Content-Type: image/png');
header('Cache-Control: max-age=86400'); 

imagepng($im);

imagedestroy($im);
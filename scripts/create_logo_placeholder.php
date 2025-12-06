<?php
// [AGENTE: OPUS 4.5] - Script para crear logo placeholder PNG
$width = 200;
$height = 60;
$img = imagecreatetruecolor($width, $height);

// Colores
$bg = imagecolorallocate($img, 30, 58, 95); // Azul oscuro
$white = imagecolorallocate($img, 255, 255, 255);

// Fondo con esquinas (fill simple)
imagefill($img, 0, 0, $bg);

// Texto centrado
$text = 'LTS Academy';
$font_size = 5; // Fuente built-in de GD
$text_width = imagefontwidth($font_size) * strlen($text);
$x = ($width - $text_width) / 2;
$y = ($height - imagefontheight($font_size)) / 2;
imagestring($img, $font_size, $x, $y, $text, $white);

// Guardar
$output_path = dirname(__DIR__) . '/public/images/logo.png';
imagepng($img, $output_path);
imagedestroy($img);

echo "Logo creado en: $output_path\n";
echo "Tamaño: " . filesize($output_path) . " bytes\n";


<?php
/**
 * Placeholder image generator
 * Run this script once to generate placeholder images
 * http://localhost/online_bookstore/assets/images/placeholder.php
 */

function createPlaceholderImage($filename, $text, $width = 200, $height = 280, $bgColor = [0x0d, 0x6e, 0xfd]) {
    if (!extension_loaded('gd')) {
        file_put_contents($filename, '');
        return;
    }
    
    $image = imagecreatetruecolor($width, $height);
    $bg = imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);
    $white = imagecolorallocate($image, 255, 255, 255);
    $gray = imagecolorallocate($image, 200, 200, 200);
    
    imagefill($image, 0, 0, $bg);
    
    // Draw a book icon
    $icon_size = 60;
    $cx = ($width - $icon_size) / 2;
    $cy = ($height - $icon_size) / 2 - 20;
    imagerectangle($image, $cx, $cy, $cx + $icon_size, $cy + $icon_size * 1.3, $white);
    imagerectangle($image, $cx + 5, $cy + 5, $cx + $icon_size - 5, $cy + $icon_size * 1.3 - 5, $white);
    imageline($image, $cx + $icon_size / 2, $cy + 5, $cx + $icon_size / 2, $cy + $icon_size * 1.3 - 5, $white);
    
    // Draw text
    $font_size = 4;
    $text_x = ($width - imagefontwidth($font_size) * strlen($text)) / 2;
    $text_y = $cy + $icon_size * 1.3 + 20;
    imagestring($image, $font_size, $text_x, $text_y, $text, $white);
    
    imagepng($image, $filename);
    imagedestroy($image);
}

$dir = __DIR__;
createPlaceholderImage($dir . '/default-book.png', 'Book', 200, 280, [13, 110, 253]);
createPlaceholderImage($dir . '/default.png', 'User', 120, 120, [108, 117, 125]);

echo "Placeholder images created successfully!";

<?php

header('Content-type: image/png');

$height = 1715;
$width = 1920;

$show = $_GET['show'];
$draw = new ImagickDraw();

$draw->setStrokeAntialias(true);  //try with and without
$draw->setTextAntialias(true);  //try with and without

$draw = new ImagickDraw();
$draw->setFillColor('#fff');

/* Font properties */
$draw->setFont('Droid-Sans');
$draw->setFontSize( 420 );

$draw->setStrokeColor('#000');
$draw->setStrokeWidth(4);
$draw->setStrokeAntialias(true);  //try with and without
$draw->setTextAntialias(true);  //try with and without
$draw->setGravity(imagick::GRAVITY_SOUTH);
$outputImage = new Imagick();
$outputImage->newImage($height, $width, "transparent");  //transparent canvas
$outputImage->annotateImage($draw, 0, 0, 0, $show);
$outputImage->trimImage(0); //Cut off transparent border
//$outputImage->resizeImage(300,0, imagick::FILTER_CATROM, 0.9, false);


$image = new Imagick('images/radio-backdrop.png');

$outputImage->compositeImage($image, imagick::COLOR_ALPHA, 0, 0);
// If 0 is provided as a width or height parameter,
// aspect ratio is maintained
//$image->annotateImage($draw, 10, 45, 0, $show);
//$image->thumbnailImage(100, 0);

echo $image;


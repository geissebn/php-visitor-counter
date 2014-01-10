<?php
require_once 'Counter.class.php';
require_once 'Configuration.class.php';

$counter = new Counter();
$parts = loadPartImages($counter->asText());

$w = calcTargetWidth($parts);
$h = calcTargetHeight($parts);
$target = imagecreatetruecolor($w, $h);
applyTransparencyFix($target, $w, $h);
assemblePartImages($target, $parts);

// Output 
header('Content-Type: image/png');
imagepng($target); // image stream will be outputted directly
imagedestroy($target);

function loadPartImages($number) {
  $result = array();
  foreach (str_split($number) as $digit) {
    // Load image and determine size
    $image = imagecreatefrompng(getImageLocation($digit, Configuration::getStyle()));
    $w = imagesx($image);
    $h = imagesy($image);
  
    // store for later usage
    $result[] = array('img' => $image, 'w' => $w, 'h' => $h);
  }
  return $result;
}

function getImageLocation($digit, $style) {
  return dirname(__FILE__) . '/styles/' . $style . '/' . $digit . '.png';
}
function calcTargetWidth($partImages) {
  $result = 0;  
  foreach ($partImages as $part) {
    $result += $part['w'];
  }
  return $result + (count($partImages) - 1) * Configuration::getExtraGap();
}

function calcTargetHeight($partImages) {
  $result = 0;  
  foreach ($partImages as $part) {
    $result = max($result, $part['h']);
  }
  return $result;
}

function applyTransparencyFix($image, $width, $height) {
  $transparent = imagecolorallocatealpha($image, 253, 253, 253, 0);
  imagefilledrectangle($image, 0, 0, $width, $height, $transparent);
  imagefill($image, 2, 2, $transparent);
  imagecolortransparent($image, $transparent);
}

function assemblePartImages($target, $parts) {
  $offset = 0;
  foreach ($parts as $part) {
    imagecopy($target, $part['img'], $offset, 0, 0, 0, $part['w'], $part['h']);
    $offset += $part['w'] + Configuration::getExtraGap();
  }
}

?>
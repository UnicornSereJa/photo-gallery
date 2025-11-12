<?php
$config = require __DIR__ . '/config.php';

function isImageType($mime) {
    global $config;
    return in_array($mime, $config['allowed_types']);
}

function uniqueFilename($dir, $name) {
    $path = $dir . $name;
    if (!file_exists($path)) return $name;
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $base = pathinfo($name, PATHINFO_FILENAME);
    $i = 1;
    do {
        $new = $base . '-' . ($i++) . '.' . $ext;
        $path = $dir . $new;
    } while (file_exists($path));
    return $new;
}

function saveMetadata($meta) {
    global $config;
    $file = $config['data_file'];
    if (!file_exists(dirname($file))) {
        @mkdir(dirname($file), 0755, true);
    }
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    $content = file_get_contents($file);
    $arr = json_decode($content, true);
    if (!is_array($arr)) $arr = [];
    array_unshift($arr, $meta);
    file_put_contents($file, json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function createImageResource($path, $mime = null) {
    if ($mime === null) $mime = @mime_content_type($path);
    if ($mime === 'image/jpeg' || $mime === 'image/jpg') return @imagecreatefromjpeg($path);
    if ($mime === 'image/png') return @imagecreatefrompng($path);
    if ($mime === 'image/gif') return @imagecreatefromgif($path);
    return false;
}

function saveResourceToFile($res, $path, $mime) {
    if ($mime === 'image/jpeg' || $mime === 'image/jpg') imagejpeg($res, $path, 85);
    elseif ($mime === 'image/png') imagepng($res, $path);
    elseif ($mime === 'image/gif') imagegif($res, $path);
}

function addWatermarkToImage($srcPath, $destPath, $mime) {
    global $config;
    $img = createImageResource($srcPath, $mime);
    if (!$img) return false;

    $w = imagesx($img);
    $h = imagesy($img);


    $wmPath = $config['watermark'];
    $wmMime = @mime_content_type($wmPath);
    $wm = createImageResource($wmPath, $wmMime);
    if (!$wm) return false;

    $ww = imagesx($wm);
    $wh = imagesy($wm);

    $maxWidth = $w * 0.3; 
    if ($ww > $maxWidth) {
        $ratio = $maxWidth / $ww;
        $newW = (int)($ww * $ratio);
        $newH = (int)($wh * $ratio);
        $resizedWM = imagecreatetruecolor($newW, $newH);
        imagealphablending($resizedWM, false);
        imagesavealpha($resizedWM, true);
        imagecopyresampled($resizedWM, $wm, 0, 0, 0, 0, $newW, $newH, $ww, $wh);
        imagedestroy($wm);
        $wm = $resizedWM;
        $ww = $newW;
        $wh = $newH;
    }

    $posx = $w - $ww;
    $posy = $h - $wh;

    imagealphablending($img, true);
    imagesavealpha($img, true);
    imagecopy($img, $wm, $posx, $posy, 0, 0, $ww, $wh);

    saveResourceToFile($img, $destPath, $mime);

    imagedestroy($img);
    imagedestroy($wm);

    return true;
}


function createThumbnailWithDate($srcPath, $destPath, $mime) {
    global $config;
    $img = createImageResource($srcPath, $mime);
    if (!$img) return false;

    $w = imagesx($img);
    $h = imagesy($img);

    $newW = (int)$config['thumb_width'];
    if ($newW <= 0) $newW = 300;
    $ratio = $newW / $w;
    $newH = (int)($h * $ratio);

    $thumb = imagecreatetruecolor($newW, $newH);

    if ($mime === 'image/png' || $mime === 'image/gif') {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        imagefilledrectangle($thumb, 0, 0, $newW, $newH, $transparent);
    } else {
        $white = imagecolorallocate($thumb, 255, 255, 255);
        imagefilledrectangle($thumb, 0, 0, $newW, $newH, $white);
    }

    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);


    $text = date('d.m.Y H:i');
    $font = $config['font_file'] ?? __DIR__ . '/fonts/Roboto-Regular.ttf';
    $size = 12;
    $angle = 0;

    if (file_exists($font)) {
        $bbox = imagettfbbox($size, $angle, $font, $text);
        $txtW = $bbox[2] - $bbox[0];
        $x = max(8, $newW - $txtW - 8);
        $y = $newH - 8;
        $color = imagecolorallocate($thumb, 255, 255, 255);
        $shadow = imagecolorallocate($thumb, 0, 0, 0);
        imagettftext($thumb, $size, $angle, $x+1, $y+1, $shadow, $font, $text);
        imagettftext($thumb, $size, $angle, $x, $y, $color, $font, $text);
    } else {
        $color = imagecolorallocate($thumb, 255, 255, 255);
        imagestring($thumb, 3, 8, $newH - 20, $text, $color);
    }

    saveResourceToFile($thumb, $destPath, $mime);
    imagedestroy($thumb);
    imagedestroy($img);
    return true;
}

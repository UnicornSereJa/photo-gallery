<?php
require __DIR__ . '/functions.php';
$config = require __DIR__ . '/config.php';

$file = $_GET['file'] ?? null;
if (!$file) {
    header('Location: index.php');
    exit;
}

$fullPath = $config['upload_dir'] . basename($file);
$thumbPath = $config['thumb_dir'] . basename($file);

if (file_exists($fullPath)) unlink($fullPath);
if (file_exists($thumbPath)) unlink($thumbPath);

$dataFile = $config['data_file'];
$images = json_decode(file_get_contents($dataFile), true) ?: [];
$images = array_filter($images, fn($img) => $img['file'] !== $file);
file_put_contents($dataFile, json_encode(array_values($images), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

header('Location: index.php');
exit;

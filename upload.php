<?php
require __DIR__ . '/functions.php';
$config = require __DIR__ . '/config.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: index.php');exit;}
if(!isset($_FILES['image']) || $_FILES['image']['error']!==UPLOAD_ERR_OK){header('Location: index.php?err=upload');exit;}

$file=$_FILES['image'];
$desc=trim($_POST['description'] ?? '');
$maxLength=100;
if(mb_strlen($desc)>$maxLength){
    $desc=mb_substr($desc,0,$maxLength).'…';
}

$mime=mime_content_type($file['tmp_name']);
if(!isImageType($mime)){header('Location: index.php?err=type');exit;}

$origName=basename($file['name']);
$safeName=preg_replace('/[^A-Za-z0-9_.-]/','_',$origName);
$unique=uniqueFilename($config['upload_dir'],$safeName);

move_uploaded_file($file['tmp_name'],$config['upload_dir'].$unique);
addWatermarkToImage($config['upload_dir'].$unique,$config['upload_dir'].$unique,$mime);
createThumbnailWithDate($config['upload_dir'].$unique,$config['thumb_dir'].$unique,$mime);

$meta=['file'=>$unique,'description'=>$desc,'mime'=>$mime,'uploaded'=>date('c')];
saveMetadata($meta);

header('Location: index.php?ok=1');
exit;

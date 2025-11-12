<?php
$config = require __DIR__ . '/config.php';
$file = $_GET['file'] ?? null;
if(!$file){header('Location: index.php');exit;}
$path = $config['upload_dir'] . basename($file);
if(!file_exists($path)){header('Location: index.php');exit;}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Просмотр</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="viewer">
<a href="index.php" class="close">&times;</a>
<img src="full/<?php echo htmlspecialchars(basename($file)); ?>" alt="">
</div>
</body>
</html>
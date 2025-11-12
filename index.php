<?php
$config = require __DIR__ . '/config.php';
$images = json_decode(file_get_contents($config['data_file']), true) ?: [];
$q = trim($_GET['q'] ?? '');
if($q!==''){
    $images = array_filter($images, fn($img)=>stripos($img['description'],$q)!==false);
}
$page = max(1, (int)($_GET['page'] ?? 1));
$per = (int)$config['per_page'];
$total = count($images);
$pages = (int)ceil($total / $per);
$start = ($page-1)*$per;
$visible = array_slice($images,$start,$per);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Галерея</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
<h1>Фото-галерея</h1>
<?php
$totalSize=0;
foreach($images as $img){
    $path=$config['upload_dir'].$img['file'];
    if(file_exists($path))$totalSize+=filesize($path);
}
$sizeMb=round($totalSize/1048576,2);
?>
<div class="stats">Всего изображений: <?php echo count($images); ?> (<?php echo $sizeMb; ?> МБ)</div>

<div id="drop-zone">
    <form action="upload.php" method="post" enctype="multipart/form-data" class="upload-form">
        <input type="file" name="image" id="fileInput" required>
        <input type="text" name="description" placeholder="Описание">
        <button type="submit">Загрузить</button>
    </form>
</div>
<form action="" method="get" class="search-form">
    <input type="text" name="q" placeholder="Поиск по описанию" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
    <button type="submit">Найти</button>
</form>
<div class="gallery">
<?php foreach($visible as $item): ?>
<div class="card">
    <a href="view.php?file=<?php echo urlencode($item['file']); ?>" class="thumb-link">
        <img src="thumbnails/<?php echo htmlspecialchars($item['file']); ?>" alt="">
    </a>
    <div class="desc"><?php echo htmlspecialchars($item['description']); ?></div>
    <a href="delete.php?file=<?php echo urlencode($item['file']); ?>" class="delete-btn" onclick="return confirm('Удалить это изображение?');">Удалить</a>
</div>

<?php endforeach; 
/*
<form action="delete.php" method="get" onsubmit="return confirm('Удалить это изображение?');">
    <input type="hidden" name="file" value="<?php echo htmlspecialchars($item['file']); ?>">
    <button type="submit" class="delete-btn">Удалить</button>
</form>
*/
?>
</div>
<div class="pagination">
<?php if($page>1): ?><a href="?page=<?php echo $page-1; ?>">&laquo; Prev</a><?php endif; ?>
<?php for($p=1;$p<=$pages;$p++): ?>
<a href="?page=<?php echo $p; ?>"<?php if($p==$page)echo ' class="active"'; ?>><?php echo $p; ?></a>
<?php endfor; ?>
<?php if($page<$pages): ?><a href="?page=<?php echo $page+1; ?>">Next &raquo;</a><?php endif; ?>
</div>
</div>
<script src="assets/js/script.js"></script>
</body>
</html>
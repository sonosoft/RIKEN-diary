<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Elnath | CACHE MANAGER</title>
</head>

<?php

/*
 * キャッシュファイル一覧
 */
function collectCacheFiles($root, $directory, &$files){
  if(empty($directory)){
    $dirname = $root;
  }else{
    $dirname = $root.DIRECTORY_SEPARATOR.$directory;
  }
  if(($dir = opendir($dirname)) !== false){
    while(($file = readdir($dir)) !== false){
      if(strcmp($file, '.') != 0 && strcmp($file, '..') != 0){
	$path = $dirname.DIRECTORY_SEPARATOR.$file;
	if(empty($directory)){
	  $name = $file;
	}else{
	  $name = $directory.DIRECTORY_SEPARATOR.$file;
	}
	if(is_dir($path)){
	  collectCacheFiles($root, $name, $files);
	}else if(is_file($path)){
	  $files[] = $name;
	}
      }
    }
    closedir($dir);
  }
}

/* キャッシュルート */
$cachePath = array(
		   __DIR__,
		   'app',
		   'var',
		   'cache',
		   );
$cacheRoot = implode(DIRECTORY_SEPARATOR, $cachePath);

/* 削除リクエスト */
if(isset($_REQUEST['cf'])){
  unlink($cacheRoot.DIRECTORY_SEPARATOR.base64_decode(urldecode($_REQUEST['cf'])));
}

/* キャッシュファイル一覧 */
$modelCacheRoot = $cacheRoot.DIRECTORY_SEPARATOR.'models';
$modelCacheFiles = array();
collectCacheFiles($modelCacheRoot, '', $modelCacheFiles);

$viewCacheRoot = $cacheRoot.DIRECTORY_SEPARATOR.'views';
$viewCacheFiles = array();
collectCacheFiles($viewCacheRoot, '', $viewCacheFiles);

?>

<style>
table {
  margin-bottom: 25px;
  border: 1px solid #717171;
  border-collapse: collapse;
  font-size: 13px;
}
table th, table td {
  border: 1px solid #717171;
  padding: 2px 10px;
}
</style>
<body>

<table>
<tr>
  <th>モデルキャッシュ</th>
  <th></th>
</tr>
<?php foreach($modelCacheFiles as $file){ ?>
<?php $arg = 'models'.DIRECTORY_SEPARATOR.$file; ?>
<tr>
  <td><?php echo htmlspecialchars($file); ?></td>
  <td><a href="_cache_manager.php?cf=<?php echo urlencode(base64_encode($arg)); ?>">削除</a></td>
</tr>
<?php } ?>
</table>

<table>
<tr>
  <th>ビューキャッシュ</th>
  <th></th>
</tr>
<?php foreach($viewCacheFiles as $file){ ?>
<?php $arg = 'views'.DIRECTORY_SEPARATOR.$file; ?>
<tr>
  <td><?php echo htmlspecialchars($file); ?></td>
  <td><a href="_cache_manager.php?cf=<?php echo urlencode(base64_encode($arg)); ?>">削除</a></td>
</tr>
<?php } ?>
</table>

</body>

</html>

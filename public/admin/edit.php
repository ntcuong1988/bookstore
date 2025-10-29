<?php
require_once __DIR__.'/../../vendor/autoload.php';
App\Auth::requireAdmin();
$repo=new App\BookRepository(App\Database::getConnection()); $id=(int)($_GET['id']??0); $book=$repo->find($id); if(!$book){ http_response_code(404); die('Not found'); }
$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!App\Security::checkCsrf($_POST['csrf'] ?? '')){ http_response_code(400); die('Bad CSRF'); }
  $sku=trim($_POST['sku']??''); $title=trim($_POST['title']??''); $author=trim($_POST['author']??''); $price=(float)($_POST['price']??0);
  $stock=(int)($_POST['stock']??999); $description=trim($_POST['description']??'');
  if($title && $author && $price>=0){ $repo->update($id,$title,$author,$price,$description,$sku?:null,$stock); header('Location: /admin/index.php'); exit; } else { $error='Thiếu dữ liệu hợp lệ'; }
}
ob_start(); ?>
<h3>Sửa sách #<?= (int)$book['id'] ?></h3>
<?php if($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input name="sku" value="<?= htmlspecialchars($book['sku'] ?? '') ?>">
  <input name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
  <input name="author" value="<?= htmlspecialchars($book['author']) ?>" required>
  <input name="price" type="number" step="1000" value="<?= htmlspecialchars($book['price']) ?>" required>
  <input name="stock" type="number" step="1" value="<?= (int)$book['stock'] ?>">
  <textarea name="description"><?= htmlspecialchars($book['description']) ?></textarea>
  <input type="hidden" name="csrf" value="<?= App\Security::csrfToken() ?>">
  <button>Lưu</button>
</form>
<?php $content=ob_get_clean(); include __DIR__.'/../_layout.php';
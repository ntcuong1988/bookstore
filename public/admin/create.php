<?php
require_once __DIR__.'/../../vendor/autoload.php';
App\Auth::requireAdmin();
$repo=new App\BookRepository(App\Database::getConnection()); $error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!App\Security::checkCsrf($_POST['csrf'] ?? '')){ http_response_code(400); die('Bad CSRF'); }
  $sku=trim($_POST['sku']??''); $title=trim($_POST['title']??''); $author=trim($_POST['author']??''); $price=(float)($_POST['price']??0);
  $stock=(int)($_POST['stock']??999); $description=trim($_POST['description']??'');
  if($title && $author && $price>=0){ $repo->create($title,$author,$price,$description,$sku?:null,$stock); header('Location: /admin/index.php'); exit; } else { $error='Thiếu dữ liệu hợp lệ'; }
}
ob_start(); ?>
<h3>Thêm sách</h3>
<?php if($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input name="sku" placeholder="SKU (vd BK010)">
  <input name="title" placeholder="Tiêu đề" required>
  <input name="author" placeholder="Tác giả" required>
  <input name="price" type="number" step="1000" placeholder="Giá" required>
  <input name="stock" type="number" step="1" placeholder="Kho" value="999">
  <textarea name="description" placeholder="Mô tả"></textarea>
  <input type="hidden" name="csrf" value="<?= App\Security::csrfToken() ?>">
  <button>Lưu</button>
</form>
<?php $content=ob_get_clean(); include __DIR__.'/../_layout.php';
<?php
require_once __DIR__.'/../../vendor/autoload.php';
App\Auth::requireAdmin();
$repo=new App\BookRepository(App\Database::getConnection());
$books=$repo->all(0,1000,null);
ob_start(); ?>
<h3>Quản trị sách</h3>
<p><a href="/admin/create.php">+ Thêm sách</a></p>
<table><thead><tr><th>ID</th><th>SKU</th><th>Tiêu đề</th><th>Tác giả</th><th>Giá</th><th>Kho</th><th></th></tr></thead><tbody>
<?php foreach($books as $b): ?>
<tr>
<td><?= (int)$b['id'] ?></td>
<td><?= htmlspecialchars($b['sku'] ?? '') ?></td>
<td><?= htmlspecialchars($b['title']) ?></td>
<td><?= htmlspecialchars($b['author']) ?></td>
<td><?= number_format($b['price'],0) ?> đ</td>
<td><?= (int)$b['stock'] ?></td>
<td>
  <a href="/admin/edit.php?id=<?= (int)$b['id'] ?>">Sửa</a>
  <form method="post" action="/admin/delete.php" class="inline" onsubmit="return confirm('Xoá?')">
    <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
    <input type="hidden" name="csrf" value="<?= App\Security::csrfToken() ?>">
    <button>Xoá</button>
  </form>
</td>
</tr>
<?php endforeach; ?></tbody></table>
<?php $content=ob_get_clean(); include __DIR__ . '/../_layout.php';
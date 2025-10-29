<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Database; use App\BookRepository; use App\Paginator;
$pdo = Database::getConnection();
$repo = new BookRepository($pdo);
$q = isset($_GET['q']) ? trim($_GET['q']) : null;
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$perPage = 6;
$total = $repo->countAll($q);
$pg = Paginator::meta($total, $page, $perPage);
$books = $repo->all($pg['offset'], $pg['perPage'], $q);
ob_start();
?>
<h3>Danh sách sách</h3>
<form method="get">
  <input name="q" placeholder="Tìm theo tiêu đề/tác giả..." value="<?= htmlspecialchars($q ?? '') ?>">
  <button>Tìm</button>
</form>
<p><em>Tổng: <?= $total ?> quyển</em></p>
<div class="grid">
<?php foreach ($books as $b): ?>
  <article class="card" id="card_<?= (int)$b['id'] ?>">
    <h4><?= htmlspecialchars($b['title']) ?></h4>
    <p>SKU: <strong><?= htmlspecialchars($b['sku'] ?? '') ?></strong></p>
    <p>Tác giả: <?= htmlspecialchars($b['author']) ?></p>
    <p>Kho: <?= (int)$b['stock'] ?></p>
    <p><strong><?= number_format($b['price'],0) ?> đ</strong></p>
    <a href="/book.php?id=<?= (int)$b['id'] ?>">Xem chi tiết</a>
    <form class="inline" method="post" action="/cart_add.php">
      <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
      <input type="hidden" name="csrf" value="<?= App\Security::csrfToken() ?>">
      <button aria-label="Thêm <?= htmlspecialchars($b['title']) ?> vào giỏ">Thêm vào giỏ hàng</button>
    </form>
  </article>
<?php endforeach; ?>
</div>
<?php if ($pg['pages']>1): ?>
<nav>
  <?php for ($i=1; $i<=$pg['pages']; $i++): ?>
    <a href="?q=<?= urlencode($q ?? '') ?>&page=<?= $i ?>" <?= $i===$pg['page']?'class="badge"':'' ?>><?= $i ?></a>
  <?php endfor; ?>
</nav>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/_layout.php';

<?php
require_once __DIR__.'/../vendor/autoload.php';
$items=App\Cart::items(); $total=App\Cart::total();
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['clear'])){ if(!App\Security::checkCsrf($_POST['csrf'] ?? '')){ http_response_code(400); die('Bad CSRF'); } App\Cart::clear(); $_SESSION['flash']=['type'=>'success','msg'=>'Đã xoá giỏ hàng']; header('Location: /cart.php'); exit; }
ob_start(); ?>
<h3>Giỏ hàng (<span class="total-number"><?= App\Cart::count() ?></span>)</h3>
<?php if(empty($items)): ?>
<p>Giỏ hàng trống. <a href="/index.php">Mua sắm ngay</a></p>
<?php else: ?>
<table>
<thead><tr><th>Tên sách</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
<tbody>
<?php foreach($items as $row): ?>
<tr id="item_<?= htmlspecialchars($row['book']['id']) ?>">
  <td class="item-title"><?= htmlspecialchars($row['book']['title']) ?></td>
  <td class="item-qty"><?= (int)$row['qty'] ?></td>
  <td class="item-price"><?= number_format($row['book']['price'],0) ?> đ</td>
  <td class="item-total"><?= number_format($row['book']['price'] * $row['qty'],0) ?> đ</td>
</tr>
<?php endforeach; ?>
</tbody></table>
<p class="cart-total"><strong>Tổng: <?= number_format($total,0) ?> đ</strong></p>
<form method="post" class="inline"><input type="hidden" name="csrf" value="<?= App\Security::csrfToken() ?>"><button name="clear" value="1">Xoá giỏ hàng</button></form>
<?php endif; ?>
<?php $content=ob_get_clean(); include __DIR__.'/_layout.php';
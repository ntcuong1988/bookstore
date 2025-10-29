<?php
use App\Auth;
use App\Security;
Security::ensureSession();
$cartCount=0;
if(isset($_SESSION['cart'])){ foreach($_SESSION['cart'] as $row){ $cartCount += $row['qty']; } }
$user = Auth::user();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bookstore Mini+</title>
  <link rel="stylesheet" href="https://unpkg.com/mvp.css">
  <style>
    header{display:flex;gap:1rem;align-items:center;}
    .badge{background:#222;color:#fff;padding:.2rem .5rem;border-radius:999px;font-size:.8rem;}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;}
    .card{border:1px solid #ddd;padding:1rem;border-radius:.75rem;}
    nav a{margin-left: .6rem;}
    form.inline{display:inline;}
  </style>
</head>
<body>
<header>
  <h2><a href="/index.php">📚 Bookstore Mini+</a></h2>
  <nav style="margin-left:auto">
    <a href="/cart.php">🛒 Giỏ hàng <span class="badge"><?= $cartCount ?></span></a>
    <?php if ($user): ?>
      <span class="user-name">Chào: <?= htmlspecialchars($user['username']) ?></span>
      <?php if ($user['role']==='admin'): ?><a href="/admin/index.php">⚙️ Admin</a><?php endif; ?>
      <a href="/logout.php">Đăng xuất</a>
    <?php else: ?>
      <a href="/login.php">Đăng nhập</a>
    <?php endif; ?>
  </nav>
</header>
<main>
  <?php if (!empty($_SESSION['flash'])): $f=$_SESSION['flash']; unset($_SESSION['flash']); ?>
    <h4 style="color:red"><div class="toast <?= $f['type']==='success'?'success':'error' ?>" role="status" aria-live="polite"><?= htmlspecialchars($f['msg']) ?></div></h4>
  <?php endif; ?>
  <?= $content ?? '' ?>
</main>
<footer><small>Demo BookStore</small></footer>
</body></html>
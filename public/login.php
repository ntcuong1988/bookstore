<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Auth; use App\Security;
$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!Security::checkCsrf($_POST['csrf'] ?? '')){ http_response_code(400); die('Bad CSRF');}
  $u=trim($_POST['username']??''); $p=trim($_POST['password']??'');
  if(Auth::login($u,$p)){ header('Location: /index.php'); exit; } else { $error='Sai tài khoản hoặc mật khẩu'; }
}
ob_start();
?>
<h3>Đăng nhập</h3>
<?php if($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input name="username" placeholder="Tài khoản" required>
  <input name="password" type="password" placeholder="Mật khẩu" required>
  <input type="hidden" name="csrf" value="<?= App\Security::csrfToken() ?>">
  <button name="login">Đăng nhập</button>
</form>
<p><em>Admin mặc định: <code>admin / admin123</code></em></p>
<?php
$content = ob_get_clean();
include __DIR__ . '/_layout.php';

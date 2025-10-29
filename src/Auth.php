<?php
namespace App;
use App\Database;
class Auth {
  public static function login(string $username, string $password): bool {
    Security::ensureSession();
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $u = $stmt->fetch();
    if ($u && password_verify($password, $u['password_hash'])) {
      $_SESSION['user'] = ['id'=>$u['id'],'username'=>$u['username'],'role'=>$u['role']];
      return true;
    }
    return false;
  }
  public static function logout(): void {
    Security::ensureSession();
    unset($_SESSION['user']);
  }
  public static function user(): ?array {
    Security::ensureSession();
    return $_SESSION['user'] ?? null;
  }
  public static function requireAdmin(): void {
    $u = self::user();
    if (!$u || $u['role'] !== 'admin') {
      header('Location: /login.php');
      exit;
    }
  }
  public static function isAdmin(): bool {
    $u = self::user(); return $u && $u['role']==='admin';
  }
}

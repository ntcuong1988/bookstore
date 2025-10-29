<?php
namespace App;
class Security {
  public static function ensureSession(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
  }
  public static function csrfToken(): string {
    self::ensureSession();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
  }
  public static function checkCsrf(string $token): bool {
    self::ensureSession();
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
  }
}

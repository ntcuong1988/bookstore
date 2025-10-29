<?php
namespace App;
require_once __DIR__ . '/../config/config.php';
use PDO; use PDOException;
class Database {
  public static function getConnection(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $opt = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
    return new PDO($dsn, DB_USER, DB_PASS, $opt);
  }
}

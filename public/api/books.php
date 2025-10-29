<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use App\Database; use App\BookRepository;
header('Content-Type: application/json; charset=utf-8');
$pdo = Database::getConnection();
$repo = new BookRepository($pdo);
$q = isset($_GET['q']) ? trim($_GET['q']) : null;
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$perPage = isset($_GET['perPage']) ? max(1,(int)$_GET['perPage']) : 10;
$total = $repo->countAll($q);
$pages = max(1, (int)ceil($total / $perPage));
$offset = ($page-1)*$perPage;
$data = $repo->all($offset,$perPage,$q);
echo json_encode(['data'=>$data,'meta'=>['total'=>$total,'page'=>$page,'pages'=>$pages,'perPage'=>$perPage]], JSON_UNESCAPED_UNICODE);

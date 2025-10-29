<?php
require_once __DIR__.'/../../vendor/autoload.php';
App\Auth::requireAdmin();
if($_SERVER['REQUEST_METHOD']!=='POST'){ http_response_code(405); exit; }
if(!App\Security::checkCsrf($_POST['csrf'] ?? '')){ http_response_code(400); die('Bad CSRF'); }
$repo=new App\BookRepository(App\Database::getConnection()); $repo->delete((int)($_POST['id']??0)); header('Location: /admin/index.php');
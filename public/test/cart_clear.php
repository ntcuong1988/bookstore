<?php
require_once __DIR__.'/../../vendor/autoload.php';
if(getenv('APP_ENV')!=='test'){ http_response_code(403); exit('FORBIDDEN'); }
App\Security::ensureSession(); $_SESSION['cart']=[]; header('Content-Type: text/plain; charset=utf-8'); echo 'OK';
<?php
require_once __DIR__ . '/../vendor/autoload.php';
App\Auth::logout();
header('Location: /index.php');

<?php
require_once __DIR__ . "/../../vendor/autoload.php";
header("Content-Type: application/json; charset=utf-8");
// BẬT 503 theo query khi test
if (($_GET['simulate'] ?? '') === '503') {
    http_response_code(503);
    echo json_encode(['error' => 'Không thể thêm sản phẩm, vui lòng thử lại sau'],JSON_UNESCAPED_UNICODE);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "method"]);
    exit();
}
if (!App\Security::checkCsrf($_POST["csrf"] ?? "")) {
    http_response_code(400);
    echo json_encode(["error" => "csrf"]);
    exit();
}
$id = (int) ($_POST["id"] ?? 0);
$qty = max(1, (int) ($_POST["qty"] ?? 1));
$repo = new App\BookRepository(App\Database::getConnection());
$book = $repo->find($id);
if (!$book) {
    http_response_code(404);
    echo json_encode(["error" => "not_found"]);
    exit();
}
if ((int) $book["stock"] <= 0) {
    http_response_code(409);
    echo json_encode(["error" => "out_of_stock"]);
    exit();
}
App\Cart::add($book, $qty);
echo json_encode(
    ["ok" => true, "items" => App\Cart::count(), "total" => App\Cart::total()],
    JSON_UNESCAPED_UNICODE
);

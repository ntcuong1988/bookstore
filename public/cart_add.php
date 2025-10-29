<?php
require_once __DIR__ . "/../vendor/autoload.php";
if (!App\Security::checkCsrf($_POST["csrf"] ?? "")) {
    http_response_code(400);
    die("Bad CSRF");
}
$id = (int) ($_POST["id"] ?? 0);
$qty = max(1, (int) ($_POST["qty"] ?? 1));
$repo = new App\BookRepository(App\Database::getConnection());
$book = $repo->find($id);
if (!$book) {
    http_response_code(404);
    die("Not found");
}
$_SESSION["cart"] = $_SESSION["cart"] ?? [];
$id = $book["id"];
if (isset($_SESSION["cart"][$id])) {
    $temp_qty = $_SESSION["cart"][$id]["qty"] + 1;
    if ($temp_qty > $book["stock"])
    {
        $_SESSION["flash"] = ["type" => "error", "msg" => "Quá số lượng sản phẩm"];
        header("Location: /index.php");
        exit();
    }
}
if ((int) $book["stock"] <= 0) {
    $_SESSION["flash"] = ["type" => "error", "msg" => "Sản phẩm đã hết hàng"];
    header("Location: /index.php");
    exit();
}
App\Cart::add($book, $qty);
$_SESSION["flash"] = [
    "type" => "success",
    "msg" => "Đã thêm vào giỏ hàng thành công",
];
header("Location: /cart.php");

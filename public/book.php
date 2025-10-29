<?php
require_once __DIR__ . "/../vendor/autoload.php";
use App\Database;
use App\BookRepository;
use App\Cart;
$repo = new BookRepository(Database::getConnection());
$id = (int) ($_GET["id"] ?? 0);
$book = $repo->find($id);
if (!$book) {
    http_response_code(404);
    die("Book not found");
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!App\Security::checkCsrf($_POST["csrf"] ?? "")) {
        http_response_code(400);
        die("Bad CSRF");
    }
    if ((int) $book["stock"] <= 0) {
        $_SESSION["flash"] = [
            "type" => "error",
            "msg" => "Sản phẩm đã hết hàng",
        ];
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
    $_SESSION["cart"] = $_SESSION["cart"] ?? [];
    $id = $book["id"];
    if (isset($_SESSION["cart"][$id])) {
        $temp_qty = $_SESSION["cart"][$id]["qty"] + 1;
        if ($temp_qty > $book["stock"])
        {
            $_SESSION["flash"] = ["type" => "error", "msg" => "Quá số lượng sản phẩm"];
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
    }
    Cart::add($book, 1);
    $_SESSION["flash"] = [
        "type" => "success",
        "msg" => "Đã thêm vào giỏ hàng thành công",
    ];
    header("Location: /cart.php");
    exit();
}
ob_start();
?>
<h3><?= htmlspecialchars($book["title"]) ?></h3>
<p>SKU: <strong><?= htmlspecialchars($book["sku"] ?? "") ?></strong></p>
<p><em>Tác giả: <?= htmlspecialchars($book["author"]) ?></em></p>
<p>Kho: <?= (int) $book["stock"] ?></p>
<p><?= nl2br(htmlspecialchars($book["description"])) ?></p>
<p><strong>Giá: <?= number_format($book["price"], 0) ?> đ</strong></p>
<form method="post"><input type="hidden" name="csrf" value="<?= App\Security::csrfToken() ?>"><button type="submit">Thêm vào giỏ</button></form>
<p><a href="/index.php">← Quay lại danh sách</a></p>
<?php
$content = ob_get_clean();
include __DIR__ . "/_layout.php";


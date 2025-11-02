<?php
namespace App;
use InvalidArgumentException;

class Cart
{
    public static function add(array $book, int $qty = 1): void
    {
        Security::ensureSession();
        $_SESSION["cart"] = $_SESSION["cart"] ?? [];
        $id = $book["id"];
        if (!isset($_SESSION["cart"][$id])) {
            $_SESSION["cart"][$id] = ["book" => $book, "qty" => 0];
        }
        $_SESSION["cart"][$id]["qty"] += max(1, $qty);
    }
    public static function items(): array
    {
        Security::ensureSession();
        return $_SESSION["cart"] ?? [];
    }
    public static function total(): float
    {
        $sum = 0;
        foreach (self::items() as $row) {
            $sum += $row["book"]["price"] * $row["qty"];
        }
        return $sum;
    }
    public static function count(): int
    {
        $c = 0;
        foreach (self::items() as $row) {
            $c += $row["qty"];
        }
        return $c;
    }
    public static function clear(): void
    {
        Security::ensureSession();
        $_SESSION["cart"] = [];
    }
    public static function calTax(): float
    {
        $sum = 0;
        foreach (self::items() as $row) {
            $sum += $row["book"]["price"] * $row["qty"];
        }
        $tax = $sum * 0.1;
        return $tax;
    }
    public static function calTaxFromValue(float $total): float
    {
        if ($total < 0) {
            throw new InvalidArgumentException(
                "Giá trị truyền vào phải lớn hơn hoặc bằng 0."
            );
        }
        $tax = $total * 0.08;
        return $tax;
    }
}

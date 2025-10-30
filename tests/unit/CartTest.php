<?php
use PHPUnit\Framework\TestCase;
use App\Cart;

final class CartTest extends TestCase {
  protected function setUp(): void { if(session_status()===PHP_SESSION_NONE) session_start(); $_SESSION['cart']=[]; }
  public function testAddAndTotal(): void {
    Cart::add(['id'=>1,'title'=>'A','price'=>100000],2);
    Cart::add(['id'=>2,'title'=>'B','price'=>50000],1);
    $this->assertSame(3, Cart::count());
    $this->assertSame(250000.0, Cart::total());
    $this->assertSame(25000.0, Cart::calTax());
  }
  public function testClear(): void {
    Cart::add(['id'=>1,'title'=>'A','price'=>100000],1);
    Cart::clear();
    $this->assertSame(0, Cart::count());
    $this->assertSame(0.0, Cart::total());
  }

  public function testCalTaxValid(): void {
    $this->assertSame(100.0, Cart::calTaxFromValue(1000));
  }

  public function testCalTaxZero(): void {
      $this->assertSame(0.0, Cart::calTaxFromValue(0));
  }

  public function testCalTaxNegativeThrows(): void {
      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage('Giá trị truyền vào phải lớn hơn hoặc bằng 0.');
      Cart::calTaxFromValue(-500);
  }

  public function testCalTaxNonNumericThrowsTypeError(): void {
      $this->expectException(\TypeError::class); // do tham số khai báo float

      Cart::calTaxFromValue('abc');
  }
}

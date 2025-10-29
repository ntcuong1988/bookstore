<?php
use PHPUnit\Framework\TestCase;
use App\Paginator;
final class PaginatorTest extends TestCase {
  public function testMeta(): void {
    $m = Paginator::meta(23, 2, 10);
    $this->assertSame(3, $m['pages']);
    $this->assertSame(2, $m['page']);
    $this->assertSame(10, $m['perPage']);
    $this->assertSame(10, $m['offset']);
  }
  public function testClampPage(): void {
    $m = Paginator::meta(5, 10, 10);
    $this->assertSame(1, $m['pages']);
    $this->assertSame(1, $m['page']);
  }
}

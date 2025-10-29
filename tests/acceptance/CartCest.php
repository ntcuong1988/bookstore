<?php

class CartCest
{
    public function _before(AcceptanceTester $I): void
    {
        // Dọn session để mỗi test độc lập
        $I->resetCookie('PHPSESSID');

        // Nếu bật APP_ENV=test, endpoint này sẽ xóa giỏ (không bắt buộc)
        $I->amOnPage('/test/cart_clear.php'); // OK (200) hoặc FORBIDDEN (403) cũng được
    }

    /** @ui @happy
     * Scenario: Thêm sản phẩm mới vào giỏ từ trang Chi tiết */
    public function addFromDetailShowsInCart(AcceptanceTester $I): void
    {
        // Given người dùng mở /book.php?id=1
        $I->amOnPage('/book.php?id=1');

        // When người dùng nhấn "Thêm vào giỏ" (submit form kèm CSRF)
        $token = $I->grabValueFrom('input[name=csrf]');
        $I->submitForm('form', ['csrf' => $token]);

        // Then giỏ hàng hiển thị dòng #item_1 với quantity >= 1
        $I->seeInCurrentUrl('/cart.php');
        $I->seeElement('#item_1');
        $qty = (int) trim($I->grabTextFrom('#item_1 .item-qty'));
        $I->assertGreaterThanOrEqual(1, $qty);

        // And bộ đếm giỏ hàng trên header >= 1
        $badge = (int) trim($I->grabTextFrom('a[href="/cart.php"] .badge'));
        $I->assertGreaterThanOrEqual(1, $badge);

        // And tổng tiền > 0
        $I->see('Tổng:');
        $totalText = $I->grabTextFrom('p strong');              // "Tổng: 320,000 đ"
        $totalNum  = (int) preg_replace('/\D+/', '', $totalText);
        $I->assertGreaterThan(0, $totalNum);
    }

    /** @ui @logic
     * Scenario: Thêm lại cùng sản phẩm thì tăng số lượng */
    public function addSameItemTwiceIncreasesQuantity(AcceptanceTester $I): void
    {
        // Given người dùng mở /book.php?id=1
        $I->amOnPage('/book.php?id=1');

        // When nhấn "Thêm vào giỏ" hai lần
        $token = $I->grabValueFrom('input[name=csrf]');
        $I->submitForm('form', ['csrf' => $token]);             // lần 1
        $I->amOnPage('/book.php?id=1');
        $token = $I->grabValueFrom('input[name=csrf]');
        $I->submitForm('form', ['csrf' => $token]);             // lần 2

        // Then trong /cart.php, #item_1 .item-qty >= 2
        $I->amOnPage('/cart.php');
        $I->seeElement('#item_1');
        $qty = (int) trim($I->grabTextFrom('#item_1 .item-qty'));
        $I->assertGreaterThanOrEqual(2, $qty);

        // And không có 2 dòng #item_1 trùng lặp
        $rows = $I->grabMultiple('#item_1');                    // mảng các match
        $I->assertSame(1, count($rows), 'Chỉ được có 1 dòng cho item_1');
    }

    /** @persist
     * Scenario: Dữ liệu giỏ còn nguyên sau refresh */
    public function cartDataPersistsAfterRefresh(AcceptanceTester $I): void
    {
        // Given /book.php?id=1 và thêm vào giỏ
        $I->amOnPage('/book.php?id=1');
        $token = $I->grabValueFrom('input[name=csrf]');
        $I->submitForm('form', ['csrf' => $token]);

        // When mở /cart.php và refresh
        $I->amOnPage('/cart.php');
        $I->seeElement('#item_1 .item-qty');
        $qtyBefore = (int) trim($I->grabTextFrom('#item_1 .item-qty'));
        $I->amOnPage('/cart.php');

        // Then #item_1 vẫn tồn tại và quantity không đổi
        $I->seeElement('#item_1 .item-qty');
        $qtyAfter = (int) trim($I->grabTextFrom('#item_1 .item-qty'));
        $I->assertSame($qtyBefore, $qtyAfter);
    }

    /** @price @calc
     * Scenario: Tổng tiền đúng khi có nhiều sản phẩm */
    public function totalIsCorrectWithTwoItems(AcceptanceTester $I): void
    {
        // Given thêm /book.php?id=1
        $I->amOnPage('/book.php?id=1');
        $token = $I->grabValueFrom('input[name=csrf]');
        $I->submitForm('form', ['csrf' => $token]);

        // And thêm /book.php?id=3
        $I->amOnPage('/book.php?id=3');
        $token = $I->grabValueFrom('input[name=csrf]');
        $I->submitForm('form', ['csrf' => $token]);

        // Then trong /cart.php, bộ đếm giỏ hàng = 2 (mỗi sản phẩm +1)
        $I->amOnPage('/cart.php');
        $badge = (int) trim($I->grabTextFrom('.total-number'));
        $I->assertSame(2, $badge);

        
        // Parse helper: "420,000 đ" -> 420000
        $toInt = function(string $s): int {
            return (int) preg_replace('/[^\d]/', '', $s);
        };
    
        // Lấy tất cả ô thành tiền từng dòng
        $lineTotals = $I->grabMultiple('tbody tr .item-total'); // mảng chuỗi
        $calcTotal = 0;
        foreach ($lineTotals as $t) {
            $calcTotal += $toInt($t);
        }

        // And Tổng: bằng tổng các item
        $I->see('Tổng:');
        $totalText = $I->grabTextFrom('p strong');
        $totalNum  = $toInt($totalText);
        $I->assertSame($calcTotal, $totalNum, 'Tổng hiển thị phải bằng tổng từng dòng');
    }

    public function addToCartTemporaryFailureDoesNotChangeCart(AcceptanceTester $I): void
    {
        // Given: giỏ hiện tại đang rỗng
        $I->amOnPage('/cart.php');
        $I->see('Giỏ hàng');
        $I->dontSeeElement('#item_1');

        // Lấy CSRF để gọi API
        $I->amOnPage('/book.php?id=1');
        $token = $I->grabValueFrom('input[name=csrf]');

        // When: gọi API thêm giỏ TRONG KHI server đang mô phỏng sự cố (503): chạy test với env SIMULATE_503=1
        $I->sendPOST('/api/cart_add.php?simulate=503', ['id' => 1, 'csrf' => $token]);

        // Then: trả về 503 + thông điệp lỗi máy chủ
        $I->seeResponseCodeIs(503);
        $I->seeInSource('Không thể thêm sản phẩm, vui lòng thử lại sau'); // {"error":"temporary"}

        // And: dữ liệu giỏ không thay đổi
        $I->amOnPage('/cart.php');
        $I->dontSeeElement('#item_1');
        $I->see('Giỏ hàng trống');
    }
}
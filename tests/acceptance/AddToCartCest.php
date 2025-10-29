<?php
// tests/acceptance/AddToCartCest.php
class AddToCartCest
{

    public function addNewBookToCart(AcceptanceTester $I): void
    {
        // Scenario 1
        $I->amOnPage('/index.php');
        $I->see('Danh sách sách');
        // vào chi tiết quyển đầu tiên
        $I->click('Xem chi tiết');
        $I->seeElement('form');               // có form Thêm vào giỏ
        $I->click('Thêm vào giỏ');
        $I->amOnPage('/cart.php');
        $I->see('Giỏ hàng');
        // xác nhận có 1 dòng sản phẩm và tổng tiền > 0
        $I->seeElement('table');
        $I->seeNumberOfElements('tbody tr', [1,100]); // >=1
        $I->see('Tổng:');
    }

    public function increaseQuantityWhenAlreadyInCart(AcceptanceTester $I): void
    {
        // Scenario 2: bấm 2 lần để tăng SL
        $I->amOnPage('/book.php?id=1');
        $I->click('Thêm vào giỏ'); // lần 1
        $I->amOnPage('/book.php?id=1');
        $I->click('Thêm vào giỏ'); // lần 2
        $I->amOnPage('/cart.php');
        $I->see('Giỏ hàng');
        // Kiểm tra có một dòng cho sách đó và SL hiển thị >= 2
        $I->seeElement('#item_1');
        $qtyText = $I->grabTextFrom('#item_1 .item-qty');
        $qty = (int) trim($qtyText);

        // ✅ So sánh SL phải >= 2
        $I->assertGreaterThanOrEqual(2, $qty, "Số lượng của book id=1 phải >= 2 sau khi thêm 2 lần");
        
    }

    public function viewCartAfterAdding(AcceptanceTester $I): void
    {
        $I->amOnPage('/book.php?id=1');
        $token = $I->grabValueFrom('input[name=csrf]');
        $I->submitForm('form', ['csrf' => $token]);

        // Scenario 3
        $I->amOnPage('/cart.php');
        $I->see('Giỏ hàng');
        $I->seeElement('table');
        $I->see('Tổng:');
    }
}
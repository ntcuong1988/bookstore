<?php

use Step\Acceptance\Admin;

class AdminCrudCest
{
    public function CreateBook(Admin $I): void
    {
        $I->loginAsAdmin();
        $I->seeElement('.user-name');                 // <span class="user-name">...</span>
        $I->see('Chào: admin', '.user-name');        // kiểm tra đúng greeting trong header

        $I->amOnPage('/admin/create.php');
        $I->see('Thêm sách');
        $I->fillField('title', 'Acceptance Testing in PHP');
        $sku = sprintf('BK%s', date('His'));
        $I->fillField('sku', $sku);
        $I->fillField('author', 'QA Bot');
        $I->fillField('price', '123000');
        $I->fillField('description', 'A demo book.');
        $I->click('Lưu');
        $I->see('Quản trị sách');
        $I->see('Acceptance Testing in PHP');

        $I->amOnPage('/index.php?q=Acceptance');
        $I->see('Acceptance Testing in PHP');
    }

    public function apiReturnsJson(Admin $I): void
    {
        $I->amOnPage('/api/books.php');
        $I->see('"data"');
        $I->see('"meta"');
    }
}

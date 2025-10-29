<?php

class AuthCest
{
    public function login(AcceptanceTester $I): void
    {
        $I->amOnPage('/login.php');
        $I->seeElement('button', ['name' => 'login']);
        $token = $I->grabValueFrom('input[name=csrf]');
        $I->submitForm('form', [
            'username' => 'admin',
            'password' => 'admin123',
            'csrf'     => $token,
        ]);
        $I->seeResponseCodeIsSuccessful();
        $I->seeCurrentUrlEquals('/index.php');

        $I->seeElement('.user-name');                 // <span class="user-name">...</span>
        $I->see('Chào: admin', '.user-name');        // kiểm tra đúng greeting trong header
    }

    public function loginFail(AcceptanceTester $I): void
    {
        $I->amOnPage('/login.php');
        $I->seeElement('button', ['name' => 'login']);
        $token = $I->grabValueFrom('input[name=csrf]');
        $I->submitForm('form', [
            'username' => 'nameuser',
            'password' => '112233',
            'csrf'     => $token,
        ]);

        $I->see('Sai tài khoản hoặc mật khẩu');        // kiểm tra đúng greeting trong header
    }

}

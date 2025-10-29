<?php
namespace Step\Acceptance;

class Admin extends \AcceptanceTester
{
    public function loginAsAdmin()
    {
        $I = $this;
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


    }
}
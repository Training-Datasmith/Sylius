<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace Sylius\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\When;
use Sylius\Behat\Element\Shop\Account\Register_Element_Interface;
use Sylius\Behat\Page\Shop\Account\Login_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Register_Page_Interface;
final readonly class Authorization_Context implements Context
{
    public function __construct(private Login_Page_Interface $login_page, private Register_Page_Interface $register_page, private Register_Element_Interface $register_element)
    {
    }
    #[When('I sign in with email :email and password :password')]
    #[When('I sign in again with email :email and password :password in the previous session')]
    public function i_sign_in_with_email_and_password(string $email, string $password): void
    {
        $this->login_page->open();
        $this->login_page->specify_username($email);
        $this->login_page->specify_password($password);
        $this->login_page->log_in();
    }
    #[When('I register with email :email and password :password')]
    public function i_register_with_email_and_password(string $email, string $password): void
    {
        $this->register_page->open();
        $this->register_element->specify_email($email);
        $this->register_element->specify_password($password);
        $this->register_element->verify_password($password);
        $this->register_element->specify_first_name('Carrot');
        $this->register_element->specify_last_name('Ironfoundersson');
        $this->register_element->register();
    }
}
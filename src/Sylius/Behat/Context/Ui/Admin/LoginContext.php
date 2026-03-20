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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Page\Admin\Account\Login_Page_Interface;
use Sylius\Behat\Page\Admin\Dashboard_Page_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Webmozart\Assert\Assert;
final readonly class Login_Context implements Context
{
    public function __construct(private Dashboard_Page_Interface $dashboard_page, private Login_Page_Interface $login_page)
    {
    }
    #[When('I want to log in')]
    public function i_want_to_log_in(): void
    {
        $this->login_page->open();
    }
    #[When('I specify the username as :username')]
    public function i_specify_the_username(?string $username = null): void
    {
        $this->login_page->specify_username($username);
    }
    #[When('I specify the password as :password')]
    #[When('I do not specify the password')]
    public function i_specify_the_password_as(?string $password = null): void
    {
        $this->login_page->specify_password($password);
    }
    #[When('/^(this administrator) logs in using "([^"]+)" password$/')]
    public function they_log_in(Admin_User_Interface $admin_user, string $password): void
    {
        $this->log_in_again($admin_user->get_username(), $password);
    }
    #[When('I log in')]
    public function i_log_in(): void
    {
        $this->login_page->log_in();
    }
    #[Then('I should be logged in')]
    public function i_should_be_logged_in(): void
    {
        $this->dashboard_page->verify();
    }
    #[Then('I should not be logged in')]
    public function i_should_not_be_logged_in(): void
    {
        Assert::false($this->dashboard_page->is_open());
    }
    #[Given('I should be on login page')]
    public function i_should_be_on_login_page(): void
    {
        Assert::true($this->login_page->is_open());
    }
    #[Then('I should be notified about bad credentials')]
    public function i_should_be_notified_about_bad_credentials(): void
    {
        Assert::true($this->login_page->has_validation_error_with('Invalid credentials.'));
    }
    #[Then('I should be able to log in as :username authenticated by :password password')]
    public function i_should_be_able_to_log_in_as_authenticated_by_password(string $username, string $password): void
    {
        $this->log_in_again($username, $password);
        $this->i_should_be_logged_in();
    }
    #[Then('I should not be able to log in as :username authenticated by :password password')]
    public function i_should_not_be_able_to_log_in_as_authenticated_by_password(string $username, string $password): void
    {
        $this->log_in_again($username, $password);
        Assert::true($this->login_page->has_validation_error_with('Invalid credentials.'));
        Assert::false($this->dashboard_page->is_open());
    }
    #[Then('I should be on the login page')]
    public function i_should_be_on_the_login_page(): void
    {
        Assert::true($this->login_page->is_open());
    }
    private function log_in_again(string $username, string $password): void
    {
        $this->dashboard_page->try_to_open();
        if ($this->dashboard_page->is_open()) {
            $this->dashboard_page->log_out();
        }
        $this->login_page->open();
        $this->login_page->specify_username($username);
        $this->login_page->specify_password($password);
        $this->login_page->log_in();
    }
}
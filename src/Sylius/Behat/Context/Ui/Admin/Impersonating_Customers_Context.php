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
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Administrator\Impersonate_User_Page_Interface;
use Sylius\Behat\Page\Admin\Customer\Show_Page_Interface;
use Sylius\Behat\Page\Admin\Dashboard_Page_Interface;
use Sylius\Behat\Page\Shop\Home_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Component\Customer\Model\Customer_Interface;
use Webmozart\Assert\Assert;
final readonly class Impersonating_Customers_Context implements Context
{
    public function __construct(private Show_Page_Interface $customer_show_page, private Dashboard_Page_Interface $dashboard_page, private Home_Page_Interface $home_page, private Impersonate_User_Page_Interface $impersonate_user_page, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[Given('I am impersonating the customer :customer')]
    public function i_am_impersonating_customer(Customer_Interface $customer): void
    {
        $this->customer_show_page->open(['id' => $customer->get_id()]);
        $this->customer_show_page->impersonate();
        $this->home_page->open();
    }
    #[When('I visit the store')]
    public function i_visit_the_store(): void
    {
        $this->home_page->open();
    }
    #[When('I log out from the store')]
    public function i_log_out(): void
    {
        $this->home_page->log_out();
    }
    #[When('I log out from my admin account')]
    public function i_log_out_from_my_admin_account(): void
    {
        $this->dashboard_page->open();
        $this->dashboard_page->log_out();
    }
    #[When('I impersonate them')]
    public function i_try_to_impersonate_them(): void
    {
        $this->customer_show_page->impersonate();
    }
    #[When('I impersonate the customer :customer')]
    public function i_impersonate_customer(Customer_Interface $customer): void
    {
        $this->impersonate_user_page->try_to_open(['username' => $customer->get_email()]);
    }
    #[Then('I should be unable to impersonate them')]
    public function i_should_be_unable_to_impersonate_them(): void
    {
        Assert::false($this->customer_show_page->has_impersonate_button());
    }
    #[Then('I should still be able to access the administration dashboard')]
    public function i_should_be_able_to_access_administration_dashboard(): void
    {
        $this->dashboard_page->open();
    }
    #[Then('I should be logged in as :fullName')]
    public function i_should_be_logged_in_as(string $full_name): void
    {
        [$first_name, $last_name] = explode(' ', $full_name);
        Assert::true($this->home_page->has_logout_button());
        Assert::contains($this->home_page->get_full_name(), $first_name);
    }
    #[Then('I should not be logged in as :fullName')]
    public function i_should_not_be_logged_in_as($full_name): void
    {
        $this->home_page->open();
        Assert::false($this->home_page->has_logout_button());
        Assert::false(strpos($this->home_page->get_full_name(), (string) $full_name));
    }
    #[Then('I should see that impersonating :email was successful')]
    public function i_should_see_that_impersonating_was_successful(string $email): void
    {
        $this->notification_checker->check_notification($email, Notification_Type::success());
    }
}
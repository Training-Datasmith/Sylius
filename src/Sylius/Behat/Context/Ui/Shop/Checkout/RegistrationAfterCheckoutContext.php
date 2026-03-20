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
namespace Sylius\Behat\Context\Ui\Shop\Checkout;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Shop\Account\Register_Element_Interface;
use Sylius\Behat\Page\Shop\Account\Dashboard_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Login_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Register_Thank_You_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Verification_Page_Interface;
use Sylius\Behat\Page\Shop\Home_Page_Interface;
use Sylius\Behat\Page\Shop\Order\Thank_You_Page_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Repository\Customer_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Registration_After_Checkout_Context implements Context
{
    public function __construct(private Login_Page_Interface $login_page, private Thank_You_Page_Interface $thank_you_page, private Home_Page_Interface $home_page, private Verification_Page_Interface $verification_page, private Register_Thank_You_Page_Interface $register_thank_you_page, private Dashboard_Page_Interface $dashboard_page, private Register_Element_Interface $register_element, private Customer_Repository_Interface $customer_repository)
    {
    }
    #[When('I specify a password as :password')]
    public function i_specify_the_password_as(string $password): void
    {
        $this->register_element->specify_password($password);
    }
    #[When('/^I confirm (this password)$/')]
    public function i_confirm_this_password(string $password): void
    {
        $this->register_element->verify_password($password);
    }
    #[When('I register this account')]
    public function i_register_this_account(): void
    {
        $this->register_element->register();
    }
    #[When('I verify my account using link sent to :customer')]
    public function i_verify_my_account_using_link(Customer_Interface $customer): void
    {
        $user = $customer->get_user();
        Assert::not_null($user, 'No account for given customer');
        $this->verification_page->verify_account($user->get_email_verification_token());
    }
    #[Then('the registration form should be prefilled with :email email')]
    public function the_registration_form_should_be_prefilled_with_email(string $email): void
    {
        $this->thank_you_page->create_account();
        Assert::same($this->register_element->get_email(), $email);
    }
    #[Then('I should be able to log in as :email with :password password')]
    public function i_should_be_able_to_log_in_as_with_password(string $email, string $password): void
    {
        $this->login_page->open();
        $this->login_page->specify_username($email);
        $this->login_page->specify_password($password);
        $this->login_page->log_in();
        Assert::true($this->home_page->has_logout_button());
    }
    #[Then('I should be on registration thank you page')]
    public function i_should_be_on_registration_thank_you_page(): void
    {
        $registered_customer = $this->customer_repository->find_latest(1)[0];
        Assert::true($this->register_thank_you_page->is_open(['id' => $registered_customer->get_id()]));
    }
    #[Then('I should be on my account dashboard')]
    public function i_should_be_on_my_account_dashboard(): void
    {
        Assert::true($this->dashboard_page->is_open());
    }
}
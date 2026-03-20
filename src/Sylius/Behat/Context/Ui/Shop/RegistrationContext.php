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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Shop\Account\Register_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Shop\Account\Dashboard_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Login_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Profile_Update_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Register_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Register_Thank_You_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Verification_Page_Interface;
use Sylius\Behat\Page\Shop\Home_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\Core\Repository\Customer_Repository_Interface;
use Webmozart\Assert\Assert;
class Registration_Context implements Context
{
    public function __construct(private readonly Shared_Storage_Interface $shared_storage, private readonly Dashboard_Page_Interface $dashboard_page, private readonly Home_Page_Interface $home_page, private readonly Login_Page_Interface $login_page, private readonly Register_Page_Interface $register_page, private readonly Register_Thank_You_Page_Interface $register_thank_you_page, private readonly Verification_Page_Interface $verification_page, private readonly Profile_Update_Page_Interface $profile_update_page, private readonly Register_Element_Interface $register_element, private readonly Notification_Checker_Interface $notification_checker, private readonly Customer_Repository_Interface $customer_repository)
    {
    }
    #[When('/^I want to(?:| again) register a new account$/')]
    public function i_want_to_register_a_new_account(): void
    {
        $this->register_page->open();
    }
    #[When('I specify the first name as :firstName')]
    #[When('I do not specify the first name')]
    public function i_specify_the_first_name(?string $first_name = null): void
    {
        $this->register_element->specify_first_name($first_name);
    }
    #[When('I specify the last name as :lastName')]
    #[When('I do not specify the last name')]
    public function i_specify_the_last_name(?string $last_name = null): void
    {
        $this->register_element->specify_last_name($last_name);
    }
    #[When('I specify the :firstOrLast name as too long value')]
    public function i_specify_first_or_last_name_as_too_long_value(string $first_or_last): void
    {
        match ($first_or_last) {
            'first' => $this->register_element->specify_first_name(str_repeat('a', 256)),
            'last' => $this->register_element->specify_last_name(str_repeat('a', 256)),
        };
    }
    #[When('I specify the email as :email')]
    #[When('I do not specify the email')]
    public function i_specify_the_email(?string $email = null): void
    {
        $this->register_element->specify_email($email);
    }
    #[When('I specify the password as :password')]
    public function i_specify_the_password_as(string $password): void
    {
        $this->register_element->specify_password($password);
    }
    #[When('I do not specify the password')]
    public function i_do_not_specify_the_password(): void
    {
        $this->register_element->specify_password('');
    }
    #[When('/^I confirm (this password)$/')]
    public function i_confirm_this_password(string $password): void
    {
        $this->register_element->verify_password($password);
    }
    #[When('I do not confirm the password')]
    public function i_do_not_confirm_password(): void
    {
        $this->register_element->verify_password('');
    }
    #[When('I specify the phone number as :phoneNumber')]
    public function i_specify_the_phone_number_as(string $phone_number): void
    {
        $this->register_element->specify_phone_number($phone_number);
    }
    #[When('I register this account')]
    #[When('I try to register this account')]
    public function i_register_this_account(): void
    {
        $this->register_element->register();
    }
    #[Then('my email should be :email')]
    #[Then('my email should still be :email')]
    public function my_email_should_be(string $email): void
    {
        $this->dashboard_page->open();
        Assert::true($this->dashboard_page->has_customer_email($email));
    }
    #[Then('/^I should be notified that the ([^"]+) is required$/')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        $this->assert_field_validation_message($element, sprintf('Please enter your %s.', $element));
    }
    #[Then('I should be notified that the :firstOrLast name is too long')]
    public function i_should_be_notified_that_first_or_last_name_is_too_long(string $first_or_last): void
    {
        $this->assert_field_validation_message($first_or_last . '_name', sprintf('%s name must not be longer than 255 characters.', ucfirst($first_or_last)));
    }
    #[Then('I should be notified that the email is already used')]
    public function i_should_be_notified_that_the_email_is_already_used(): void
    {
        $this->assert_field_validation_message('email', 'This email is already used.');
    }
    #[Then('I should be notified that the password do not match')]
    public function i_should_be_notified_that_the_password_do_not_match(): void
    {
        $this->assert_field_validation_message('password', 'The entered passwords don\'t match');
    }
    #[Then('I should be notified that new account has been successfully created')]
    #[Then('I should be notified that my account has been created and the verification email has been sent')]
    public function i_should_be_notified_that_new_account_has_been_successfully_created(): void
    {
        $this->notification_checker->check_notification('Thank you for registering, check your email to verify your account.', Notification_Type::success());
    }
    #[Then('I should be logged in')]
    public function i_should_be_logged_in(): void
    {
        Assert::true($this->home_page->has_logout_button());
    }
    #[Then('I should not be logged in')]
    public function i_should_not_be_logged_in(): void
    {
        Assert::false($this->home_page->has_logout_button());
    }
    #[Then('I should be able to log in as :email with :password password')]
    public function i_should_be_able_to_log_in_as_with_password(string $email, string $password): void
    {
        $this->i_log_in_as_with_password($email, $password);
        $this->i_should_be_logged_in();
    }
    #[Then('I should not be able to log in as :email with :password password')]
    public function i_should_not_be_able_to_log_in_as_with_password(string $email, string $password): void
    {
        $this->i_log_in_as_with_password($email, $password);
        Assert::true($this->login_page->has_validation_error_with('Error Invalid credentials.'));
    }
    #[When('I log in as :email with :password password')]
    public function i_log_in_as_with_password(string $email, string $password): void
    {
        $this->login_page->open();
        $this->login_page->specify_username($email);
        $this->login_page->specify_password($password);
        $this->login_page->log_in();
    }
    #[When('I register with email :email and password :password')]
    #[When('I register with email :email and password :password in the :localeCode locale')]
    public function i_register_with_email_and_password(string $email, string $password, string $locale_code = 'en_US'): void
    {
        $this->register_page->open(['_locale' => $locale_code]);
        $this->register_element->specify_email($email);
        $this->register_element->specify_password($password);
        $this->register_element->verify_password($password);
        $this->register_element->specify_first_name('Carrot');
        $this->register_element->specify_last_name('Ironfoundersson');
        $this->register_element->register();
    }
    #[Then('/^my account should be verified$/')]
    public function my_account_should_be_verified(): void
    {
        Assert::true($this->dashboard_page->is_verified());
    }
    #[When('/^(I) try to verify my account using the link from this email$/')]
    public function i_use_it_to_verify(Shop_User_Interface $user): void
    {
        $this->verification_page->verify_account($user->get_email_verification_token());
    }
    #[When('I verify my account using link sent to :customer')]
    public function i_verify_my_account(Customer_Interface $customer): void
    {
        $user = $customer->get_user();
        Assert::not_null($user, 'No account for given customer');
        $this->i_use_it_to_verify($user);
    }
    #[When('I resend the verification email')]
    public function i_resend_verification_email(): void
    {
        $this->dashboard_page->open();
        $this->dashboard_page->press_resend_verification_email();
    }
    #[When('I use the verification link from the first email to verify')]
    public function i_use_verification_link_from_first_email_to_verify(): void
    {
        $token = $this->shared_storage->get('verification_token');
        $this->verification_page->verify_account($token);
    }
    #[When('I (try to )verify using :token token')]
    public function i_try_to_verify_using(string $token): void
    {
        $this->verification_page->verify_account($token);
    }
    #[Then('/^(?:my|his|her) account should not be verified$/')]
    public function my_account_should_not_be_verified(): void
    {
        $this->dashboard_page->open();
        Assert::false($this->dashboard_page->is_verified());
    }
    #[Then('I should not be able to resend the verification email')]
    public function i_should_be_unable_to_resend_verification_email(): void
    {
        $this->dashboard_page->open();
        Assert::false($this->dashboard_page->has_resend_verification_email_button());
    }
    #[Then('I should be notified that the verification was successful')]
    public function i_should_be_notified_that_the_verification_was_successful(): void
    {
        $this->notification_checker->check_notification('has been successfully verified.', Notification_Type::success());
    }
    #[Then('I should be notified that the verification token is invalid')]
    public function i_should_be_notified_that_the_verification_token_is_invalid(): void
    {
        $this->notification_checker->check_notification('The verification token is invalid.', Notification_Type::failure());
    }
    #[Then('I should be notified that the verification email has been sent')]
    public function i_should_be_notified_that_the_verification_email_has_been_sent(): void
    {
        $this->notification_checker->check_notification('An email with the verification link has been sent to your email address.', Notification_Type::success());
    }
    #[When('I subscribe to the newsletter')]
    public function i_subscribe_to_the_newsletter(): void
    {
        $this->register_element->subscribe_to_the_newsletter();
    }
    #[Then('I should be subscribed to the newsletter')]
    public function i_should_be_subscribed_to_the_newsletter(): void
    {
        $this->profile_update_page->open();
        Assert::true($this->profile_update_page->is_subscribed_to_the_newsletter());
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
    private function assert_field_validation_message(string $element, string $expected_message): void
    {
        Assert::same($this->register_element->get_validation_message(str_replace(' ', '_', $element)), $expected_message);
    }
}
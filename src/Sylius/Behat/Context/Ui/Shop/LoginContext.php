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
use Sylius\Behat\Page\Shop\Account\Login_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Register_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Request_Password_Reset_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Reset_Password_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Well_Known_Password_Change_Page_Interface;
use Sylius\Behat\Page\Shop\Home_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Repository\Customer_Repository_Interface;
use Sylius\Component\User\Model\User_Interface;
use Webmozart\Assert\Assert;
final readonly class Login_Context implements Context
{
    public function __construct(private Home_Page_Interface $home_page, private Login_Page_Interface $login_page, private Register_Page_Interface $register_page, private Request_Password_Reset_Page_Interface $request_password_reset_page, private Reset_Password_Page_Interface $reset_password_page, private Well_Known_Password_Change_Page_Interface $well_known_password_change_page, private Register_Element_Interface $register_element, private Notification_Checker_Interface $notification_checker, private Current_Page_Resolver_Interface $current_page_resolver, private Shared_Storage_Interface $shared_storage, private Customer_Repository_Interface $customer_repository)
    {
    }
    #[When('I want to log in')]
    public function i_want_to_log_in(): void
    {
        $this->login_page->open();
    }
    #[When('I log in with the email :email')]
    public function i_log_in_with_the_email(string $email): void
    {
        $this->login_page->open();
        $this->login_page->specify_username($email);
        $this->login_page->specify_password('sylius');
        $this->login_page->log_in();
        $this->shared_storage->set('user', $email);
    }
    #[When('I want to reset password')]
    public function i_want_to_reset_password(): void
    {
        $this->request_password_reset_page->open();
    }
    #[When('I want to reset password from my password manager')]
    public function i_want_to_reset_password_from_my_password_manager(): void
    {
        $this->well_known_password_change_page->try_to_open();
    }
    #[When('/^I follow link on (my) email to reset my password$/')]
    public function i_follow_link_on_my_email_to_reset_password(User_Interface $user): void
    {
        $this->reset_password_page->open(['token' => $user->get_password_reset_token()]);
    }
    #[When('I specify the username as :username')]
    public function i_specify_the_username(?string $username = null): void
    {
        $this->login_page->specify_username($username);
    }
    #[When('I specify customer email as :email')]
    #[When('I do not specify the email')]
    public function i_specify_the_email(?string $email = null): void
    {
        $this->request_password_reset_page->specify_email($email);
    }
    #[When('I specify the password as :password')]
    #[When('I do not specify the password')]
    public function i_specify_the_password_as(?string $password = null): void
    {
        $this->login_page->specify_password($password);
    }
    #[When('I specify my new password as :password')]
    #[When('I do not specify my new password')]
    public function i_specify_my_new_password(?string $password = null): void
    {
        $this->reset_password_page->specify_new_password($password);
    }
    #[When('I confirm my new password as :password')]
    #[When('I do not confirm my new password')]
    public function i_confirm_my_new_password(?string $password = null): void
    {
        $this->reset_password_page->specify_confirm_password($password);
    }
    #[When('I log in')]
    #[When('I try to log in')]
    public function i_log_in(): void
    {
        $this->login_page->log_in();
    }
    #[When('I reset it')]
    #[When('I try to reset it')]
    public function i_reset_it(): void
    {
        /** @var RequestPasswordResetPageInterface|ResetPasswordPageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->request_password_reset_page, $this->reset_password_page]);
        $current_page->reset();
    }
    #[When('I sign in with email :email and password :password')]
    public function i_sign_in_with_email_and_password(string $email, string $password): void
    {
        $this->i_want_to_log_in();
        $this->i_specify_the_username($email);
        $this->i_specify_the_password_as($password);
        $this->i_log_in();
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
    #[When('I reset password for email :email in :localeCode locale')]
    public function i_reset_password_for_email_in_locale(string $email, string $locale_code): void
    {
        $this->request_password_reset_page->open(['_locale' => $locale_code]);
        $this->i_specify_the_email($email);
        $this->i_reset_it();
    }
    #[Then('I should be logged in')]
    public function i_should_be_logged_in(): void
    {
        $this->home_page->verify();
        Assert::true($this->home_page->has_logout_button());
    }
    #[Then('I should not be logged in')]
    public function i_should_not_be_logged_in(): void
    {
        Assert::false($this->home_page->has_logout_button());
    }
    #[Then('I should be notified about bad credentials')]
    public function i_should_be_notified_about_bad_credentials(): void
    {
        Assert::true($this->login_page->has_validation_error_with('Error Invalid credentials.'));
    }
    #[Then('I should be notified that email with reset instruction has been sent')]
    public function i_should_be_notified_that_email_with_reset_instruction_was_sent(): void
    {
        $this->notification_checker->check_notification('If the email you have specified exists in our system, we have sent there an instruction on how to reset your password.', Notification_Type::success());
    }
    #[Then('I should be notified that the :elementName is required')]
    public function i_should_be_notified_that_element_is_required(string $element_name): void
    {
        Assert::true($this->request_password_reset_page->check_validation_message_for($element_name, sprintf('Please enter your %s.', $element_name)));
    }
    #[Then('I should be notified that my password has been successfully reset')]
    public function i_should_be_notified_that_my_password_has_been_successfully_reset(): void
    {
        $this->notification_checker->check_notification('has been reset successfully!', Notification_Type::success());
    }
    #[Then('I should be able to log in as :email with :password password')]
    #[Then('the customer should be able to log in as :email with :password password')]
    public function i_should_be_able_to_log_in_as_with_password(string $email, string $password): void
    {
        $this->login_page->open();
        $this->login_page->specify_username($email);
        $this->login_page->specify_password($password);
        $this->login_page->log_in();
        $this->i_should_be_logged_in();
    }
    #[Then('I should be notified that the entered passwords do not match')]
    public function i_should_be_notified_that_the_entered_passwords_do_not_match(): void
    {
        Assert::true($this->reset_password_page->check_validation_message_for('password', 'The entered passwords don\'t match'));
    }
    #[Then('I should be notified that the password should be at least :length characters long')]
    public function i_should_be_notified_that_the_password_should_be_at_least_characters_long(int $length): void
    {
        Assert::true($this->reset_password_page->check_validation_message_for('password', sprintf('Password must be at least %s characters long.', $length)));
    }
    #[Then('I should be redirected to the forgotten password page')]
    public function i_should_be_redirected_to_the_forgotten_password_page(): void
    {
        Assert::true($this->request_password_reset_page->is_open(), 'User should be on the forgotten password page but they are not.');
    }
    #[Then('I should not be able to change my password again with the same token')]
    public function i_should_not_be_able_to_change_my_password_again_with_the_same_token(): void
    {
        $this->reset_password_page->try_to_open(['token' => 'itotallyforgotmypassword']);
        $this->i_should_not_be_able_to_change_my_password_with_this_token();
    }
    #[Then('I should not be able to change my password with this token')]
    #[Then('I should not be able to change my password')]
    public function i_should_not_be_able_to_change_my_password_with_this_token(): void
    {
        Assert::false($this->reset_password_page->is_open(), 'User should not be on the forgotten password page');
    }
    #[Then('I should see who I am')]
    public function i_should_see_who_i_am(): void
    {
        /** @var CustomerInterface $customer */
        $customer = $this->customer_repository->find_one_by(['email' => $this->shared_storage->get('user')]);
        Assert::contains($this->home_page->get_full_name(), $customer->get_first_name(), 'User should see their name on the page after logging in, but they do not.');
    }
}
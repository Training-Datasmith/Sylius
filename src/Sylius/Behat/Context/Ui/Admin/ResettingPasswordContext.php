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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Admin\Account\Reset_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Account\Request_Password_Reset_Page;
use Sylius\Behat\Page\Admin\Account\Reset_Password_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Webmozart\Assert\Assert;
final readonly class Resetting_Password_Context implements Context
{
    public function __construct(private Request_Password_Reset_Page $request_password_reset_page, private Reset_Password_Page_Interface $reset_password_page, private Reset_Element_Interface $reset_element, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[When('I want to reset password')]
    public function i_want_to_reset_password(): void
    {
        $this->request_password_reset_page->open();
    }
    #[When('I specify email as :email')]
    #[When('I do not specify an email')]
    public function i_specify_email_as(string $email = ''): void
    {
        $this->request_password_reset_page->specify_email($email);
    }
    #[When('/^I(?:| try to) reset it$/')]
    public function i_reset_it(): void
    {
        $this->reset_element->reset();
    }
    #[When('/^(I)(?:| try to) follow the instructions to reset my password$/')]
    public function i_follow_the_instructions_to_reset_my_password(Admin_User_Interface $admin): void
    {
        $this->reset_password_page->try_to_open(['token' => $admin->get_password_reset_token()]);
    }
    #[When('I specify my new password as :password')]
    #[When('I do not specify my new password')]
    public function i_specify_my_new_password(string $password = ''): void
    {
        $this->reset_password_page->specify_new_password($password);
    }
    #[When('I confirm my new password as :password')]
    #[When('I do not confirm my new password')]
    public function i_confirm_my_new_password(string $password = ''): void
    {
        $this->reset_password_page->specify_password_confirmation($password);
    }
    #[Then('I should be notified that email with reset instruction has been sent')]
    public function i_should_be_notified_that_email_with_reset_instruction_has_been_sent(): void
    {
        $this->notification_checker->check_notification('If the email you have specified exists in our system, we have sent there an instruction on how to reset your password.', Notification_Type::success());
    }
    #[Then('I should be notified that the email is required')]
    public function i_should_be_notified_that_the_email_is_required(): void
    {
        Assert::same($this->request_password_reset_page->get_email_validation_message(), 'Please enter an email.');
    }
    #[Then('I should be notified that the email is not valid')]
    public function i_should_be_notified_that_the_email_is_not_valid(): void
    {
        Assert::same($this->request_password_reset_page->get_email_validation_message(), 'This email is not valid.');
    }
    #[Then('I should be notified that my password has been successfully changed')]
    public function i_should_be_notified_that_my_password_has_been_successfully_changed(): void
    {
        $this->notification_checker->check_notification('Your password has been changed successfully!', Notification_Type::success());
    }
    #[Then('I should not be able to change my password again with the same token')]
    public function i_should_not_be_able_to_change_my_password_again_with_the_same_token(): void
    {
        $this->reset_password_page->try_to_open(['token' => 'itotallyforgotmypassword']);
        Assert::false($this->reset_password_page->is_open(), 'User should not be on the forgotten password page');
    }
    #[Then('I should be notified that the password reset token has expired')]
    public function i_should_be_notified_that_the_password_reset_token_has_expired(): void
    {
        $this->notification_checker->check_notification('The password reset token has expired', Notification_Type::failure());
    }
    #[Then('I should be notified that the new password is required')]
    public function i_should_be_notified_that_the_new_password_is_required(): void
    {
        Assert::contains($this->reset_password_page->get_validation_message_for_new_password(), 'Please enter the password.');
    }
    #[Then('I should be notified that the entered passwords do not match')]
    public function i_should_be_notified_that_the_entered_passwords_do_not_match(): void
    {
        Assert::contains($this->reset_password_page->get_validation_message_for_new_password(), 'The entered passwords do not match.');
    }
    #[Then('I should be notified that the password should be at least :length characters long')]
    public function i_should_be_notified_that_the_password_should_be_at_least_characters_long(int $length): void
    {
        Assert::true($this->reset_password_page->check_validation_message_for('new_password', sprintf('Password must be at least %s characters long.', $length)));
    }
}
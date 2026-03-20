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
namespace Sylius\Behat\Context\Api\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Client\Request_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Symfony\Component\Http_Foundation\Request as HTTPRequest;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final class Resetting_Password_Context implements Context
{
    use Secure_Password_Trait;
    private ?Request_Interface $request = null;
    public function __construct(private Api_Client_Interface $client, private Request_Factory_Interface $request_factory, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private string $api_url_prefix)
    {
    }
    #[When('I want to reset password')]
    public function i_want_to_reset_password(): void
    {
        $this->request = $this->request_factory->create('admin', 'administrators/reset-password', 'Bearer');
    }
    #[When('I specify email as :email')]
    #[When('I do not specify an email')]
    public function i_specify_email_as(string $email = ''): void
    {
        $this->request->update_content(['email' => $email]);
    }
    #[When('I reset it')]
    #[When('I try to reset it')]
    public function i_reset_it(): void
    {
        $this->client->execute_custom_request($this->request);
    }
    #[When('/^(I)(?:| try to) follow the instructions to reset my password$/')]
    public function i_follow_the_instructions_to_reset_my_password(Admin_User_Interface $admin): void
    {
        $this->request = $this->request_factory->custom(sprintf('%s/admin/administrators/reset-password/%s', $this->api_url_prefix, $admin->get_password_reset_token()), Http_Request::METHOD_PATCH);
    }
    #[When('I specify my new password as :password')]
    #[When('I do not specify my new password')]
    public function i_specify_my_new_password(string $password = ''): void
    {
        $this->request->update_content(['newPassword' => $this->replace_with_secure_password($password)]);
    }
    #[When('I confirm my new password as :password')]
    #[When('I do not confirm my new password')]
    public function i_confirm_my_new_password(string $password = ''): void
    {
        $this->request->update_content(['confirmNewPassword' => $this->confirm_secure_password($password)]);
    }
    #[Then('I should be notified that email with reset instruction has been sent')]
    public function i_should_be_notified_that_email_reset_instruction_has_been_sent(): void
    {
        Assert::same($this->client->get_last_response()->get_status_code(), Response::HTTP_ACCEPTED);
    }
    #[Then('I should be notified that my password has been successfully changed')]
    public function i_should_be_notified_that_my_password_has_been_successfully_changed(): void
    {
        Assert::same($this->client->get_last_response()->get_status_code(), Response::HTTP_ACCEPTED);
    }
    #[Then('I should not be able to change my password again with the same token')]
    public function i_should_not_be_able_to_change_my_password_again_with_the_same_token(): void
    {
        $this->client->execute_custom_request($this->request);
        $last_response = $this->client->get_last_response();
        Assert::same($last_response->get_status_code(), Response::HTTP_NOT_FOUND);
        $message = $this->response_checker->get_error($last_response);
        Assert::starts_with($message, 'No user found with reset token:');
    }
    #[Then('I should be notified that the email is required')]
    public function i_should_be_notified_that_the_email_is_required(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Please enter an email.');
    }
    #[Then('I should be notified that the email is not valid')]
    public function i_should_be_notified_that_the_email_is_not_valid(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'This email is not valid.');
    }
    #[Then('I should be notified that the password reset token has expired')]
    public function i_should_be_notified_that_the_password_reset_token_has_expired(): void
    {
        $message = $this->response_checker->get_error($this->client->get_last_response());
        Assert::same($message, 'The password reset token has expired.');
    }
    #[Then('I should be notified that the new password is required')]
    public function i_should_be_notified_that_the_new_password_is_required(): void
    {
        $this->assert_response_has_validation_message_for_new_password('Please enter the password.');
    }
    #[Then('I should be notified that the entered passwords do not match')]
    public function i_should_be_notified_that_the_entered_passwords_do_not_match(): void
    {
        $this->assert_response_has_validation_message_for_new_password('The entered passwords do not match.');
    }
    #[Then('I should be notified that the password should be at least :length characters long')]
    public function i_should_be_notified_that_the_password_should_be_at_least_characters_long(int $length): void
    {
        $this->assert_response_has_validation_message_for_new_password(sprintf('Password must be at least %s characters long.', $length));
    }
    private function assert_response_has_validation_message_for_new_password(string $message): void
    {
        $last_response = $this->client->get_last_response();
        Assert::true($this->response_checker->has_violation_with_message($last_response, $message, 'newPassword'), $last_response->get_content());
    }
}
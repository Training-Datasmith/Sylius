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
namespace Sylius\Behat\Context\Api\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Symfony\Component\Http_Foundation\Request as HttpRequest;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final class Registration_Context implements Context
{
    use Secure_Password_Trait;
    private array $content = [];
    public function __construct(private Api_Client_Interface $shop_client, private Login_Context $login_context, private Shared_Storage_Interface $shared_storage, private Response_Checker_Interface $response_checker, private Request_Factory_Interface $request_factory, private string $api_url_prefix)
    {
    }
    #[When('I want to register a new account')]
    #[When('I want to again register a new account')]
    public function i_want_to_register_new_account(): void
    {
        $this->fill_content();
    }
    #[When('I specify the first name as :firstName')]
    #[When('I do not specify the first name')]
    public function i_specify_the_first_name_as(string $first_name = ''): void
    {
        $this->content['firstName'] = $first_name;
    }
    #[When('I specify the :firstOrLast name as too long value')]
    public function i_specify_the_first_or_last_name_as_too_long_value(string $first_or_last): void
    {
        $this->content[$first_or_last . 'Name'] = str_repeat('a', 256);
    }
    #[When('I specify the last name as :lastName')]
    #[When('I do not specify the last name')]
    public function i_specify_the_last_name_as(string $last_name = ''): void
    {
        $this->content['lastName'] = $last_name;
    }
    #[When('I specify the email as :email')]
    #[When('I do not specify the email')]
    public function i_specify_the_email_as(string $email = ''): void
    {
        $this->content['email'] = $email;
    }
    #[When('I specify the password as :password')]
    #[When('I do not specify the password')]
    public function i_specify_the_password_as(string $password = ''): void
    {
        $this->content['password'] = $this->replace_with_secure_password($password);
    }
    #[When('I specify the phone number as :phoneNumber')]
    public function i_specify_the_phone_number_as(string $phone_number): void
    {
        $this->content['phoneNumber'] = $phone_number;
    }
    #[When('I subscribe to the newsletter')]
    public function i_subscribe_to_the_newsletter(): void
    {
        $this->content['subscribedToNewsletter'] = true;
    }
    #[When('I verify my account using link sent to :customer')]
    public function i_verify_my_account_using_link(Customer_Interface $customer): void
    {
        $this->shared_storage->set('customer', $customer);
        $token = $customer->get_user()->get_email_verification_token();
        $request = $this->request_factory->custom(\sprintf('%s/shop/customers/verify/%s', $this->api_url_prefix, $token), Http_Request::METHOD_PATCH);
        $this->shop_client->execute_custom_request($request);
    }
    #[When('I confirm this password')]
    public function i_confirm_this_password(): void
    {
        // Intentionally left blank
    }
    #[When('I register with email :email and password :password')]
    #[When('I register with email :email and password :password in the :localeCode locale')]
    public function i_register_with_email_and_password(string $email, string $password, string $locale_code = 'en_US'): void
    {
        $this->shared_storage->set('current_locale_code', $locale_code);
        $this->fill_content($email, $password);
        $this->i_specify_the_first_name_as('John');
        $this->i_specify_the_last_name_as('Doe');
        $this->i_register_this_account();
        $this->login_context->i_log_in_as_with_password($email, $password);
    }
    #[When('I register this account')]
    #[When('I try to register this account')]
    public function i_register_this_account(): void
    {
        $request = $this->request_factory->create('shop', Resources::CUSTOMERS, '');
        $request->set_content($this->content);
        $this->shop_client->execute_custom_request($request);
        $this->content = [];
    }
    #[When('I log in as :email with :password password')]
    public function i_log_in_as_with_password(string $email, string $password): void
    {
        $this->login_context->i_log_in_as_with_password($email, $password);
    }
    #[When('I log out')]
    public function i_log_out(): void
    {
        $this->login_context->i_log_out();
    }
    #[Then('I should be notified that new account has been successfully created')]
    public function i_should_be_notified_that_new_account_has_been_successfully_created(): void
    {
        Assert::same($this->shop_client->get_last_response()->get_status_code(), 204);
    }
    #[Then('I should be notified that the first name is required')]
    public function i_should_be_notified_that_the_first_name_is_required(): void
    {
        $this->assert_field_validation_message('firstName', 'Please enter your first name.');
    }
    #[Then('/^I should be notified that the "([^"]+)" and "([^"]+)" have to be provided$/')]
    public function i_should_be_notified_that_field_have_to_be_provided(string ...$fields): void
    {
        $fields = $this->convert_elements_to_camel_case($fields);
        $content = $this->get_response_content();
        Assert::same($content['hydra:description'], 'Request does not have the following required fields specified: ' . implode(', ', $fields) . '.');
        Assert::same($this->shop_client->get_last_response()->get_status_code(), Response::HTTP_UNPROCESSABLE_ENTITY);
    }
    #[Then('I should be notified that the last name is required')]
    public function i_should_be_notified_that_the_last_name_is_required(): void
    {
        $this->assert_field_validation_message('lastName', 'Please enter your last name.');
    }
    #[Then('I should be notified that the password is required')]
    public function i_should_be_notified_that_the_password_is_required(): void
    {
        $this->assert_field_validation_message('password', 'Please enter your password.');
    }
    #[Then('I should be notified that the :firstOrLast name is too long')]
    public function i_should_be_notified_that_the_first_or_last_name_is_too_long(string $first_or_last): void
    {
        $this->assert_field_validation_message($first_or_last . 'Name', sprintf('%s name must not be longer than 255 characters.', ucfirst($first_or_last)));
    }
    #[Then('I should be notified that the email is required')]
    public function i_should_be_notified_that_the_email_is_required(): void
    {
        $this->assert_field_validation_message('email', 'Please enter your email.');
    }
    #[Then('I should be notified that the email is already used')]
    public function i_should_be_notified_that_the_email_is_already_used(): void
    {
        $this->assert_field_validation_message('email', 'This email is already used.');
    }
    #[Then('I should not be logged in')]
    #[Then('I should be logged in')]
    public function i_should_not_be_logged_in(): void
    {
        // Intentionally left blank
    }
    #[Then('I should be subscribed to the newsletter')]
    public function i_should_be_subscribed_to_the_newsletter(): void
    {
        $customer = $this->shared_storage->get('customer');
        $response = $this->shop_client->show(Resources::CUSTOMERS, (string) $customer->get_id());
        Assert::true($this->response_checker->get_response_content($response)['subscribedToNewsletter']);
    }
    private function assert_field_validation_message(string $path, string $message): void
    {
        $decoded_response = $this->get_response_content();
        Assert::key_exists($decoded_response, 'violations');
        Assert::same($decoded_response['violations'][0], ['propertyPath' => $path, 'message' => $message, 'code' => $decoded_response['violations'][0]['code']]);
    }
    private function fill_content(?string $email = 'example@example.com', ?string $password = 'example'): void
    {
        $this->content = ['email' => $email, 'password' => $this->replace_with_secure_password($password)];
    }
    private function get_response_content(): array
    {
        return json_decode($this->shop_client->get_last_response()->get_content(), true);
    }
    private function convert_elements_to_camel_case(array $fields): array
    {
        foreach ($fields as $key => $field) {
            $fields[$key] = lcfirst(str_replace(' ', '', ucwords((string) $field)));
        }
        return $fields;
    }
}
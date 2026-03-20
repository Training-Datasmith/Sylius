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
use Sylius\Behat\Context\Setup\Shop_Security_Context;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Symfony\Component\Http_Foundation\Request as HttpRequest;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final class Customer_Context implements Context
{
    use Secure_Password_Trait;
    private ?string $verification_token = '';
    public function __construct(private Api_Client_Interface $client, private Shared_Storage_Interface $shared_storage, private Response_Checker_Interface $response_checker, private Registration_Context $registration_context, private Login_Context $login_context, private Shop_Security_Context $shop_api_security_context, private Request_Factory_Interface $request_factory, private string $api_url_prefix)
    {
    }
    #[When('I want to modify my profile')]
    public function i_want_to_modify_my_profile(): void
    {
        /** @var ShopUserInterface $shopUser */
        $shop_user = $this->shared_storage->get('user');
        $customer = $shop_user->get_customer();
        $this->client->build_update_request(Resources::CUSTOMERS, (string) $customer->get_id());
    }
    #[When('I want to change my password')]
    public function i_want_to_change_my_password(): void
    {
        /** @var ShopUserInterface $shopUser */
        $shop_user = $this->shared_storage->get('user');
        /** @var CustomerInterface $customer */
        $customer = $shop_user->get_customer();
        $this->client->build_custom_update_request(sprintf('customers/%s/password', $customer->get_id()));
    }
    #[When('I specify the first name as :firstName')]
    #[When('I remove the first name')]
    public function i_specify_the_first_name(string $first_name = ''): void
    {
        $this->client->add_request_data('firstName', $first_name);
    }
    #[When('I specify the :firstOrLast name as null value')]
    public function i_specify_the_first_or_last_name_as_null(string $first_or_last): void
    {
        $this->client->add_request_data($first_or_last . 'Name', null);
    }
    #[When('I specify the gender as a wrong value')]
    public function i_specify_the_first_name_as_wrong_value(): void
    {
        $this->client->add_request_data('gender', 'wrong_value');
    }
    #[When('I specify the phone number as huge value')]
    public function i_specify_the_phone_number_as_huge_value(): void
    {
        $this->client->add_request_data('phoneNumber', str_repeat('1', 256));
    }
    #[When('I specify the last name as :lastName')]
    #[When('I remove the last name')]
    public function i_specify_the_last_name(string $last_name = ''): void
    {
        $this->client->add_request_data('lastName', $last_name);
    }
    #[When('I specify the customer email as :email')]
    #[When('I remove the customer email')]
    public function i_specify_customer_the_email(string $email = ''): void
    {
        $this->client->add_request_data('email', $email);
    }
    #[When('I specify the current password as :password')]
    public function i_specify_the_current_password_as(string $password): void
    {
        $this->client->add_request_data('currentPassword', $password);
    }
    #[When('I specify the new password as :password')]
    public function i_specify_the_new_password_as(string $password): void
    {
        $this->client->add_request_data('newPassword', $password);
    }
    #[When('I confirm this password as :password')]
    public function i_specify_the_confirmation_password_as(string $password): void
    {
        $this->client->add_request_data('confirmNewPassword', $password);
    }
    #[When('I change password from :oldPassword to :newPassword')]
    public function i_change_password_to(string $old_password, string $new_password): void
    {
        $this->client->set_request_data(['currentPassword' => $this->retrieve_secure_password($old_password), 'newPassword' => $this->replace_with_secure_password($new_password), 'confirmNewPassword' => $this->confirm_secure_password($new_password)]);
    }
    #[When('I subscribe to the newsletter')]
    public function i_subscribe_to_the_newsletter(): void
    {
        $this->client->add_request_data('subscribedToNewsletter', true);
    }
    #[Then('I should be subscribed to the newsletter')]
    public function i_should_be_subscribed_to_the_newsletter(): void
    {
        $response = $this->client->get_last_response();
        Assert::true($this->response_checker->get_value($response, 'subscribedToNewsletter'));
    }
    #[When('/^(I) try to verify my account using the link from this email$/')]
    public function i_try_to_verify_my_account_using_the_link_from_email(Shop_User_Interface $user): void
    {
        $this->verification_token = $user->get_email_verification_token();
        $this->verify_account($this->verification_token);
    }
    #[When('I (try to )verify using :token token')]
    public function i_try_to_verify_using(string $token): void
    {
        $this->verification_token = $token;
        $this->verify_account($token);
    }
    #[When('I resend the verification email')]
    public function i_resend_verification_email(): void
    {
        /** @var ShopUserInterface $user */
        $user = $this->shared_storage->get('user');
        $this->resend_verification_email($user->get_email());
    }
    #[When('I use the verification link from the first email to verify')]
    public function i_use_verification_link_from_first_email_to_verify(): void
    {
        $token = $this->shared_storage->get('verification_token');
        $this->verify_account($token);
    }
    #[When('I register with email :email and password :password')]
    public function i_register_with_email_and_password(string $email, string $password): void
    {
        $this->register_account($email, $password);
        $this->login_context->i_log_in_as_with_password($email, $password);
    }
    #[Then('I should be notified that the verification email has been sent')]
    public function i_should_be_notified_that_the_verification_email_has_been_sent(): void
    {
        Assert::same($this->client->get_last_response()->get_status_code(), 202);
    }
    #[Then('my email should be :email')]
    #[Then('my email should still be :email')]
    public function my_email_should_be(string $email): void
    {
        /** @var ShopUserInterface $shopUser */
        $shop_user = $this->shared_storage->get('user');
        $this->shop_api_security_context->i_am_logged_in_as($email);
        $response = $this->client->show(Resources::CUSTOMERS, (string) $shop_user->get_customer()->get_id());
        Assert::true($this->response_checker->has_value($response, 'email', $email));
    }
    #[Then('my name should be :name')]
    #[Then('my name should still be :name')]
    public function my_name_should_be(string $name): void
    {
        /** @var ShopUserInterface $shopUser */
        $shop_user = $this->shared_storage->get('user');
        $response = $this->client->show(Resources::CUSTOMERS, (string) $shop_user->get_customer()->get_id());
        Assert::true($this->response_checker->has_value($response, 'fullName', $name));
    }
    #[Then('my gender should still be :gender')]
    public function my_gender_should_be(string $gender): void
    {
        /** @var ShopUserInterface $shopUser */
        $shop_user = $this->shared_storage->get('user');
        $response = $this->client->show(Resources::CUSTOMERS, (string) $shop_user->get_customer()->get_id());
        Assert::true($this->response_checker->has_value($response, 'gender', $gender));
    }
    #[Then('my phone number should still be :phoneNumber')]
    public function my_phone_number_should_be(string $phone_number): void
    {
        /** @var ShopUserInterface $shopUser */
        $shop_user = $this->shared_storage->get('user');
        $response = $this->client->show(Resources::CUSTOMERS, (string) $shop_user->get_customer()->get_id());
        Assert::true($this->response_checker->has_value($response, 'phoneNumber', $phone_number));
    }
    #[Then('I should be notified that the first name is required')]
    public function i_should_be_notified_that_first_name_is_required(): void
    {
        Assert::true($this->is_violation_with_message_in_response($this->client->get_last_response(), 'First name must be at least 2 characters long.'));
    }
    #[Then('I should be (also) notified that the :firstOrLast name needs to be provided')]
    public function i_should_be_notified_that_first_or_last_name_needs_to_be_provided(string $first_or_last): void
    {
        Assert::true($this->is_violation_with_message_in_response($this->client->get_last_response(), sprintf('Please enter your %s name.', $first_or_last)));
    }
    #[Then('I should be notified that my gender is invalid')]
    public function i_should_be_notified_that_gender_is_invalid(): void
    {
        Assert::true($this->is_violation_with_message_in_response($this->client->get_last_response(), 'The value you selected is not a valid choice.'));
    }
    #[Then('I should be notified that the phone number is too long')]
    public function i_should_be_notified_that_the_phone_number_is_too_long(): void
    {
        Assert::true($this->is_violation_with_message_in_response($this->client->get_last_response(), 'Phone number must not be longer than 255 characters.'));
    }
    #[Then('I should be notified that the last name is required')]
    public function i_should_be_notified_that_last_name_is_required(): void
    {
        Assert::true($this->is_violation_with_message_in_response($this->client->get_last_response(), 'Last name must be at least 2 characters long.'));
    }
    #[Then('I should be notified that the email is required')]
    public function i_should_be_notified_that_email_is_required(): void
    {
        Assert::true($this->is_violation_with_message_in_response($this->client->get_last_response(), 'Please enter your email.'));
    }
    #[Then('I should be notified that the email is already used')]
    public function i_should_be_notified_that_the_email_is_already_used(): void
    {
        Assert::true($this->is_violation_with_message_in_response($this->client->get_last_response(), 'This email is already used.'));
    }
    #[Then('I should be notified that the email is invalid')]
    public function i_should_be_notified_that_email_is_invalid(): void
    {
        Assert::true($this->is_violation_with_message_in_response($this->client->get_last_response(), 'This email is invalid.'));
    }
    #[Then('I should be notified that the verification token is invalid')]
    public function i_should_be_notified_that_the_verification_token_is_invalid(): void
    {
        $this->client->get_last_response();
        $this->is_violation_with_message_in_response($this->client->get_last_response(), sprintf('There is no shop user with %s email verification token.', $this->verification_token));
    }
    #[When('I (try to) browse my orders')]
    public function i_browse_my_orders(): void
    {
        $this->client->index(Resources::ORDERS);
    }
    #[When('I register with previously used :email email and :password password')]
    public function i_register_with_previously_used_email_and_password(string $email, string $password): void
    {
        $this->registration_context->i_want_to_register_new_account();
        $this->registration_context->i_specify_the_email_as($email);
        $this->registration_context->i_specify_the_password_as($password);
        $this->registration_context->i_register_this_account();
        $this->login_context->i_log_in_as_with_password($email, $password);
    }
    #[Then('I should see a single order in the list')]
    public function i_should_see_a_single_order_in_the_list(): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->index(Resources::ORDERS)), 1);
    }
    #[Then('this order should have :orderNumber number')]
    public function this_order_should_have_number(string $order_number): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'number', $order_number));
    }
    #[Then('I should be notified that the verification was successful')]
    public function i_should_be_notified_that_the_verification_was_successful(): void
    {
        $this->response_checker->is_creation_successful($this->client->get_last_response());
    }
    #[Then('I should be notified that my password has been successfully changed')]
    #[Then('I should be notified that new account has been successfully created')]
    #[Then('I should be notified that my account has been created and the verification email has been sent')]
    public function i_should_be_notified_that_it_has_been_successfully_changed(): void
    {
        $response = $this->client->get_last_response();
        Assert::same($response->get_status_code(), 204, $response->get_content());
    }
    #[Then('I should be notified that provided password is different than the current one')]
    public function i_should_be_notified_that_provided_password_is_different_than_the_current_one(): void
    {
        Assert::same($this->client->get_last_response()->get_status_code(), 422);
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Provided password is different than the current one.');
    }
    #[Then('I should be notified that the entered passwords do not match')]
    public function i_should_be_notified_that_the_entered_passwords_do_not_match(): void
    {
        Assert::same($this->client->get_last_response()->get_status_code(), 422);
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'newPassword: The entered passwords don\'t match');
    }
    #[Then('/^I should be notified that the ([^"]+) should be ([^"]+)$/')]
    public function i_should_be_notified_that_the_element_should_be(string $element_name, string $validation_message): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s must be %s.', ucfirst($element_name), $validation_message));
    }
    #[Then('my account should be verified')]
    public function my_account_should_be_verified(): void
    {
        $response = $this->get_response_with_account_data();
        Assert::true($this->response_checker->get_response_content($response)['user']['verified']);
    }
    #[Then('/^(?:my|his|her) account should not be verified$/')]
    public function my_account_should_not_be_verified(): void
    {
        $response = $this->get_response_with_account_data();
        Assert::false($this->response_checker->get_response_content($response)['user']['verified']);
    }
    #[Then('I should not be able to resend the verification email')]
    public function i_should_be_unable_to_resend_verification_email(): void
    {
        /** @var ShopUserInterface $user */
        $user = $this->shared_storage->get('user');
        $this->resend_verification_email($user->get_email());
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), \sprintf('Account with email %s is currently verified.', $user->get_email()), 'Validation message is different then expected.');
    }
    private function is_violation_with_message_in_response(Response $response, string $message): bool
    {
        $violations = $this->response_checker->get_response_content($response)['violations'];
        foreach ($violations as $violation) {
            if ($violation['message'] === $message) {
                return true;
            }
        }
        return false;
    }
    private function verify_account(string $token): void
    {
        $request = $this->request_factory->custom(\sprintf('%s/shop/customers/verify/%s', $this->api_url_prefix, $token), Http_Request::METHOD_PATCH);
        $this->client->execute_custom_request($request);
    }
    private function register_account(?string $email = 'example@example.com', ?string $password = 'example'): void
    {
        $request = $this->request_factory->create('shop', Resources::CUSTOMERS, '');
        $request->set_content(['firstName' => 'First', 'lastName' => 'Last', 'email' => $email, 'password' => $this->replace_with_secure_password($password)]);
        $this->client->execute_custom_request($request);
    }
    private function resend_verification_email(string $email): void
    {
        $request = $this->request_factory->create('shop', 'customers/verify', 'Bearer');
        $request->set_content(['email' => $email]);
        $this->client->execute_custom_request($request);
    }
    private function get_response_with_account_data(): Response
    {
        /** @var ShopUserInterface $user */
        $user = $this->shared_storage->get('user');
        $this->login_context->i_log_in_as_with_password($user->get_email(), 'sylius');
        return $this->client->show(Resources::CUSTOMERS, (string) $user->get_customer()->get_id());
    }
}
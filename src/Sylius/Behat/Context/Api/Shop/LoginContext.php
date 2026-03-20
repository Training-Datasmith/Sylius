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

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Api_Security_Client_Interface;
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Client\Request_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Symfony\Component\Browser_Kit\Abstract_Browser;
use Symfony\Component\Browser_Kit\Exception\BadMethodCallException;
use Symfony\Component\Http_Foundation\Request as HTTPRequest;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final class Login_Context implements Context
{
    use Secure_Password_Trait;
    private ?Request_Interface $request = null;
    public function __construct(private Api_Security_Client_Interface $api_security_client, private Api_Client_Interface $client, private Iri_Converter_Interface $iri_converter, private Abstract_Browser $shop_authentication_token_client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private Request_Factory_Interface $request_factory, private string $api_url_prefix)
    {
    }
    #[Given('there is the visitor')]
    public function i_am_a_visitor(): void
    {
        // Intentionally left blank;
    }
    #[When('I log in with the email :email')]
    public function i_log_in_with_the_email(string $email): void
    {
        $this->shop_authentication_token_client->request('POST', sprintf('%s/shop/customers/token', $this->api_url_prefix), [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode(['email' => $email, 'password' => $this->retrieve_secure_password('sylius')]));
        $response = $this->shop_authentication_token_client->get_response();
        $content = json_decode((string) $response->get_content(), true, 512, \JSON_THROW_ON_ERROR);
        Assert::key_exists($content, 'token', 'Token not found.');
    }
    #[When('I want to log in')]
    public function i_want_to_log_in(): void
    {
        $this->api_security_client->prepare_login_request();
    }
    #[When('I want to reset password')]
    public function i_want_to_reset_password(): void
    {
        $this->request = $this->request_factory->create('shop', 'customers/reset-password', 'Bearer');
    }
    #[When('I reset password for email :email in :locale locale')]
    public function i_reset_password_for_email_in_locale(string $email, Locale_Interface $locale): void
    {
        $this->i_want_to_reset_password();
        $this->i_specify_the_email($email);
        $this->shared_storage->set('current_locale_code', $locale->get_code());
        $this->i_reset_it();
    }
    #[When('/^I follow link on (my) email to reset my password$/')]
    public function i_follow_link_on_my_email_to_reset_password(Shop_User_Interface $user): void
    {
        $this->request = $this->request_factory->custom(sprintf('%s/shop/customers/reset-password/%s', $this->api_url_prefix, $user->get_password_reset_token()), Http_Request::METHOD_PATCH);
    }
    #[When('I reset it')]
    #[When('I try to reset it')]
    public function i_reset_it(): void
    {
        $this->client->execute_custom_request($this->request);
    }
    #[When('I specify the username as :username')]
    public function i_specify_the_username(string $username): void
    {
        $this->api_security_client->set_email($username);
    }
    #[When('I specify customer email as :email')]
    #[When('I do not specify the email')]
    public function i_specify_the_email(string $email = ''): void
    {
        $this->request->update_content(['email' => $email]);
    }
    #[When('I specify my new password as :password')]
    #[When('I do not specify my new password')]
    public function i_specify_my_new_password(?string $password = null): void
    {
        $this->request->update_content(['newPassword' => $this->replace_with_secure_password($password)]);
    }
    #[When('I confirm my new password as :password')]
    #[When('I do not confirm my new password')]
    public function i_confirm_my_new_password(?string $password = null): void
    {
        $this->request->update_content(['confirmNewPassword' => $this->confirm_secure_password($password)]);
    }
    #[When('I specify the password as :password')]
    public function i_specify_the_password_as(string $password): void
    {
        $this->api_security_client->set_password($password);
    }
    #[When('I log in')]
    #[When('I try to log in')]
    public function i_log_in(): void
    {
        $this->api_security_client->call();
    }
    #[When('I log in as :email with :password password')]
    public function i_log_in_as_with_password(string $email, string $password): void
    {
        $this->api_security_client->prepare_login_request();
        $this->api_security_client->set_email($email);
        $this->api_security_client->set_password($password);
        $this->api_security_client->call();
    }
    #[When('I log out')]
    #[When('the customer logged out')]
    public function i_log_out(): void
    {
        $this->api_security_client->log_out();
    }
    #[Then('I should be logged in')]
    public function i_should_be_logged_in(): void
    {
        Assert::true($this->api_security_client->is_logged_in(), 'Shop user should be logged in, but they are not.');
    }
    #[Then('I should not be logged in')]
    public function i_should_not_be_logged_in(): void
    {
        try {
            $is_logged_in = $this->api_security_client->is_logged_in();
        } catch (BadMethodCallException) {
            /** @var ShopUserInterface $shopUser */
            $shop_user = $this->shared_storage->get('user');
            $this->client->show(Resources::CUSTOMERS, (string) $shop_user->get_customer()->get_id());
            $is_logged_in = $this->client->get_last_response()->get_status_code() !== Response::HTTP_UNAUTHORIZED;
        }
        Assert::false($is_logged_in, 'Shop user should not be logged in, but they are.');
    }
    #[Then('I should be notified about bad credentials')]
    public function i_should_be_notified_about_bad_credentials(): void
    {
        Assert::same($this->api_security_client->get_error_message(), 'Invalid credentials.');
    }
    #[Then('I should be notified that email with reset instruction has been sent')]
    #[Then('I should be notified that my password has been successfully reset')]
    public function i_should_be_notified_that_email_with_reset_instruction_was_sent(): void
    {
        Assert::same($this->client->get_last_response()->get_status_code(), 202);
    }
    #[Then('I should be able to log in as :email with :password password')]
    #[Then('the customer should be able to log in as :email with :password password')]
    public function i_should_be_able_to_log_in_as_with_password(string $email, string $password): void
    {
        $this->i_log_in_as_with_password($email, $password);
        $this->i_should_be_logged_in();
    }
    #[Then('I should not be able to log in as :email with :password password')]
    public function i_should_not_be_able_to_log_in_as_with_password(string $email, string $password): void
    {
        $this->i_log_in_as_with_password($email, $password);
        $this->i_should_not_be_logged_in();
    }
    #[Then('I should see who I am')]
    public function i_should_see_who_i_am(): void
    {
        /** @var CustomerInterface $customer */
        $customer = $this->shared_storage->get('customer');
        Assert::same($this->response_checker->get_value($this->shop_authentication_token_client->get_response(), 'customer'), $this->iri_converter->get_iri_from_resource($customer));
    }
    #[Then('I should not be able to change my password again with the same token')]
    public function i_should_not_be_able_to_change_my_password_again_with_the_same_token(): void
    {
        $response = $this->client->execute_custom_request($this->request);
        Assert::same($response->get_status_code(), 422);
        Assert::same($this->response_checker->get_error($response), 'token: Password reset token itotallyforgotmypassword is invalid.');
    }
    #[Then('I should not be able to change my password with this token')]
    public function i_should_not_be_able_to_change_my_password_with_this_token(): void
    {
        $response = $this->client->get_last_response();
        Assert::same($response->get_status_code(), 422);
        Assert::same($this->response_checker->get_error($response), 'token: Password reset token has expired.');
    }
}
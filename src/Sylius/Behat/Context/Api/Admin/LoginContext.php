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
use Sylius\Behat\Client\Api_Security_Client_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Symfony\Component\Browser_Kit\Exception\BadMethodCallException;
use Webmozart\Assert\Assert;
final readonly class Login_Context implements Context
{
    public function __construct(private Api_Security_Client_Interface $api_security_client, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I want to log in')]
    public function i_want_to_log_in(): void
    {
        $this->api_security_client->prepare_login_request();
    }
    #[When('I specify the username as :username')]
    public function i_specify_the_username(string $username): void
    {
        $this->api_security_client->set_email($username);
    }
    #[When('I specify the password as :password')]
    public function i_specify_the_password_as(string $password): void
    {
        $this->api_security_client->set_password($password);
    }
    #[When('I log in')]
    public function i_log_in(): void
    {
        $this->api_security_client->call();
    }
    #[Then('I should be logged in')]
    public function i_should_be_logged_in(): void
    {
        Assert::true($this->api_security_client->is_logged_in(), 'Admin should be logged in, but they are not.');
    }
    #[Then('I should not be logged in')]
    public function i_should_not_be_logged_in(): void
    {
        try {
            Assert::false($this->api_security_client->is_logged_in(), 'Admin should not be logged in, but they are.');
        } catch (BadMethodCallException) {
            Assert::same($this->shared_storage->get('last_response')->get_status_code(), 401, 'Admin should not be logged in, but they are.');
        }
    }
    #[Then('I should be notified about bad credentials')]
    public function i_should_be_notified_about_bad_credentials(): void
    {
        Assert::same($this->api_security_client->get_error_message(), 'Invalid credentials.');
    }
    #[Then('I should be able to log in as :username authenticated by :password password')]
    public function i_should_be_able_to_log_in_as_authenticated_by_password(string $username, string $password): void
    {
        $this->log_in($username, $password);
        $this->i_should_be_logged_in();
    }
    #[Then('I should not be able to log in as :username authenticated by :password password')]
    public function i_should_not_be_able_to_log_in_as_authenticated_by_password(string $username, string $password): void
    {
        $this->log_in($username, $password);
        $this->i_should_not_be_logged_in();
    }
    private function log_in(string $username, string $password): void
    {
        $this->i_want_to_log_in();
        $this->i_specify_the_username($username);
        $this->i_specify_the_password_as($password);
        $this->i_log_in();
    }
}
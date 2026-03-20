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
namespace Sylius\Behat\Client;

use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Symfony\Component\Browser_Kit\Abstract_Browser;
use Symfony\Component\Http_Foundation\Response;
final class Api_Platform_Security_Client implements Api_Security_Client_Interface
{
    use Secure_Password_Trait;
    /** @var array<string, string|object> */
    private array $request = [];
    public function __construct(private readonly Abstract_Browser $client, private readonly Shared_Storage_Interface $shared_storage, private readonly string $api_url_prefix, private readonly string $section)
    {
    }
    public function prepare_login_request(): void
    {
        $this->request['url'] = sprintf('%s/%s', $this->api_url_prefix, $this->section);
        $this->request['method'] = 'POST';
    }
    public function set_email(string $email): void
    {
        $this->request['body']['email'] = $email;
    }
    public function set_password(string $password): void
    {
        $this->request['body']['password'] = $this->retrieve_secure_password($password);
    }
    public function call(): void
    {
        $this->client->request($this->request['method'], $this->request['url'], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode($this->request['body']));
        $response = $this->client->get_response();
        $content = json_decode((string) $response->get_content(), true);
        if (isset($content['token'])) {
            $this->shared_storage->set('token', $content['token']);
        }
    }
    public function is_logged_in(): bool
    {
        $response = $this->client->get_response();
        return isset(json_decode((string) $response->get_content(), true)['token']) && $response->get_status_code() !== Response::HTTP_UNAUTHORIZED;
    }
    public function get_error_message(): string
    {
        return json_decode((string) $this->client->get_response()->get_content(), true)['message'];
    }
    public function log_out(): void
    {
        $this->shared_storage->set('token', null);
        if ($this->shared_storage->has('cart_token')) {
            $this->shared_storage->set('previous_cart_token', $this->shared_storage->get('cart_token'));
        }
    }
}
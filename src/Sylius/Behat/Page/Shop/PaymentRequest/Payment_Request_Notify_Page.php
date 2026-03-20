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
namespace Sylius\Behat\Page\Shop\Payment_Request;

use Behat\Mink\Driver\Browser_Kit_Driver;
use Sylius\Behat\Page\Sylius_Page;
use Symfony\Component\Browser_Kit\Abstract_Browser;
class Payment_Request_Notify_Page extends Sylius_Page implements Payment_Request_Notify_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_payment_request_notify';
    }
    public function open_with_client(string $method, array $url_parameters = [], array $files = [], array $server = [], ?string $content = null): void
    {
        $client = $this->get_client();
        $client->request(method: $method, uri: $this->get_url($url_parameters), files: $files, server: $server, content: $content);
    }
    public function get_client(): Abstract_Browser
    {
        $driver = $this->get_driver();
        if ($driver instanceof Browser_Kit_Driver) {
            return $driver->get_client();
        }
        throw new \LogicException(sprintf('This page require a "%s" driver.', Browser_Kit_Driver::class));
    }
}
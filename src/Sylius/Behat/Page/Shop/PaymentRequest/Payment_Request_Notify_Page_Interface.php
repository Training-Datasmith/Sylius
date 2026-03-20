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

use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface as BasePageInterface;
use Symfony\Component\Browser_Kit\Abstract_Browser;
interface Payment_Request_Notify_Page_Interface extends Base_Page_Interface
{
    public function open_with_client(string $method, array $url_parameters = [], array $files = [], array $server = [], ?string $content = null): void;
    public function get_client(): Abstract_Browser;
}
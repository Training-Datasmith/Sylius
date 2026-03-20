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
namespace Sylius\Behat\Context\Hybrid\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Sylius\Behat\Context\Setup\Shop_Security_Context;
use Sylius\Behat\Service\Shared_Storage_Interface;
class Security_Context implements Context
{
    public function __construct(private readonly Shop_Security_Context $ui_security_context, private readonly Shop_Security_Context $api_security_context, private readonly Shared_Storage_Interface $shared_storage)
    {
    }
    #[Given('I am a logged in customer on the web store and in the API')]
    public function i_am_a_logged_in_customer_on_the_api_and_the_ui(): void
    {
        $this->api_security_context->i_am_logged_in_customer();
        $this->ui_security_context->i_am_logged_in_as($this->shared_storage->get('user')->get_email());
    }
}
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

class Payment_Method_Notify_Page extends Payment_Request_Notify_Page implements Payment_Method_Notify_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_payment_method_notify';
    }
}
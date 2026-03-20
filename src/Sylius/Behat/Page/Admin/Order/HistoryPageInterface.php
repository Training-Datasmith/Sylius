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
namespace Sylius\Behat\Page\Admin\Order;

use Sylius\Behat\Page\Sylius_Page_Interface;
interface History_Page_Interface extends Sylius_Page_Interface
{
    public function count_billing_address_changes(): int;
    public function count_shipping_address_changes(): int;
}
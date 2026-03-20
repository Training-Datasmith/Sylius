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
namespace Sylius\Behat\Page\Admin\Shipment;

use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface;
interface Show_Page_Interface extends Page_Interface
{
    public function get_amount_of_units(string $product_name): int;
}
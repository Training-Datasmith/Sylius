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
namespace Sylius\Behat\Page\Shop\Account;

use Sylius\Behat\Page\Sylius_Page;
class Register_Thank_You_Page extends Sylius_Page implements Register_Thank_You_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_register_thank_you';
    }
}
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
namespace Sylius\Behat\Page\Shop\Contact;

use Sylius\Behat\Page\Shop\Page_Interface as ShopPageInterface;
interface Contact_Page_Interface extends Shop_Page_Interface
{
    public function send(): void;
}
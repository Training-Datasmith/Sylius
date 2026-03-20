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
namespace Sylius\Behat\Page\Admin\Shipping_Category;

use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface as BaseUpdatePageInteface;
interface Update_Page_Interface extends Base_Update_Page_Inteface
{
    public function is_code_disabled(): bool;
}
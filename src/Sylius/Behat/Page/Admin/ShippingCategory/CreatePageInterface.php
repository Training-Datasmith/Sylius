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

use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface as BaseCreatePageInteface;
interface Create_Page_Interface extends Base_Create_Page_Inteface
{
    public function specify_code(string $code): void;
    public function name_it(string $name): void;
    public function specify_description(string $description): void;
}
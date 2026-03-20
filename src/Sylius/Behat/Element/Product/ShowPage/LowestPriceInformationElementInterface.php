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
namespace Sylius\Behat\Element\Product\Show_Page;

interface Lowest_Price_Information_Element_Interface
{
    public function is_there_information_about_product_lowest_price_with_price(string $lowest_price_before_discount): bool;
    public function is_there_information_about_product_lowest_price(): bool;
}
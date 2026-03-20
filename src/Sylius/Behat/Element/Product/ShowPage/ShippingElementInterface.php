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

interface Shipping_Element_Interface
{
    public function get_product_shipping_category(): string;
    public function get_product_height(): float;
    public function get_product_depth(): float;
    public function get_product_weight(): float;
    public function get_product_width(): float;
}
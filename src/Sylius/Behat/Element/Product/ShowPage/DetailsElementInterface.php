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

interface Details_Element_Interface
{
    public function get_product_code(): string;
    public function has_channel(string $channel_code): bool;
    public function count_channels(): int;
    public function get_product_current_stock(): int;
    public function get_product_tax_category(): string;
}
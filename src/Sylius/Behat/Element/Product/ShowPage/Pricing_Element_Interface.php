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

use Behat\Mink\Element\Node_Element;
interface Pricing_Element_Interface
{
    public function get_price_for_channel(string $channel_code): string;
    public function get_original_price_for_channel(string $channel_code): string;
    public function get_catalog_promotions_names_for_channel(string $channel_code): array;
    public function get_catalog_promotion_links_for_channel(string $channel_code): array;
    public function get_lowest_price_before_discount_for_channel(string $channel_code): string;
    public function get_simple_product_pricing_row_for_channel(string $channel_code): Node_Element;
    public function get_variant_pricing_row_for_channel(string $variant_code, string $channel_code): Node_Element;
}
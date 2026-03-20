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
namespace Sylius\Behat\Page\Admin\Product;

use Sylius\Behat\Page\Admin\Show_To_Edit_Page_Switcher_Interface;
use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
interface Show_Page_Interface extends Sylius_Page_Interface, Show_To_Edit_Page_Switcher_Interface
{
    /** @return string[] */
    public function get_applied_catalog_promotions_links(string $variant_name, string $channel_name): array;
    /** @return string[] */
    public function get_applied_catalog_promotions_names(string $variant_name, string $channel_name): array;
    public function get_name(): string;
    public function get_breadcrumb(): string;
    public function is_simple_product_page(): bool;
    public function is_show_in_shop_button_disabled(): bool;
    public function show_product_in_channel(string $channel): void;
    public function show_product_in_single_channel(): void;
    public function show_variant_edit_page(Product_Variant_Interface $variant): void;
}
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
namespace Sylius\Behat\Page\Shop\Product;

use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface;
interface Index_Page_Interface extends Page_Interface
{
    public function count_products_items(): int;
    public function get_first_product_name_from_list(): string;
    public function get_last_product_name_from_list(): string;
    public function search(string $name): void;
    public function sort(string $order_number): void;
    public function clear_filter(): void;
    public function is_product_on_list(string $product_name): bool;
    public function is_empty(): bool;
    public function get_product_price(string $product_code): string;
    public function get_product_original_price(string $product_code): ?string;
    public function get_product_promotion_label(string $product_name): ?string;
    public function is_product_on_page_with_name(string $product_name): bool;
    public function has_products_in_order(array $product_names): bool;
}
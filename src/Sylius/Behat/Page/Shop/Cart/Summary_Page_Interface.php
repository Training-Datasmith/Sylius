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
namespace Sylius\Behat\Page\Shop\Cart;

use Sylius\Behat\Page\Shop\Page_Interface as ShopPageInterface;
use Sylius\Component\Core\Model\Product_Interface;
interface Summary_Page_Interface extends Shop_Page_Interface
{
    public function get_grand_total(): string;
    public function get_base_grand_total(): string;
    public function get_included_tax_total(): string;
    public function get_excluded_tax_total(): string;
    public function are_taxes_charged(): bool;
    public function get_shipping_total(): string;
    public function has_shipping_total(): bool;
    public function get_promotion_total(): string;
    public function get_items_total(): string;
    public function get_item_total(string $product_name): string;
    public function get_item_unit_regular_price(string $product_name): string;
    public function get_item_unit_price(string $product_name): string;
    public function has_original_price(string $product_name): bool;
    public function get_item_image(int $item_number): string;
    public function is_item_discounted(string $product_name): bool;
    public function remove_product(string $product_name): void;
    public function change_quantity(string $product_name, string $quantity): void;
    public function apply_coupon(string $coupon_code): void;
    public function remove_coupon(): void;
    public function count_order_items(): int;
    public function has_item_named(string $name): bool;
    public function has_item_with_code(string $code): bool;
    public function has_item_with_variant_named(string $variant_name): bool;
    public function get_item_option_value(string $product_name, string $option_name): string;
    public function has_item_with_insufficient_stock(string $product_name): bool;
    public function cart_is_empty(): bool;
    public function get_quantity(string $product_name): int;
    public function get_cart_total(): string;
    public function clear_cart(): void;
    public function checkout(): void;
    public function wait_for_redirect(int $timeout): void;
    public function has_product_out_of_stock_validation_message(Product_Interface $product): bool;
}
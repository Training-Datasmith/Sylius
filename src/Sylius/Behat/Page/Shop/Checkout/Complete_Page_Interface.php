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
namespace Sylius\Behat\Page\Shop\Checkout;

use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Component\Core\Model\Address_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
interface Complete_Page_Interface extends Sylius_Page_Interface
{
    public function has_item_with_product_and_quantity(string $product_name, string $quantity): bool;
    public function has_shipping_address(Address_Interface $address): bool;
    public function has_billing_address(Address_Interface $address): bool;
    public function get_payment_method_name(): string;
    public function has_payment_method(): bool;
    public function get_product_unit_price(Product_Interface $product): int;
    public function has_product_discounted_unit_price_by(Product_Interface $product, int $amount): bool;
    public function has_order_total(int $total): bool;
    public function get_base_currency_order_total(): string;
    public function has_shipping_method(Shipping_Method_Interface $shipping_method): bool;
    public function add_notes(string $notes): void;
    public function has_promotion_total(string $promotion_total): bool;
    public function has_promotion(string $promotion_name): bool;
    public function has_shipping_promotion(string $promotion_name): bool;
    public function get_tax_total(): string;
    public function get_shipping_total(): string;
    public function has_shipping_total(): bool;
    public function has_product_unit_price(Product_Interface $product, string $price): bool;
    public function has_product_out_of_stock_validation_message(Product_Interface $product): bool;
    public function get_validation_errors(): string;
    public function has_locale(string $locale_name): bool;
    public function has_currency(string $currency_code): bool;
    public function confirm_order(): void;
    public function change_address(): void;
    public function change_shipping_method(): void;
    public function change_payment_method(): void;
    public function has_shipping_province_name(string $province_name): bool;
    public function has_billing_province_name(string $province_name): bool;
    public function has_shipping_promotion_with_discount(string $promotion_name, string $discount): bool;
    public function has_order_promotion(string $promotion_name): bool;
}
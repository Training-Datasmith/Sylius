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
namespace Sylius\Behat\Page\Shop\Account\Order;

use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
interface Show_Page_Interface extends Sylius_Page_Interface
{
    public function get_number(): string;
    public function has_shipping_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): bool;
    public function has_billing_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): bool;
    public function choose_payment_method(Payment_Method_Interface $payment_method): void;
    public function pay(): void;
    public function get_chosen_payment_method(): string;
    public function get_total(): string;
    public function get_subtotal(): string;
    public function get_order_shipment_state(): string;
    public function get_shipment_status(): string;
    public function count_items(): int;
    public function get_payment_price(): string;
    public function get_payment_state(): string;
    public function get_order_payment_status(): string;
    public function is_product_in_the_list(string $product_name): bool;
    public function get_item_price(): string;
    public function has_shipping_province_name(string $province_name): bool;
    public function has_billing_province_name(string $province_name): bool;
}
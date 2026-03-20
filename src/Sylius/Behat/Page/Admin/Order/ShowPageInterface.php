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
namespace Sylius\Behat\Page\Admin\Order;

use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Component\Core\Model\Order_Interface;
interface Show_Page_Interface extends Sylius_Page_Interface
{
    public function has_customer(string $customer_email): bool;
    public function has_shipping_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): bool;
    public function has_shipping_address_visible(): bool;
    public function has_billing_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): bool;
    public function has_shipment(string $shipping_method_name): bool;
    public function has_shipment_with_state(string $state);
    public function specify_tracking_code(string $code): void;
    public function can_ship_order(Order_Interface $order): bool;
    public function ship_order(Order_Interface $order): void;
    public function has_payment(string $payment_method_name): bool;
    public function has_payment_with_state(string $state);
    public function can_complete_order_last_payment(Order_Interface $order): bool;
    public function complete_order_last_payment(Order_Interface $order): void;
    public function refund_order_last_payment(Order_Interface $order): void;
    public function count_items(): int;
    public function is_product_in_the_list(string $product_name): bool;
    public function get_items_total(): string;
    public function get_total(): string;
    public function get_shipping_total(): string;
    public function has_shipping_charge(string $shipping_charge, string $shipping_method_name): bool;
    public function has_shipping_tax(string $shipping_tax, string $shipping_method_name): bool;
    public function get_tax_total(): string;
    public function get_order_promotion_total(): string;
    public function has_promotion_discount(string $promotion_name, string $promotion_amount): bool;
    public function has_tax(string $tax): bool;
    public function get_item_code(string $item_name): string;
    public function get_item_unit_price(string $item_name): string;
    public function get_item_discounted_unit_price(string $item_name): string;
    public function get_item_quantity(string $item_name): string;
    public function get_item_subtotal(string $item_name): string;
    public function get_item_discount(string $item_name): string;
    public function get_item_tax(string $item_name): string;
    public function get_item_tax_included_in_price(string $item_name): string;
    public function get_item_total(string $item_name): string;
    public function get_payment_amount(): string;
    public function get_payments_count(): int;
    public function get_shipments_count(): int;
    public function has_cancel_button(): bool;
    public function get_order_state(): string;
    public function get_payment_state(): string;
    public function get_shipping_state(): string;
    public function cancel_order(): void;
    public function delete_order(): void;
    public function has_note(string $note): bool;
    public function has_shipping_province_name(string $province_name): bool;
    public function has_billing_province_name(string $province_name): bool;
    public function get_ip_address_assigned(): string;
    public function get_order_currency(): string;
    public function has_refund_button(): bool;
    public function get_shipping_promotion_data(): string;
    public function get_item_order_discount(string $item_name): string;
    public function has_information_about_no_payment(): bool;
    public function resend_order_confirmation_email(): void;
    public function is_resend_order_confirmation_email_button_visible(): bool;
    public function resend_shipment_confirmation_email(): void;
    public function is_resend_shipment_confirmation_email_button_visible(): bool;
    public function get_shipped_at_date(): string;
}
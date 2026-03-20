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

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Mink\Session;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Component\Core\Model\Order_Interface;
use Symfony\Component\Routing\Router_Interface;
class Show_Page extends Sylius_Page implements Show_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, protected readonly Table_Accessor_Interface $table_accessor)
    {
    }
    public function has_customer(string $customer_email): bool
    {
        return 0 === strcasecmp($customer_email, $this->get_element('customer_email')->get_text());
    }
    public function has_shipping_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): bool
    {
        $shipping_address_text = $this->get_element('shipping_address')->get_text();
        return $this->has_address($shipping_address_text, $customer_name, $street, $postcode, $city, $country_name);
    }
    public function has_shipping_address_visible(): bool
    {
        try {
            $this->get_element('shipping_address');
        } catch (Element_Not_Found_Exception) {
            return false;
        }
        return true;
    }
    public function has_billing_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): bool
    {
        $billing_address_text = $this->get_element('billing_address')->get_text();
        return $this->has_address($billing_address_text, $customer_name, $street, $postcode, $city, $country_name);
    }
    public function has_shipment(string $shipping_method_name): bool
    {
        foreach ($this->get_element('shipments')->find_all('css', '[data-test-shipment-method]') as $shipment_method) {
            if (0 === strcasecmp($shipping_method_name, (string) $shipment_method->get_text())) {
                return true;
            }
        }
        return false;
    }
    public function has_shipment_with_state(string $state): bool
    {
        foreach ($this->get_element('shipments')->find_all('css', '[data-test-shipment-state]') as $shipment_state) {
            if (0 === strcasecmp($state, (string) $shipment_state->get_text())) {
                return true;
            }
        }
        return false;
    }
    public function specify_tracking_code(string $code): void
    {
        $this->get_element('shipment_tracking')->set_value($code);
    }
    public function can_ship_order(Order_Interface $order): bool
    {
        return $this->has_element('shipment_ship_button');
    }
    public function ship_order(Order_Interface $order): void
    {
        $this->get_element('shipment_ship_button')->press();
    }
    public function has_payment(string $payment_method_name): bool
    {
        foreach ($this->get_element('payments')->find_all('css', '[data-test-payment-method]') as $payment_method) {
            if (0 === strcasecmp($payment_method_name, (string) $payment_method->get_text())) {
                return true;
            }
        }
        return false;
    }
    public function has_payment_with_state(string $state): bool
    {
        foreach ($this->get_element('payments')->find_all('css', '[data-test-payment-state]') as $payment_state) {
            if (0 === strcasecmp($state, (string) $payment_state->get_text())) {
                return true;
            }
        }
        return false;
    }
    public function can_complete_order_last_payment(Order_Interface $order): bool
    {
        $last_payment = $order->get_last_payment();
        return $this->has_element('payment_complete', ['%paymentId%' => $last_payment->get_id()]);
    }
    public function complete_order_last_payment(Order_Interface $order): void
    {
        $last_payment = $order->get_last_payment();
        $this->get_element('payment_complete', ['%paymentId%' => $last_payment->get_id()])->submit();
    }
    public function refund_order_last_payment(Order_Interface $order): void
    {
        $last_payment = $order->get_last_payment();
        $this->get_element('payment_refund', ['%paymentId%' => $last_payment->get_id()])->submit();
    }
    public function count_items(): int
    {
        return $this->table_accessor->count_table_body_rows($this->get_element('table-items'));
    }
    public function is_product_in_the_list(string $product_name): bool
    {
        return $this->has_element('item', ['%name%' => $product_name]);
    }
    public function get_items_total(): string
    {
        return $this->get_element('items_total')->get_text();
    }
    public function get_total(): string
    {
        return $this->get_element('order_total')->get_text();
    }
    public function get_shipping_total(): string
    {
        return $this->get_element('shipping_total')->get_text();
    }
    public function get_tax_total(): string
    {
        $tax_total_element = $this->get_element('tax_total');
        return trim(str_replace('Tax total:', '', $tax_total_element->get_text()));
    }
    public function has_shipping_charge(string $shipping_charge, string $shipping_method_name): bool
    {
        $shipping = $this->get_element('shipping', ['%name%' => $shipping_method_name]);
        return 0 === strcasecmp($shipping_charge, (string) $shipping->find('css', '[data-test-base-value]')->get_text());
    }
    public function has_shipping_tax(string $shipping_tax, string $shipping_method_name): bool
    {
        $shipping = $this->get_element('shipping', ['%name%' => $shipping_method_name]);
        return 0 === strcasecmp($shipping_tax, (string) $shipping->find('css', '[data-test-tax-value]')->get_text());
    }
    public function get_order_promotion_total(): string
    {
        return $this->get_element('promotion_total')->get_text();
    }
    public function has_promotion_discount(string $promotion_name, string $promotion_amount): bool
    {
        $promotion = $this->get_element('promotion', ['%name%' => $promotion_name]);
        return 0 === strcasecmp($promotion_amount, (string) $promotion->find('css', '[data-test-discount]')->get_text());
    }
    public function has_tax(string $tax): bool
    {
        $taxes_text = $this->get_element('taxes')->get_text();
        return stripos($taxes_text, $tax) !== false;
    }
    public function get_item_code(string $item_name): string
    {
        return $this->get_row_with_item($item_name)->find('css', '[data-test-code]')->get_text();
    }
    public function get_item_unit_price(string $item_name): string
    {
        return $this->get_row_with_item($item_name)->find('css', '[data-test-unit-price]')->get_text();
    }
    public function get_item_discounted_unit_price(string $item_name): string
    {
        return $this->get_row_with_item($item_name)->find('css', '[data-test-discounted-unit-price]')->get_text();
    }
    public function get_item_order_discount(string $item_name): string
    {
        return $this->get_row_with_item($item_name)->find('css', '[data-test-distributed-order-discount]')->get_text();
    }
    public function get_item_quantity(string $item_name): string
    {
        return $this->get_row_with_item($item_name)->find('css', '[data-test-quantity]')->get_text();
    }
    public function get_item_subtotal(string $item_name): string
    {
        return $this->get_row_with_item($item_name)->find('css', '[data-test-subtotal]')->get_text();
    }
    public function get_item_discount(string $item_name): string
    {
        return $this->get_row_with_item($item_name)->find('css', '[data-test-unit-discount]')->get_text();
    }
    public function get_item_tax(string $item_name): string
    {
        return $this->get_row_with_item($item_name)->find('css', '[data-test-tax-excluded]')->get_text();
    }
    public function get_item_tax_included_in_price(string $item_name): string
    {
        return $this->get_row_with_item($item_name)->find('css', '[data-test-tax-included]')->get_text();
    }
    public function get_item_total(string $item_name): string
    {
        return $this->get_row_with_item($item_name)->find('css', '[data-test-total]')->get_text();
    }
    public function get_payment_amount(): string
    {
        $payments_price = $this->get_element('payments')->find('css', '[data-test-payment-amount]');
        return $payments_price->get_text();
    }
    public function get_payments_count(): int
    {
        $payments = $this->get_element('payments')->find_all('css', '[data-test-payment]');
        return count($payments);
    }
    public function get_shipments_count(): int
    {
        try {
            $shipments = $this->get_element('shipments')->find_all('css', '.item');
        } catch (Element_Not_Found_Exception) {
            return 0;
        }
        return count($shipments);
    }
    public function has_cancel_button(): bool
    {
        return $this->has_element('cancel_order');
    }
    public function cancel_order(): void
    {
        $this->get_element('cancel_order')->click();
    }
    public function get_order_state(): string
    {
        return $this->get_element('order_state')->get_text();
    }
    public function get_payment_state(): string
    {
        return $this->get_element('order_payment_state')->get_text();
    }
    public function get_shipping_state(): string
    {
        return $this->get_element('order_shipping_state')->get_text();
    }
    public function delete_order(): void
    {
        $this->get_document()->press_button('Delete');
    }
    public function has_note(string $note): bool
    {
        $notes = $this->get_element('notes');
        return $notes->get_text() === $note;
    }
    public function has_shipping_province_name(string $province_name): bool
    {
        $shipping_address_text = $this->get_element('shipping_address')->get_text();
        return false !== stripos($shipping_address_text, $province_name);
    }
    public function has_billing_province_name(string $province_name): bool
    {
        $billing_address_text = $this->get_element('billing_address')->get_text();
        return false !== stripos($billing_address_text, $province_name);
    }
    public function get_ip_address_assigned(): string
    {
        return $this->get_element('ip_address')->get_text();
    }
    public function get_order_currency(): string
    {
        Driver_Helper::wait_for_page_to_load($this->get_session());
        return $this->get_element('currency')->get_text();
    }
    public function has_refund_button(): bool
    {
        return $this->get_document()->has_button('Refund');
    }
    public function get_shipping_promotion_data(): string
    {
        return $this->get_element('shipping_promotion_discount')->get_text();
    }
    public function get_route_name(): string
    {
        return 'sylius_admin_order_show';
    }
    public function has_information_about_no_payment(): bool
    {
        return $this->get_element('payments')->has('css', '[data-test-no-payments]');
    }
    public function resend_order_confirmation_email(): void
    {
        $this->get_element('resend_order_confirmation_email')->click();
    }
    public function is_resend_order_confirmation_email_button_visible(): bool
    {
        return $this->get_document()->has('css', '[data-test-resend-order-confirmation-email]');
    }
    public function resend_shipment_confirmation_email(): void
    {
        $this->get_element('resend_shipment_confirmation_email')->click();
    }
    public function is_resend_shipment_confirmation_email_button_visible(): bool
    {
        return $this->get_document()->has('css', '[data-test-resend-shipment-confirmation-email]');
    }
    public function get_shipped_at_date(): string
    {
        return $this->get_element('shipment_shipped_at_date')->get_text();
    }
    /**
     * @return array<string, string>
     */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['billing_address' => '[data-test-billing-address]', 'cancel_order' => '[data-test-cancel-order]', 'currency' => '[data-test-currency]', 'customer_email' => '[data-test-customer] [data-test-email]', 'ip_address' => '[data-test-ip-address]', 'item' => '[data-test-item="%name%"]', 'items_total' => '[data-test-items-total]', 'notes' => '[data-test-notes]', 'order_payment_state' => '[data-test-order-payment-state]', 'order_shipping_state' => '[data-test-order-shipping-state]', 'order_state' => '[data-test-order-state]', 'order_total' => '[data-test-order-total]', 'payment_complete' => '[data-test-complete-payment="%paymentId%"]', 'payment_refund' => '[data-test-refund-payment="%paymentId%"]', 'payments' => '[data-test-payments]', 'promotion' => '[data-test-promotion="%name%"]', 'promotion_total' => '[data-test-promotion-total]', 'resend_order_confirmation_email' => '[data-test-resend-order-confirmation-email]', 'resend_shipment_confirmation_email' => '[data-test-resend-shipment-confirmation-email]', 'shipment_ship_button' => '[data-test-shipment-ship-button]', 'shipment_shipped_at_date' => '[data-test-shipments] [data-test-shipped-at-date]', 'shipment_tracking' => '[data-test-shipment-tracking]', 'shipments' => '[data-test-shipments]', 'shipping' => '[data-test-shipping="%name%"]', 'shipping_address' => '[data-test-shipping-address]', 'shipping_adjustment_name' => '#shipping-adjustment-label', 'shipping_promotion_discount' => '[data-test-shipping-promotion-discount]', 'shipping_tax' => '#shipping-tax-value', 'shipping_total' => '[data-test-shipping-total]', 'table-items' => '[data-test-table-items]', 'tax_total' => '[data-test-tax-total]', 'taxes' => '#taxes']);
    }
    protected function has_address(string $element_text, string $customer_name, string $street, string $postcode, string $city, string $country_name): bool
    {
        return stripos($element_text, $customer_name) !== false && stripos($element_text, $street) !== false && stripos($element_text, $city) !== false && stripos($element_text, $country_name . ' ' . $postcode) !== false;
    }
    protected function get_row_with_item(string $item_name): Node_Element
    {
        return $this->get_element('item', ['%name%' => $item_name]);
    }
}
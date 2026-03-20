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

use Behat\Mink\Session;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Symfony\Component\Routing\Router_Interface;
class Show_Page extends Sylius_Page implements Show_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, protected Table_Accessor_Interface $table_accessor)
    {
    }
    public function get_route_name(): string
    {
        return 'sylius_shop_account_order_show';
    }
    public function get_number(): string
    {
        $number_text = $this->get_element('number')->get_text();
        return str_replace('#', '', $number_text);
    }
    public function has_shipping_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): bool
    {
        $shipping_address_text = $this->get_element('shipping_address')->get_text();
        return $this->has_address($shipping_address_text, $customer_name, $street, $postcode, $city, $country_name);
    }
    public function has_billing_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): bool
    {
        $billing_address_text = $this->get_element('billing_address')->get_text();
        return $this->has_address($billing_address_text, $customer_name, $street, $postcode, $city, $country_name);
    }
    public function choose_payment_method(Payment_Method_Interface $payment_method): void
    {
        $payment_method_element = $this->get_element('payment_method', ['%name%' => $payment_method->get_name()]);
        $payment_method_element->select_option($payment_method_element->get_attribute('value'));
    }
    public function pay(): void
    {
        $this->get_element('pay_link')->click();
    }
    public function get_chosen_payment_method(): string
    {
        $payment_method_items = $this->get_document()->find_all('css', '[data-test-payment-item]');
        foreach ($payment_method_items as $method) {
            if ($method->find('css', '[data-test-payment-method-select]')->has_attribute('checked')) {
                return $method->find('css', '[data-test-payment-method-checkbox]')->get_text();
            }
        }
        return '';
    }
    public function get_total(): string
    {
        return $this->get_element('total')->get_text();
    }
    public function get_subtotal(): string
    {
        return $this->get_element('subtotal')->get_text();
    }
    public function get_order_shipment_state(): string
    {
        return $this->get_element('order_shipment_state')->get_text();
    }
    public function get_shipment_status(): string
    {
        return $this->get_element('shipment_state')->get_text();
    }
    public function count_items(): int
    {
        return $this->table_accessor->count_table_body_rows($this->get_element('order_items'));
    }
    public function get_payment_price(): string
    {
        return $this->get_element('payment_price')->get_text();
    }
    public function get_payment_state(): string
    {
        return $this->get_element('payment_state')->get_text();
    }
    public function get_order_payment_status(): string
    {
        return $this->get_element('order_payment_state')->get_text();
    }
    public function is_product_in_the_list(string $product_name): bool
    {
        return $this->has_element('product_name', ['%productName%' => $product_name]);
    }
    public function get_item_price(): string
    {
        return $this->get_element('product_price')->get_text();
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
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['billing_address' => '[data-test-billing-address]', 'checked_payment_method' => '[data-test-payment-item] div.form-check:has(input:checked)', 'number' => '[data-test-order-number]', 'order_items' => '[data-test-order-table]', 'order_payment_state' => '[data-test-order-payment-state]', 'order_shipment_state' => '[data-test-order-shipment-state]', 'pay_link' => '[data-test-pay-link]', 'payment_method' => '[data-test-payment-item]:contains("%name%") [data-test-payment-method-select]', 'payment_price' => '[data-test-payment-price]', 'payment_state' => '[data-test-payment-state]', 'product_name' => '[data-test-order-table] [data-test-product-name="%productName%"]', 'product_price' => '[data-test-order-table] [data-test-product-unit-price]', 'shipment_state' => '[data-test-shipment-state]', 'shipping_address' => '[data-test-shipping-address]', 'subtotal' => '[data-test-subtotal]', 'total' => '[data-test-order-total]']);
    }
    protected function has_address(string $element_text, string $customer_name, string $street, string $postcode, string $city, string $country_name): bool
    {
        return stripos($element_text, $customer_name) !== false && stripos($element_text, $street) !== false && stripos($element_text, $city . ', ' . $postcode) !== false && stripos($element_text, $country_name) !== false;
    }
}
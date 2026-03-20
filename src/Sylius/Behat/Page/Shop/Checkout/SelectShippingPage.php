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

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Driver_Helper;
class Select_Shipping_Page extends Sylius_Page implements Select_Shipping_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_checkout_select_shipping';
    }
    public function select_shipping_method(string $shipping_method): void
    {
        if (Driver_Helper::is_javascript($this->get_driver())) {
            Driver_Helper::wait_for_page_to_load($this->get_session());
            $this->get_element('shipping_method_select', ['%shipping_method%' => $shipping_method])->click();
            return;
        }
        $shipping_method_option_element = $this->get_element('shipping_method_option', ['%shipping_method%' => $shipping_method]);
        $shipping_method_option_element->select_option($shipping_method_option_element->get_attribute('value'));
    }
    public function get_shipping_methods(): array
    {
        $inputs = $this->get_document()->find_all('css', '[data-test-shipping-method-select]');
        $shipping_methods = [];
        foreach ($inputs as $input) {
            $shipping_methods[] = trim((string) $input->get_parent()->get_text());
        }
        return $shipping_methods;
    }
    public function get_selected_shipping_method_name(): ?string
    {
        return $this->has_element('shipping_method_option_selected') ? $this->get_element('shipping_method_option_selected')->get_parent()->get_text() : null;
    }
    public function has_shipping_method_fee(string $shipping_method_name, string $fee): bool
    {
        $fee_element = $this->get_element('shipping_method_fee', ['%shipping_method%' => $shipping_method_name])->get_text();
        return str_contains($fee_element, $fee);
    }
    public function get_item_subtotal(string $item_name): string
    {
        $item_slug = strtolower(str_replace('\"', '', str_replace(' ', '-', $item_name)));
        $subtotal_table = $this->get_element('checkout_subtotal');
        return $subtotal_table->find('css', sprintf('[data-test-item-subtotal="%s"]', $item_slug))->get_text();
    }
    public function next_step(): void
    {
        $this->get_element('next_step')->press();
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    public function change_address(): void
    {
        $this->get_document()->click_link('Change address');
    }
    public function change_address_by_step_label(): void
    {
        $this->get_element('address')->click();
    }
    public function get_purchaser_identifier(): string
    {
        return $this->get_element('purchaser_email')->get_text();
    }
    public function get_validation_message_for_shipment(): string
    {
        $found_element = $this->get_element('shipment');
        if (null === $found_element) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Items element');
        }
        $validation_message = $found_element->find('css', '[data-test-validation-error]');
        if (null === $validation_message) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '[data-test-validation-error]');
        }
        return $validation_message->get_text();
    }
    public function has_no_available_shipping_methods_message(): bool
    {
        return $this->has_element('warning_no_shipping_methods');
    }
    public function is_next_step_button_enabled(): bool
    {
        return !$this->get_element('next_step')->has_class('disabled');
    }
    public function has_shipping_method(string $shipping_method_name): bool
    {
        return $this->has_element('shipping_method_item', ['%shipping_method%' => $shipping_method_name]);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['address' => '[data-test-step-address]', 'checkout_subtotal' => '[data-test-checkout-subtotal]', 'next_step' => '[data-test-next-step]', 'purchaser_email' => '[data-test-purchaser-name-or-email]', 'shipping_method_fee' => '[data-test-shipping-item]:contains("%shipping_method%") [data-test-shipping-method-fee]', 'shipping_method_item' => '[data-test-shipping-item]:contains("%shipping_method%")', 'shipping_method_option' => '[data-test-shipping-item]:contains("%shipping_method%") [data-test-shipping-method-select]', 'shipping_method_option_selected' => '[data-test-shipping-method-select][checked="checked"]', 'shipping_method_select' => '[data-test-shipping-item]:contains("%shipping_method%") [data-test-shipping-method-checkbox]', 'warning_no_shipping_methods' => '[data-test-order-cannot-be-shipped]']);
    }
}
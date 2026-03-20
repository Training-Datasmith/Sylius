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
class Select_Payment_Page extends Sylius_Page implements Select_Payment_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_checkout_select_payment';
    }
    public function select_payment_method(string $payment_method): void
    {
        if (Driver_Helper::is_javascript($this->get_driver())) {
            Driver_Helper::wait_for_page_to_load($this->get_session());
            $this->get_element('payment_method_select', ['%payment_method%' => $payment_method])->click();
            return;
        }
        $payment_method_option_element = $this->get_element('payment_method_option', ['%payment_method%' => $payment_method]);
        $payment_method_option_element->select_option($payment_method_option_element->get_attribute('value'));
    }
    public function has_payment_method(string $payment_method_name): bool
    {
        try {
            $this->get_element('payment_method_option', ['%payment_method%' => $payment_method_name]);
        } catch (Element_Not_Found_Exception) {
            return false;
        }
        return true;
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
    public function change_shipping_method(): void
    {
        $this->get_document()->click_link('Change shipping method');
    }
    public function change_shipping_method_by_step_label(): void
    {
        $this->get_element('shipping_step_label')->click();
    }
    public function change_address_by_step_label(): void
    {
        $this->get_element('address')->click();
    }
    public function has_no_available_payment_methods_warning(): bool
    {
        return $this->has_element('warning_no_payment_methods');
    }
    public function is_next_step_button_unavailable(): bool
    {
        return $this->get_element('next_step')->has_class('disabled');
    }
    public function get_payment_methods(): array
    {
        $inputs = $this->get_document()->find_all('css', '[data-test-payment-method-select]');
        $payment_methods = [];
        foreach ($inputs as $input) {
            $payment_methods[] = trim((string) $input->get_parent()->get_text());
        }
        return $payment_methods;
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['address' => '[data-test-step-address]', 'checkout_subtotal' => '[data-test-checkout-subtotal]', 'next_step' => '[data-test-next-step]', 'order_cannot_be_paid_message' => '[data-test-order-cannot-be-paid]', 'payment_method_option' => '[data-test-payment-item]:contains("%payment_method%") [data-test-payment-method-select]', 'payment_method_select' => '[data-test-payment-item]:contains("%payment_method%") [data-test-payment-method-checkbox]', 'shipping_step_label' => '[data-test-step-shipping]', 'warning_no_payment_methods' => '[data-test-order-cannot-be-paid]']);
    }
}
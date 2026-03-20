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
namespace Sylius\Behat\Page\Shop\Order;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Driver_Helper;
class Show_Page extends Sylius_Page implements Show_Page_Interface
{
    public function has_pay_action(): bool
    {
        return $this->has_element('pay_link');
    }
    public function can_be_paid(): bool
    {
        return $this->has_pay_action() && !$this->get_element('pay_link')->has_attribute('disabled');
    }
    public function pay(): void
    {
        $this->get_element('pay_link')->click();
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    public function get_notifications(): array
    {
        /** @var NodeElement[] $notificationElements */
        $notification_elements = $this->get_document()->find_all('css', '[data-test-flash-messages]');
        $notifications = [];
        foreach ($notification_elements as $notification_element) {
            $notifications[] = $notification_element->get_text();
        }
        return $notifications;
    }
    public function choose_payment_method(string $payment_method_name): void
    {
        Driver_Helper::wait_for_page_to_load($this->get_session());
        $payment_method_element = $this->get_element('payment_method', ['%name%' => $payment_method_name]);
        $payment_method_element->select_option($payment_method_element->get_attribute('value'));
    }
    public function get_route_name(): string
    {
        return 'sylius_shop_order_show';
    }
    public function get_amount_of_items(): int
    {
        $payment_items = $this->get_document()->find_all('css', '[data-test-payment-item]');
        return count($payment_items);
    }
    public function get_chosen_payment_method(): string
    {
        Driver_Helper::wait_for_page_to_load($this->get_session());
        $payment_method_items = $this->get_document()->find_all('css', '[data-test-payment-item]');
        foreach ($payment_method_items as $method) {
            if ($method->find('css', '[data-test-payment-method-select]')->has_attribute('checked')) {
                return $method->find('css', '[data-test-payment-method-checkbox]')->get_text();
            }
        }
        return '';
    }
    public function get_payment_validation_message(): string
    {
        $message = '';
        $validation_elements = $this->get_document()->find_all('css', '[data-test-validation-error]');
        foreach ($validation_elements as $validation_element) {
            $message .= $validation_element->get_text();
        }
        return $message;
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['pay_link' => '[data-test-pay-link]', 'payment_method' => '[data-test-payment-item]:contains("%name%") [data-test-payment-method-select]']);
    }
}
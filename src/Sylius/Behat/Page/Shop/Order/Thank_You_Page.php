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

use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Driver_Helper;
class Thank_You_Page extends Sylius_Page implements Thank_You_Page_Interface
{
    public function go_to_the_change_payment_method_page(): void
    {
        $this->get_element('payment_method_page')->click();
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    public function go_to_order_details_in_account(): void
    {
        $this->get_element('order_details_in_account')->click();
    }
    public function has_thank_you_message(): bool
    {
        $thank_you_message = $this->get_element('thank_you')->get_text();
        return str_contains($thank_you_message, 'Thank you!');
    }
    public function get_instructions(): string
    {
        return $this->get_element('instructions')->get_text();
    }
    public function has_instructions(): bool
    {
        return $this->has_element('instructions');
    }
    public function has_change_payment_method_button(): bool
    {
        return $this->has_element('payment_method_page');
    }
    public function has_registration_button(): bool
    {
        return $this->has_element('create_account_button');
    }
    public function create_account(): void
    {
        $this->get_element('create_account_button')->click();
    }
    public function get_route_name(): string
    {
        return 'sylius_shop_thank_you';
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['create_account_button' => '[data-test-button="create-an-account"]', 'instructions' => '[data-test-payment-method-instructions]', 'order_details_in_account' => '[data-test-button="show-order-in-account"]', 'payment_method_page' => '[data-test-button="payment-method-page"]', 'thank_you' => '[data-test-thank-you]']);
    }
}
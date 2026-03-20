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
namespace Sylius\Behat\Page\Shop\Account;

use Sylius\Behat\Page\Sylius_Page;
class Dashboard_Page extends Sylius_Page implements Dashboard_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_account_dashboard';
    }
    public function has_customer_name(string $name): bool
    {
        return $this->has_value_in_customer_section($name);
    }
    public function has_customer_email(string $email): bool
    {
        return $this->has_value_in_customer_section($email);
    }
    public function is_verified(): bool
    {
        return !$this->has_element('verification');
    }
    public function has_resend_verification_email_button(): bool
    {
        return $this->has_element('verification_button');
    }
    public function press_resend_verification_email(): void
    {
        $this->get_element('verification_button')->press();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['customer' => '[data-test-customer-information]', 'verification' => '[data-test-verification-form]', 'verification_button' => '[data-test-verification-button]']);
    }
    protected function has_value_in_customer_section(string $value): bool
    {
        $customer_text = $this->get_element('customer')->get_text();
        return stripos($customer_text, $value) !== false;
    }
}
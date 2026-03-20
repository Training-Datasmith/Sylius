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
namespace Sylius\Behat\Page\Admin\Customer;

use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Driver_Helper;
class Show_Page extends Sylius_Page implements Show_Page_Interface
{
    public function delete_account(): void
    {
        $this->get_element('delete_account_button')->press();
    }
    public function get_customer_email(): string
    {
        return $this->get_element('customer_email')->get_text();
    }
    public function get_customer_phone_number(): string
    {
        return $this->get_element('customer_phone_number')->get_text();
    }
    public function get_customer_name(): string
    {
        return $this->get_element('customer_name')->get_text();
    }
    public function get_registration_date(): \DateTimeInterface
    {
        return \DateTime::create_from_format('d-m-Y H:i:s', $this->get_element('registration_date')->get_text());
    }
    public function get_default_address(): string
    {
        return $this->get_element('default_address')->get_text();
    }
    public function has_account(): bool
    {
        return $this->has_element('delete_account_button');
    }
    public function is_subscribed_to_newsletter(): bool
    {
        return null !== $this->get_element('subscribed_to_newsletter')->find('css', 'svg.text-green');
    }
    public function has_default_address_province_name(string $province_name): bool
    {
        $default_address_province = $this->get_element('default_address')->get_text();
        return false !== stripos($default_address_province, $province_name);
    }
    public function has_verified_email(): bool
    {
        return null !== $this->get_element('verified_email')->find('css', 'svg.text-green');
    }
    public function get_group_name(): string
    {
        return $this->get_element('group')->get_text();
    }
    public function has_email_verification_information(): bool
    {
        return null === $this->get_document()->find('css', '#verified-email');
    }
    public function has_impersonate_button(): bool
    {
        return $this->has_element('impersonate_button');
    }
    public function impersonate(): void
    {
        if (Driver_Helper::is_javascript($this->get_driver())) {
            $this->get_element('actions_button')->click();
        }
        $this->get_element('impersonate_button')->click();
    }
    public function has_customer_placed_any_orders(): bool
    {
        return !$this->has_element('statistics_no_orders');
    }
    public function get_orders_count_in_channel(string $channel_code): int
    {
        return (int) $this->get_element('statistics_orders_count', ['%channelCode%' => $channel_code])->get_text();
    }
    public function get_orders_total_in_channel(string $channel_code): string
    {
        return $this->get_element('statistics_orders_total', ['%channelCode%' => $channel_code])->get_text();
    }
    public function get_average_total_in_channel(string $channel_code): string
    {
        return $this->get_element('statistics_orders_average', ['%channelCode%' => $channel_code])->get_text();
    }
    public function get_success_flash_message(): string
    {
        return trim($this->get_element('flash_message')->get_text());
    }
    public function get_route_name(): string
    {
        return 'sylius_admin_customer_show';
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['actions_button' => '[data-test-customer-actions]', 'customer_email' => '[data-test-customer-email]', 'customer_name' => '[data-test-customer-fullname]', 'customer_phone_number' => '[data-test-customer-phone]', 'default_address' => '[data-test-customer-default-address]', 'delete_account_button' => '[data-test-customer-actions-delete]', 'group' => '[data-test-customer-group]', 'impersonate_button' => '[data-test-customer-actions-impersonate]', 'registration_date' => '[data-test-customer-since]', 'statistics_no_orders' => '[data-test-customer-statistics-no-orders]', 'statistics_orders_average' => '[data-test-customer-statistics-%channelCode%-orders-average]', 'statistics_orders_count' => '[data-test-customer-statistics-%channelCode%-orders-count]', 'statistics_orders_total' => '[data-test-customer-statistics-%channelCode%-orders-total]', 'subscribed_to_newsletter' => '[data-test-customer-subscribed-to-newsletter]', 'verified_email' => '[data-test-customer-verified-email]']);
    }
}
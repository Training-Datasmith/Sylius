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

use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface;
interface Show_Page_Interface extends Page_Interface
{
    public function delete_account(): void;
    public function get_customer_email(): string;
    public function get_customer_phone_number(): string;
    public function get_customer_name(): string;
    public function get_registration_date(): \DateTimeInterface;
    public function get_default_address(): string;
    public function has_account(): bool;
    public function is_subscribed_to_newsletter(): bool;
    public function has_default_address_province_name(string $province_name): bool;
    public function has_verified_email(): bool;
    public function get_group_name(): string;
    public function has_email_verification_information(): bool;
    public function has_impersonate_button(): bool;
    public function impersonate(): void;
    public function has_customer_placed_any_orders(): bool;
    public function get_orders_count_in_channel(string $channel_code): int;
    public function get_orders_total_in_channel(string $channel_code): string;
    public function get_average_total_in_channel(string $channel_code): string;
    public function get_success_flash_message(): string;
}
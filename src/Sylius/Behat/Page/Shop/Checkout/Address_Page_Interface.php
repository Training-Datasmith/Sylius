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

use Sylius\Behat\Page\Shop\Page_Interface as ShopPageInterface;
use Sylius\Component\Core\Model\Address_Interface;
interface Address_Page_Interface extends Shop_Page_Interface
{
    public function choose_different_shipping_address(): void;
    public function choose_different_billing_address(): void;
    public function check_invalid_credentials_validation(): bool;
    public function check_validation_message_for(string $element, string $message): bool;
    public function check_form_validation_message(string $message): bool;
    public function specify_shipping_address(Address_Interface $shipping_address): void;
    public function select_shipping_address_province(string $province): void;
    public function specify_billing_address(Address_Interface $billing_address): void;
    public function select_billing_address_province(string $province): void;
    public function specify_email(?string $email): void;
    public function specify_billing_address_full_name(string $full_name): void;
    public function can_sign_in(): bool;
    public function sign_in(): void;
    public function specify_password(string $password): void;
    public function get_item_subtotal(string $item_name): string;
    public function get_shipping_address_country(): string;
    public function get_billing_address_country(): string;
    public function next_step(): void;
    public function back_to_store(): void;
    public function specify_billing_address_province(string $province_name): void;
    public function specify_shipping_address_province(string $province_name): void;
    public function has_shipping_address_input(): bool;
    public function has_billing_address_input(): bool;
    public function has_email_input(): bool;
    public function select_shipping_address_from_address_book(Address_Interface $address): void;
    public function select_billing_address_from_address_book(Address_Interface $address): void;
    public function get_pre_filled_shipping_address(): Address_Interface;
    public function get_pre_filled_billing_address(): Address_Interface;
    /** @return string[] */
    public function get_available_shipping_countries(): array;
    /** @return string[] */
    public function get_available_billing_countries(): array;
    public function is_different_shipping_address_checked(): bool;
    public function is_shipping_address_visible(): bool;
    public function wait_for_form_to_stop_loading(): void;
}
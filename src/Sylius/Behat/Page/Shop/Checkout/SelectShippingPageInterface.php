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

use Sylius\Behat\Page\Sylius_Page_Interface;
interface Select_Shipping_Page_Interface extends Sylius_Page_Interface
{
    public function select_shipping_method(string $shipping_method): void;
    public function get_shipping_methods(): array;
    public function get_selected_shipping_method_name(): ?string;
    public function has_shipping_method_fee(string $shipping_method_name, string $fee): bool;
    public function get_item_subtotal(string $item_name): string;
    public function next_step(): void;
    public function change_address(): void;
    public function change_address_by_step_label(): void;
    public function get_purchaser_identifier(): string;
    public function get_validation_message_for_shipment(): string;
    public function has_no_available_shipping_methods_message(): bool;
    public function is_next_step_button_enabled(): bool;
    public function has_shipping_method(string $shipping_method_name): bool;
}
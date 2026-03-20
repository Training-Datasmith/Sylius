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
interface Select_Payment_Page_Interface extends Sylius_Page_Interface
{
    public function select_payment_method(string $payment_method): void;
    public function has_payment_method(string $payment_method_name): bool;
    public function get_item_subtotal(string $item_name): string;
    public function next_step(): void;
    public function change_shipping_method(): void;
    public function change_shipping_method_by_step_label(): void;
    public function change_address_by_step_label(): void;
    public function has_no_available_payment_methods_warning(): bool;
    public function is_next_step_button_unavailable(): bool;
    public function get_payment_methods(): array;
}
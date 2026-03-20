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

use Sylius\Behat\Page\Sylius_Page_Interface;
interface Show_Page_Interface extends Sylius_Page_Interface
{
    public function has_pay_action(): bool;
    public function can_be_paid(): bool;
    public function pay(): void;
    public function choose_payment_method(string $payment_method_name): void;
    public function get_notifications(): array;
    public function get_amount_of_items(): int;
    public function get_chosen_payment_method(): string;
    public function get_payment_validation_message(): string;
}
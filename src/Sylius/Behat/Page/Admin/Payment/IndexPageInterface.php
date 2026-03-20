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
namespace Sylius\Behat\Page\Admin\Payment;

use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as BaseIndexPageInterface;
interface Index_Page_Interface extends Base_Index_Page_Interface
{
    public function choose_state_to_filter(string $payment_state): void;
    public function complete_payment_of_order_with_number(string $order_number): void;
    public function get_payment_state_by_order_number(string $order_number): string;
    public function is_payment_with_order_number_in_position(string $order_number, int $position): bool;
    public function show_order_page_for_nth_payment(int $position): void;
    public function show_payment_request_of_nth_payment(int $position): void;
    public function choose_channel_filter(string $channel_name): void;
}
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
namespace Sylius\Behat\Page\Shop\Account\Order;

use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Component\Core\Model\Order_Interface;
interface Index_Page_Interface extends Sylius_Page_Interface
{
    public function count_orders(): int;
    public function change_payment_method(Order_Interface $order): void;
    public function has_flash_message(string $message): bool;
    public function is_order_with_number_in_the_list(string $number): bool;
    public function open_last_order_page(): void;
}
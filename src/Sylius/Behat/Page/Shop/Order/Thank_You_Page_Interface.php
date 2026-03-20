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
interface Thank_You_Page_Interface extends Sylius_Page_Interface
{
    public function go_to_the_change_payment_method_page(): void;
    public function go_to_order_details_in_account(): void;
    public function has_thank_you_message(): bool;
    public function get_instructions(): string;
    public function has_instructions(): bool;
    public function has_change_payment_method_button(): bool;
    public function has_registration_button(): bool;
    public function create_account(): void;
}
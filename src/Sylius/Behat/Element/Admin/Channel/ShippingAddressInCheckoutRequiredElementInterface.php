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
namespace Sylius\Behat\Element\Admin\Channel;

interface Shipping_Address_In_Checkout_Required_Element_Interface
{
    public function require_shipping_address_in_checkout(): void;
    public function require_billing_address_in_checkout(): void;
    public function require_address_type_in_checkout(string $type): void;
    public function is_shipping_address_in_checkout_required(): bool;
    public function get_required_address_type_in_checkout(): string;
}
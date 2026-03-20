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
namespace Sylius\Behat\Element\Admin\Promotion_Coupon;

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
interface Form_Element_Interface extends Base_Form_Element_Interface
{
    public function set_usage_limit(int $limit): void;
    public function set_customer_usage_limit(int $limit): void;
    public function set_expires_at(\DateTimeInterface $date): void;
    public function toggle_reusable_from_cancelled_orders(bool $reusable): void;
    public function is_reusable_from_cancelled_orders(): bool;
}
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
namespace Sylius\Behat\Page\Admin\Promotion;

use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface as BaseUpdatePageInterface;
interface Update_Page_Interface extends Base_Update_Page_Interface
{
    public function name_it(string $name): void;
    public function check_channels_state(string $channel_name): bool;
    public function is_code_disabled(): bool;
    public function has_starts_at(\DateTimeInterface $date_time): bool;
    public function has_ends_at(\DateTimeInterface $date_time): bool;
    public function is_coupon_management_available(): bool;
    public function manage_coupons(): void;
    public function has_any_rule(): bool;
    public function has_rule(string $name): bool;
    public function remove_action_field_value(string $channel_code, string $field): void;
    public function get_item_percentage_discount_action_value(string $channel_code): string;
    public function specify_order_percentage_discount_action_value(string $discount): void;
    public function get_order_percentage_discount_action_value(): string;
    public function remove_rule_amount(string $channel_code): void;
    public function get_action_validation_errors_count(string $channel_code): int;
    public function get_rule_validation_errors_count(string $channel_code): int;
}
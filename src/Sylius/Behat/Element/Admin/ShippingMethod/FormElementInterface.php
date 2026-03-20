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
namespace Sylius\Behat\Element\Admin\Shipping_Method;

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
interface Form_Element_Interface extends Base_Form_Element_Interface
{
    public function get_code(): string;
    public function set_code(string $code): void;
    public function is_code_disabled(): bool;
    public function get_name(string $locale_code = 'en_US');
    public function set_name(string $name, string $locale_code = 'en_US'): void;
    public function get_position(): int;
    public function set_position(int $position): void;
    public function get_description(string $locale_code = 'en_US'): string;
    public function set_description(string $description, string $locale_code = 'en_US'): void;
    public function get_zone_code(): string;
    public function set_zone_code(string $code): void;
    public function enable(): void;
    public function disable(): void;
    public function check_channel(string $channel_code): void;
    public function has_checked_channel(string $channel_code): bool;
    public function choose_calculator(string $calculator_name): void;
    public function set_calculator_configuration_amount_for_channel(string $channel_code, int $amount): void;
    public function add_rule(string $type): void;
    public function fill_last_rule_option(string $field_name, string $value): void;
    public function fill_last_rule_option_for_channel(string $channel_code, string $field_name, string $value): void;
    public function get_shipping_charges_validation_errors_count(string $channel_code): int;
    public function set_field(string $field, string $value): void;
}
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
namespace Sylius\Behat\Element\Admin\Promotion;

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
interface Form_Element_Interface extends Base_Form_Element_Interface
{
    public function set_priority(?int $priority): void;
    public function get_priority(): int;
    public function set_starts_at(\DateTimeInterface $date_time): void;
    public function set_ends_at(\DateTimeInterface $date_time): void;
    public function set_usage_limit(int $limit): void;
    public function make_exclusive(): void;
    public function make_not_applies_to_discounted_item(): void;
    public function make_coupon_based(): void;
    public function check_channel(string $name): void;
    public function set_label(string $label, string $locale_code): void;
    public function has_label(string $label, string $locale_code): bool;
    public function add_action(string $type): void;
    public function remove_last_action(): void;
    public function fill_action_option(string $option, string $value): void;
    public function fill_action_option_for_channel(string $channel_code, string $option, string $value): void;
    public function select_action_option(string $option, string $value, bool $multiple = false): void;
    public function add_rule(string $type): void;
    public function remove_last_rule(): void;
    public function select_rule_option(string $option, string $value, bool $multiple = false): void;
    public function fill_rule_option(string $option, string $value): void;
    public function fill_rule_option_for_channel(string $channel_code, string $option, string $value): void;
    public function select_autocomplete_rule_options(array $values, ?string $channel_code = null): void;
    public function select_autocomplete_action_filter_options(array $values, string $channel_code, string $filter_type): void;
    public function check_if_rule_configuration_form_is_visible(): bool;
    public function check_if_action_configuration_form_is_visible(): bool;
    public function get_validation_message_for_action(): string;
    public function get_validation_message_for_translation(string $element, string $locale_code): string;
}
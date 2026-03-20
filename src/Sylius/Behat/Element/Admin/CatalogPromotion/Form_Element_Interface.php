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
namespace Sylius\Behat\Element\Admin\Catalog_Promotion;

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
interface Form_Element_Interface extends Base_Form_Element_Interface
{
    public function name_it(string $name): void;
    public function label_it(string $label, string $locale_code): void;
    public function describe_it(string $description, string $locale_code): void;
    public function prioritize_it(int $priority): void;
    public function change_enable_to(bool $enabled): void;
    public function check_channel(string $channel_name): void;
    public function set_exclusiveness(bool $is_exclusive): void;
    public function uncheck_channel(string $channel_name): void;
    public function specify_start_date(\DateTimeInterface $start_date): void;
    public function specify_end_date(\DateTimeInterface $end_date): void;
    public function add_scope(string $type): void;
    public function add_action(string $type): void;
    public function select_scope_option(array $names): void;
    public function fill_action_option(string $option, string $value): void;
    public function fill_action_option_for_channel(string $channel_code, string $option, string $value): void;
    public function get_last_scope_names(): array;
    public function get_last_action_option(string $option): string;
    public function get_last_action_option_for_channel(string $channel_code, string $option): string;
    public function get_field_value_in_locale(string $field, string $locale_code): string;
    public function check_if_scope_configuration_form_is_visible(): bool;
    public function check_if_action_configuration_form_is_visible(): bool;
    public function get_validation_messages(): array;
    public function remove_scope_option(array $names): void;
    public function remove_last_action(): void;
    public function remove_last_scope(): void;
}
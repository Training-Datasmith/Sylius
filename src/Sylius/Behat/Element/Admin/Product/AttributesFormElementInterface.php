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
namespace Sylius\Behat\Element\Admin\Product;

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface;
interface Attributes_Form_Element_Interface extends Form_Element_Interface
{
    public function add_attribute(string $attribute_name): void;
    public function add_selected_attributes(): void;
    public function update_attribute(string $attribute_name, string $value, string $locale_code): void;
    public function remove_attribute(string $attribute_name): void;
    public function has_attribute(string $attribute_name): bool;
    public function get_number_of_attributes(): int;
    public function get_attribute_value(string $attribute_name, string $locale_code): string;
    public function get_attribute_select_text(string $attribute_name, string $locale_code): string;
    public function get_value_non_translatable_attribute(string $attribute_name): string;
    public function get_attribute_validation_errors(string $attribute_name, string $locale_code): string;
    public function has_attribute_error(string $attribute_name, string $locale_code): bool;
}
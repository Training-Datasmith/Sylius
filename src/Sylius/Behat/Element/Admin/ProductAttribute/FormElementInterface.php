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
namespace Sylius\Behat\Element\Admin\Product_Attribute;

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
interface Form_Element_Interface extends Base_Form_Element_Interface
{
    public function specify_code(string $code): void;
    public function is_code_disabled(): bool;
    public function name_it(string $name, string $language): void;
    public function change_name(string $name, string $language): void;
    public function disable_translatability(): void;
    public function is_type_disabled(): bool;
    public function has_attribute_value(string $value, string $locale_code): bool;
    public function add_attribute_value(string $value, string $locale_code): void;
    public function delete_attribute_value(string $value, string $locale_code): void;
    public function change_attribute_value(string $old_value, string $new_value, string $locale_code): void;
    public function check_multiple(): void;
    public function specify_min_value(int $min): void;
    public function specify_max_value(int $max): void;
}
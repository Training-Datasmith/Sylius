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
namespace Sylius\Behat\Element\Admin\Product_Option;

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
interface Form_Element_Interface extends Base_Form_Element_Interface
{
    public function specify_code(string $code): void;
    public function is_code_disabled(): bool;
    public function set_name(string $name, string $locale_code): void;
    public function add_option_value(string $code, string $locale_code, string $value): void;
    public function has_option_value(string $option_value, string $locale_code): bool;
    public function apply_to_all_option_values(string $code, string $locale_code): void;
}
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
namespace Sylius\Behat\Element\Admin\Exchange_Rate;

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
interface Form_Element_Interface extends Base_Form_Element_Interface
{
    public function is_field_disabled(string $field_name): bool;
    public function get_ratio(): string;
    public function has_form_validation_error(string $expected_message): bool;
    public function specify_ratio(string $ratio): void;
    public function specify_source_currency(string $source_currency): void;
    public function specify_target_currency(string $target_currency): void;
}
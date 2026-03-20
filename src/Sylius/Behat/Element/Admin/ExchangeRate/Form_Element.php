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

use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    public function is_field_disabled(string $field_name): bool
    {
        return null !== $this->get_element($field_name)->get_attribute('disabled');
    }
    public function get_ratio(): string
    {
        return $this->get_element('ratio')->get_value();
    }
    public function has_form_validation_error(string $expected_message): bool
    {
        return $expected_message === $this->get_validation_errors();
    }
    public function specify_ratio(string $ratio): void
    {
        $this->get_element('ratio')->set_value($ratio);
    }
    public function specify_source_currency(string $source_currency): void
    {
        $this->get_element('source_currency')->set_value($source_currency);
    }
    public function specify_target_currency(string $target_currency): void
    {
        $this->get_element('target_currency')->set_value($target_currency);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['ratio' => '[data-test-ratio]', 'source_currency' => '[data-test-source-currency]', 'target_currency' => '[data-test-target-currency]']);
    }
}
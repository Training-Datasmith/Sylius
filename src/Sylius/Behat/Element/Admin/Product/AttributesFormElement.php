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

use Behat\Mink\Session;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
class Attributes_Form_Element extends Base_Form_Element implements Attributes_Form_Element_Interface
{
    public function __construct(Session $session, $mink_parameters, protected readonly Autocomplete_Helper_Interface $autocomplete_helper)
    {
    }
    public function add_attribute(string $attribute_name): void
    {
        $this->change_tab();
        $this->select_attribute_to_be_added($attribute_name);
        $this->get_element('attribute_add_button')->click();
        $this->wait_for_form_update();
    }
    public function add_selected_attributes(): void
    {
        $this->change_tab();
        $this->get_element('attribute_add_button')->click();
        $this->wait_for_form_update();
    }
    public function update_attribute(string $attribute_name, string $value, string $locale_code): void
    {
        $this->change_tab();
        $this->change_attribute_tab($attribute_name);
        $attribute_value = $this->get_element('attribute_value', ['%attribute_name%' => $attribute_name, '%locale_code%' => $locale_code]);
        match ($attribute_value->get_tag_name()) {
            'input' => $attribute_value->set_value($value),
            'select' => $attribute_value->select_option($value),
            default => throw new \InvalidArgumentException('Unsupported attribute value type'),
        };
        $attribute_value->blur();
        $this->wait_for_form_update();
    }
    public function remove_attribute(string $attribute_name): void
    {
        $this->change_tab();
        $this->get_element('attribute_delete_button', ['%attribute_name%' => $attribute_name])->press();
        $this->wait_for_form_update();
    }
    public function has_attribute(string $attribute_name): bool
    {
        $this->change_tab();
        return $this->has_element('attribute_tab', ['%name%' => $attribute_name]);
    }
    public function get_number_of_attributes(): int
    {
        return count($this->get_document()->find_all('css', '[data-test-attribute-tab]'));
    }
    public function get_attribute_value(string $attribute_name, string $locale_code): string
    {
        $this->change_tab();
        $this->change_attribute_tab($attribute_name);
        $attribute_value = $this->get_element('attribute_value', ['%attribute_name%' => $attribute_name, '%locale_code%' => $locale_code]);
        return match ($attribute_value->get_tag_name()) {
            'input' => $attribute_value->get_value(),
            'select' => $attribute_value->get_text(),
            default => throw new \InvalidArgumentException('Unsupported attribute value type'),
        };
    }
    public function get_attribute_select_text(string $attribute_name, string $locale_code): string
    {
        $this->click_tab_if_its_not_active();
        return $this->get_element('attribute_select', ['%attribute_name%' => $attribute_name, '%locale_code%' => $locale_code])->get_text();
    }
    public function get_value_non_translatable_attribute(string $attribute_name): string
    {
        $this->change_tab();
        $this->change_attribute_tab($attribute_name);
        return $this->get_element('attribute_input', ['%name%' => $attribute_name])->get_value();
    }
    public function get_attribute_validation_errors(string $attribute_name, string $locale_code): string
    {
        $this->change_tab();
        $this->change_attribute_tab($attribute_name);
        return $this->get_validation_message('attribute_value', ['%attribute_name%' => $attribute_name, '%locale_code%' => $locale_code]);
    }
    public function has_attribute_error(string $attribute_name, string $locale_code): bool
    {
        $this->change_tab();
        $this->change_attribute_tab($attribute_name);
        $attribute_value = $this->get_element('attribute_value', ['%attribute_name%' => $attribute_name, '%locale_code%' => $locale_code]);
        return $attribute_value->has_class('is-invalid');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['attribute_add_button' => '[data-test-attribute-add-button]', 'attribute_autocomplete' => '[data-test-attribute-autocomplete] input[name="product_attributes"]', 'attribute_delete_button' => '[data-test-attribute-delete-button="%attribute_name%"]', 'attribute_input' => '[data-test-attribute-name="%name%"]', 'attribute_tab' => '[data-test-attribute-tab="%name%"]', 'attribute_value' => '[data-test-attribute-value][data-test-locale-code="%locale_code%"][data-test-attribute-name="%attribute_name%"]', 'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]']);
    }
    protected function select_attribute_to_be_added(string $attribute_name): void
    {
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('attribute_autocomplete')->get_xpath(), $attribute_name);
    }
    protected function click_tab_if_its_not_active(): void
    {
        $attributes_tab = $this->get_element('tab', ['%name%' => 'attributes']);
        if (!$attributes_tab->has_class('active')) {
            $attributes_tab->click();
        }
    }
    protected function change_tab(): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $this->get_element('side_navigation_tab', ['%name%' => 'attributes'])->click();
    }
    protected function change_attribute_tab(string $attribute_name): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $this->get_element('attribute_tab', ['%name%' => $attribute_name])->click();
    }
}
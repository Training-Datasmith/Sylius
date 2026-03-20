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

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    use Specifies_Its_Field;
    use Checks_Code_Immutability;
    public function set_name(string $name, string $locale_code): void
    {
        $this->get_element('name', ['%locale_code%' => $locale_code])->set_value($name);
    }
    public function remove_option_value(string $option_value): void
    {
        $option_values = $this->get_document()->find_all('css', '[data-test-option-value]');
        foreach ($option_values as $option_value_element) {
            if ($option_value_element->has('css', sprintf('input[value="%s"]', $option_value))) {
                $option_value_element->find('css', '[data-test-delete-option-value]')->click();
                $this->wait_for_form_update();
            }
        }
    }
    public function add_option_value(string $code, string $locale_code, string $value): void
    {
        $this->get_element('add_option_value')->press();
        $this->wait_for_form_update();
        $last_value = $this->get_element('last_option_value');
        $last_value->find('css', '[data-test-code]')->set_value($code);
        $last_value->find('css', sprintf('[id$="_translations_%s_value"]', $locale_code))->set_value($value);
        $this->wait_for_form_update();
    }
    public function has_option_value(string $option_value, string $locale_code): bool
    {
        return $this->has_element('option_value', ['%option_value%' => $option_value, '%locale_code%' => $locale_code]);
    }
    public function apply_to_all_option_values(string $code, string $locale_code): void
    {
        $this->get_element('apply_to_all', ['%value_code%' => $code, '%locale_code%' => $locale_code])->click();
        $this->wait_for_form_update();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['add_option_value' => '[data-test-add-option-value]', 'apply_to_all' => '[data-test-option-value="%value_code%"] [data-test-option-value-locale="%locale_code%"] [data-test-apply-to-all]', 'code' => '[data-test-code]', 'delete_option_value' => '[data-test-delete-option-value]', 'form' => '[data-live-name-value="sylius_admin:product_option:form"]', 'last_option_value' => '[data-test-option-values] [data-test-option-value]:last-child', 'name' => '#sylius_admin_product_option_translations_%locale_code%_name', 'option_value' => '[data-test-option-values] input[id$="_translations_%locale_code%_value"][value="%option_value%"]']);
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
}
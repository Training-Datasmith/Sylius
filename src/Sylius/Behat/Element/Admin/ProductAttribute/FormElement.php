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

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    use Checks_Code_Immutability;
    use Specifies_Its_Field;
    public function change_name(string $name, string $language): void
    {
        $this->get_document()->fill_field(sprintf('sylius_admin_product_attribute_translations_%s_name', $language), $name);
    }
    public function is_type_disabled(): bool
    {
        return 'disabled' === $this->get_element('type')->get_attribute('disabled');
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    public function change_attribute_value(string $old_value, string $new_value, string $locale_code = 'en_US'): void
    {
        $this->get_element('choice_direct_input', ['%value%' => $old_value, '%locale_code%' => $locale_code])->set_value($new_value);
    }
    public function has_attribute_value(string $value, string $locale_code = 'en_US'): bool
    {
        return $this->has_element('choice_direct_input', ['%value%' => $value, '%locale_code%' => $locale_code]);
    }
    public function add_attribute_value(string $value, string $locale_code): void
    {
        $this->get_element('add_button')->click();
        $this->wait_for_form_update();
        $last_choice = $this->get_last_choice_element();
        $last_choice->find('css', 'input[data-test-locale="' . $locale_code . '"]')->set_value($value);
    }
    public function delete_attribute_value(string $value, string $locale_code): void
    {
        $input = $this->get_element('choice_direct_input', ['%value%' => $value, '%locale_code%' => $locale_code]);
        $this->get_element('delete_button', ['%key%' => $input->get_attribute('data-test-key')])->click();
        $this->wait_for_form_update();
    }
    public function name_it(string $name, string $language): void
    {
        $this->get_document()->fill_field(sprintf('sylius_admin_product_attribute_translations_%s_name', $language), $name);
    }
    public function disable_translatability(): void
    {
        $this->get_element('translatable')->uncheck();
    }
    public function specify_min_value(int $min): void
    {
        $this->get_element('min')->set_value($min);
    }
    public function specify_max_value(int $max): void
    {
        $this->get_element('max')->set_value($max);
    }
    public function check_multiple(): void
    {
        $this->get_element('multiple')->click();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['add_button' => '#sylius_admin_product_attribute_configuration_choices_add', 'choice' => '[data-test-choice-key="%key%"] input[data-test-locale="%locale_code%"]', 'choice_direct_input' => 'input[value="%value%"][data-test-locale="%locale_code%"]', 'choices' => '[data-test-choice-key]', 'code' => '[data-test-code]', 'delete_button' => '[data-test-choice-removal="%key%"]', 'form' => 'form[name="sylius_admin_product_attribute"]', 'max' => '#sylius_admin_product_attribute_configuration_max', 'min' => '#sylius_admin_product_attribute_configuration_min', 'multiple' => 'label[for=sylius_admin_product_attribute_configuration_multiple]', 'name' => '[data-test-name]', 'translatable' => '[data-test-translatable]', 'type' => '[data-test-type]']);
    }
    protected function get_last_choice_element(): Node_Element
    {
        $choices = $this->get_document()->find_all('css', '[data-test-choice-key]');
        if (empty($choices)) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Last choice element', 'css', '[data-test-choice-key]');
        }
        return end($choices);
    }
}
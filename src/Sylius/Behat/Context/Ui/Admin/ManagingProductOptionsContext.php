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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Admin\Product_Option\Form_Element_Interface;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Product\Model\Product_Option_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Product_Options_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page, private Create_Page_Interface $create_page, private Update_Page_Interface $update_page, private Form_Element_Interface $form_element, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I want to create a new product option')]
    public function i_want_to_create_a_new_product_option(): void
    {
        $this->create_page->open();
    }
    #[When('I want to modify the :productOption product option')]
    public function i_want_to_modify_a_product_option(Product_Option_Interface $product_option): void
    {
        if (!$this->update_page->is_open(['id' => $product_option->get_id()])) {
            $this->update_page->open(['id' => $product_option->get_id()]);
        }
    }
    #[When('I specify a too long :field')]
    public function i_specify_a_too_long(string $field): void
    {
        $this->form_element->specify_field(ucwords($field), str_repeat('a', 256));
    }
    #[Given('I am browsing product options')]
    #[When('I browse product options')]
    public function i_browse_product_options(): void
    {
        $this->index_page->open();
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I name it :name in :language')]
    public function i_name_it_in_language(string $name, string $language): void
    {
        $this->form_element->set_name($name, $language);
    }
    #[When('I rename it to :name in :language')]
    #[When('I remove its name from :language translation')]
    public function i_rename_it_to_in_language(string $language, ?string $name = null): void
    {
        $this->form_element->set_name($name ?? '', $language);
    }
    #[When('I do not name it')]
    public function i_do_not_name_it(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->form_element->specify_code($code ?? '');
    }
    #[When('I add the :value option value identified by :code')]
    #[When('I add the :value option value identified by :code in :localeCode')]
    public function i_add_the_option_value_with_code_and_value(string $value, string $code, string $locale_code = 'en_US'): void
    {
        $this->form_element->add_option_value($code, $locale_code, $value);
    }
    #[When('I apply the option value identified by :code in :localeCode to all option values.')]
    public function i_apply_to_all_the_option_value_identified_by(string $code, string $locale_code): void
    {
        $this->form_element->apply_to_all_option_values($code, $locale_code);
    }
    #[When('I delete the :value option value of this product option')]
    public function i_delete_the_option_value_with_code_and_value(string $value): void
    {
        $this->form_element->remove_option_value($value);
    }
    #[When('I check (also) the :productOptionName product option')]
    public function i_check_the_product_option(string $product_option_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $product_option_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[Then('I should see the product option :productOption in the list')]
    #[Then('the product option :productOption should appear in the registry')]
    #[Then('the product option :productOption should be in the registry')]
    public function the_product_option_should_appear_in_the_registry(Product_Option_Interface $product_option): void
    {
        $this->shared_storage->set('product_option', $product_option);
        $this->i_browse_product_options();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $product_option->get_name()]));
    }
    #[Then('I should be notified that product option with this code already exists')]
    public function i_should_be_notified_that_product_option_with_this_code_already_exists(): void
    {
        Assert::same($this->form_element->get_validation_message('code'), 'The option with given code already exists.');
    }
    #[Then('there should still be only one product option with :element :value')]
    public function there_should_still_be_only_one_product_option_with(string $element, string $value): void
    {
        $this->i_browse_product_options();
        Assert::true($this->index_page->is_single_resource_on_page([$element => $value]));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        Assert::same($this->form_element->get_validation_message($element, ['%locale_code%' => 'en_US']), sprintf('Please enter option %s.', $element));
    }
    #[Then('the product option with :element :value should not be added')]
    public function the_product_option_with_element_value_should_not_be_added(string $element, string $value): void
    {
        $this->i_browse_product_options();
        Assert::false($this->index_page->is_single_resource_on_page([$element => $value]));
    }
    #[Then('/^(this product option) should still be named "([^"]+)"$/')]
    #[Then('/^(this product option) name should be "([^"]+)"$/')]
    public function this_product_option_name_should_still_be(Product_Option_Interface $product_option, string $product_option_name): void
    {
        $this->i_browse_product_options();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $product_option->get_code(), 'name' => $product_option_name]));
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->form_element->is_code_disabled());
    }
    #[When('I do not add an option value')]
    public function i_do_not_add_an_option_value(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[Then('I should see a single product option in the list')]
    #[Then('I should see :amount product options in the list')]
    public function i_should_see_product_options_in_the_list(int $amount = 1): void
    {
        Assert::same($this->index_page->count_items(), $amount);
    }
    #[Then('/^(this product option) should have the "([^"]*)" option value$/')]
    #[Then('/^(product option "[^"]+") should have the "([^"]*)" option value$/')]
    #[Then('/^(product option "[^"]+") should still have the "([^"]*)" option value$/')]
    #[Then('/^(this product option) should have the "([^"]*)" option value in ("([^"]+)" locale)$/')]
    public function this_product_option_should_have_the_option_value(Product_Option_Interface $product_option, string $option_value, string $locale_code = 'en_US'): void
    {
        $this->i_want_to_modify_a_product_option($product_option);
        Assert::true($this->form_element->has_option_value($option_value, $locale_code));
    }
    #[Then('/^(this product option) should not have the "([^"]*)" option value$/')]
    #[Then('/^(this product option) should not have the "([^"]*)" option value in ("([^"]+)" locale)$/')]
    public function this_product_option_should_not_have_the_option_value(Product_Option_Interface $product_option, string $option_value, string $locale_code = 'en_US'): void
    {
        $this->i_want_to_modify_a_product_option($product_option);
        Assert::false($this->form_element->has_option_value($option_value, $locale_code));
    }
    #[Then('the first product option in the list should have :field :value')]
    public function the_first_product_option_in_the_list_should_have(string $field, string $value): void
    {
        Assert::same($this->index_page->get_column_fields($field)[0], $value);
    }
    #[Then('the last product option in the list should have :field :value')]
    public function the_last_product_option_in_the_list_should_have(string $field, string $value): void
    {
        $values = $this->index_page->get_column_fields($field);
        Assert::same(end($values), $value);
    }
    #[Then('I should be notified that :field is too long')]
    #[Then('I should be notified that :field should be no longer than :maxLength characters')]
    public function i_should_be_notified_that_field_value_is_too_long(string $field, int $max_length = 255): void
    {
        $validation_message = $this->form_element->get_validation_message(String_Inflector::name_to_lowercase_code($field));
        Assert::contains($validation_message, sprintf('must not be longer than %d characters.', $max_length));
    }
}
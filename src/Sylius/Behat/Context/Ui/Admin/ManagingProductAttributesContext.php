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
use Sylius\Behat\Element\Admin\Product_Attribute\Filter_Element_Interface;
use Sylius\Behat\Element\Admin\Product_Attribute\Form_Element_Interface;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Component\Product\Model\Product_Attribute_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Product_Attributes_Context implements Context
{
    public function __construct(private Create_Page_Interface $create_page, private Index_Page_Interface $index_page, private Update_Page_Interface $update_page, private Form_Element_Interface $form_element, private Filter_Element_Interface $filter_element)
    {
    }
    #[When('I want to create a new :type product attribute')]
    public function i_want_to_create_a_new_text_product_attribute(string $type): void
    {
        $this->create_page->open(['type' => $type]);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->form_element->specify_code($code ?? '');
    }
    #[When('I name it :name in :localeCode')]
    public function i_specify_its_name_as(string $name, string $locale_code): void
    {
        $this->form_element->name_it($name, $locale_code);
    }
    #[When('I disable its translatability')]
    public function i_disable_its_translatability(): void
    {
        $this->form_element->disable_translatability();
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I( also) add value :value in :localeCode')]
    public function i_add_value(string $value, string $locale_code): void
    {
        $this->form_element->add_attribute_value($value, $locale_code);
    }
    #[When('I delete value :value')]
    public function i_delete_value(string $value, string $locale_code = 'en_US'): void
    {
        $this->form_element->delete_attribute_value($value, $locale_code);
    }
    #[When('I change its value :oldValue to :newValue')]
    public function i_change_its_value_to(string $old_value, string $new_value): void
    {
        $this->form_element->change_attribute_value($old_value, $new_value, 'en_US');
    }
    #[When('I choose :type in the type filter')]
    #[When('I choose :firstType and :secondType in the type filter')]
    public function i_choose_in_the_type_filter(string ...$types): void
    {
        foreach ($types as $type) {
            $this->filter_element->choose_type($type);
        }
    }
    #[When('I choose :translatable in the translatable filter')]
    public function i_choose_in_the_translatable_filter(string $translatable): void
    {
        $this->filter_element->choose_translatable(ucfirst($translatable));
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->filter_element->filter();
    }
    #[Then('/^I should(?:| also) see the product attribute "([^"]+)" in the list$/')]
    public function i_should_see_the_product_attribute_in_the_list(string $name): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $name]));
    }
    #[Then('the :type attribute :name should appear in the store')]
    #[Then('the :type attribute :name should still be in the store')]
    public function the_attribute_should_appear_in_the_store(string $type, string $name): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_with_specific_element_on_page(['name' => $name], sprintf('td span.ui.label:contains("%s")', ucfirst($type))));
    }
    #[When('/^I want to edit (this product attribute)$/')]
    public function i_want_to_edit_this_attribute(Product_Attribute_Interface $product_attribute): void
    {
        $this->update_page->open(['id' => $product_attribute->get_id()]);
    }
    #[When('I change its name to :name in :localeCode')]
    public function i_change_it_name_to_in(string $name, string $locale_code): void
    {
        $this->form_element->change_name($name, $locale_code);
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->form_element->is_code_disabled());
    }
    #[Then('the type field should be disabled')]
    #[Then('I should not be able to edit its type')]
    public function the_type_field_should_be_disabled(): void
    {
        Assert::true($this->form_element->is_type_disabled());
    }
    #[Then('I should be notified that product attribute with this code already exists')]
    public function i_should_be_notified_that_product_attribute_with_this_code_already_exists(): void
    {
        Assert::same($this->form_element->get_validation_message('code'), 'This code is already in use.');
    }
    #[Then('there should still be only one product attribute with code :code')]
    public function there_should_still_be_only_one_product_attribute_with_code(string $code): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $code]));
    }
    #[When('I do not name it')]
    public function i_do_not_name_it(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        $this->assert_field_validation_message($element, sprintf('Please enter attribute %s.', $element));
    }
    #[Given('the attribute with :elementName :elementValue should not appear in the store')]
    public function the_attribute_with_code_should_not_appear_in_the_store(string $element_name, string $element_value): void
    {
        $this->index_page->open();
        Assert::false($this->index_page->is_single_resource_on_page([$element_name => $element_value]));
    }
    #[When('I remove its name from :localeCode translation')]
    public function i_remove_its_name_from_translation(string $locale_code): void
    {
        $this->form_element->change_name('', $locale_code);
    }
    #[Given('I am browsing product attributes')]
    #[When('I browse product attributes')]
    #[When('I want to see all product attributes in store')]
    public function i_want_to_see_all_product_attributes_in_store(): void
    {
        $this->index_page->open();
    }
    #[When('I specify its min length as :min')]
    #[When('I specify its min entries value as :min')]
    public function i_specify_its_min_value_as(int $min): void
    {
        $this->form_element->specify_min_value($min);
    }
    #[When('I specify its max length as :max')]
    #[When('I specify its max entries value as :max')]
    public function i_specify_its_max_length_as(int $max): void
    {
        $this->form_element->specify_max_value($max);
    }
    #[When('I check multiple option')]
    public function i_check_multiple_option(): void
    {
        $this->form_element->check_multiple();
    }
    #[When('I do not check multiple option')]
    public function i_do_not_check_multiple_option(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('I check (also) the :productAttributeName product attribute')]
    public function i_check_the_product_attribute(string $product_attribute_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $product_attribute_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[Then('I should see a single product attribute in the list')]
    #[Then('I should see :amountOfProductAttributes product attributes in the list')]
    public function i_should_see_customers_in_the_list(int $amount_of_product_attributes = 1): void
    {
        Assert::same($this->index_page->count_items(), $amount_of_product_attributes);
    }
    #[When('/^I(?:| try to) delete (this product attribute)$/')]
    public function i_delete_this_product_attribute(Product_Attribute_Interface $product_attribute): void
    {
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['code' => $product_attribute->get_code(), 'name' => $product_attribute->get_name()]);
    }
    #[Then('/^(this product attribute) should no longer exist in the registry$/')]
    public function this_product_attribute_should_no_longer_exist_in_the_registry(Product_Attribute_Interface $product_attribute): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['code' => $product_attribute->get_code()]));
    }
    #[Then('the first product attribute on the list should have name :name')]
    public function the_first_product_attribute_on_the_list_should_have(string $name): void
    {
        $names = $this->index_page->get_column_fields('name');
        Assert::same(reset($names), $name);
    }
    #[Then('the last product attribute on the list should have name :name')]
    public function the_last_product_attribute_on_the_list_should_have(string $name): void
    {
        $names = $this->index_page->get_column_fields('name');
        Assert::same(end($names), $name);
    }
    #[Then('I should see the value :value in :localeCode locale')]
    public function i_should_see_the_value(string $value, string $locale_code): void
    {
        Assert::true($this->form_element->has_attribute_value($value, $locale_code));
    }
    #[Then('I should not see the value :value in :localeCode locale')]
    public function i_should_not_see_the_value(string $value, string $locale_code): void
    {
        Assert::false($this->form_element->has_attribute_value($value, $locale_code));
    }
    #[Then('/^(this product attribute) should have value "([^"]*)"/')]
    #[Then('/^the ("[^"]+" product attribute) should(?:| also) have value "([^"]+)"/')]
    public function the_select_attribute_should_have_value(Product_Attribute_Interface $product_attribute, string $value): void
    {
        $this->i_want_to_edit_this_attribute($product_attribute);
        Assert::true($this->form_element->has_attribute_value($value, 'en_US'));
    }
    #[Then('I should be notified that max length must be greater or equal to the min length')]
    public function i_should_be_notified_that_max_length_must_be_greater_or_equal_to_the_min_length(): void
    {
        Assert::same($this->form_element->get_validation_errors(), 'Configuration max length must be greater or equal to the min length.');
    }
    #[Then('I should be notified that max entries value must be greater or equal to the min entries value')]
    public function i_should_be_notified_that_max_entries_value_must_be_greater_or_equal_to_the_min_entries_value(): void
    {
        Assert::same($this->form_element->get_validation_errors(), 'Configuration max entries value must be greater or equal to the min entries value.');
    }
    #[Then('I should be notified that min entries value must be lower or equal to the number of added choices')]
    public function i_should_be_notified_that_min_entries_value_must_be_lower_or_equal_to_the_number_of_added_choices(): void
    {
        Assert::same($this->form_element->get_validation_errors(), 'Configuration min entries value must be lower or equal to the number of added choices.');
    }
    #[Then('I should be notified that multiple must be true if min or max entries values are specified')]
    public function i_should_be_notified_that_multiple_must_be_true_if_min_or_max_entries_values_are_specified(): void
    {
        Assert::same($this->form_element->get_validation_errors(), 'Configuration multiple must be true if min or max entries values are specified.');
    }
    #[Then('/^(this product attribute) should not have value "([^"]*)"/')]
    public function the_select_attribute_should_not_have_value(Product_Attribute_Interface $product_attribute, string $value): void
    {
        $this->i_want_to_edit_this_attribute($product_attribute);
        Assert::false($this->form_element->has_attribute_value($value, 'en_US'));
    }
    private function assert_field_validation_message(string $element, string $expected_message): void
    {
        Assert::same($this->form_element->get_validation_message($element), $expected_message);
    }
}
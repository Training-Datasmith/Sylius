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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Admin\Product_Association_Type\Form_Element_Interface;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Behat\Page\Admin\Product_Association_Type\Index_Page_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Sylius\Component\Product\Model\Product_Association_Type_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Product_Association_Types_Context implements Context
{
    public function __construct(private Create_Page_Interface $create_page, private Index_Page_Interface $index_page, private Update_Page_Interface $update_page, private Form_Element_Interface $form_element, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I browse product association types')]
    #[When('I am browsing product association types')]
    #[When('I want to browse product association types')]
    public function i_want_to_browse_product_association_types(): void
    {
        $this->index_page->open();
    }
    #[When('I want to create a new product association type')]
    public function i_want_to_create_a_new_product_association_type(): void
    {
        $this->create_page->open();
    }
    #[When('I want to modify the :productAssociationType product association type')]
    public function i_want_to_modify_a_payment_method(Product_Association_Type_Interface $product_association_type): void
    {
        $this->update_page->open(['id' => $product_association_type->get_id()]);
    }
    #[When('I name it :name in :language')]
    public function i_name_it_in(string $name, string $language): void
    {
        $this->form_element->set_name($name, $language);
    }
    #[When('I do not name it')]
    public function i_do_not_name_it(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('I rename it to :name in :language')]
    #[When('I remove its name from :language translation')]
    public function i_rename_it_to_in_language(string $language, string $name = ''): void
    {
        $this->form_element->set_name($name, $language);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(string $code = ''): void
    {
        $this->form_element->set_code($code);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I delete the :productAssociationType product association type')]
    public function i_delete_the_product_association_type(Product_Association_Type_Interface $product_association_type): void
    {
        $this->i_want_to_browse_product_association_types();
        $this->index_page->delete_resource_on_page(['code' => $product_association_type->get_code(), 'name' => $product_association_type->get_name()]);
    }
    #[When('I check (also) the :productAssociationTypeName product association type')]
    public function i_check_the_product_association_type(string $product_association_type_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $product_association_type_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[When('/^I filter product association types with (code|name) containing "([^"]+)"/')]
    public function i_filter_product_association_types_with_field_containing(string $field, string $value): void
    {
        $this->index_page->specify_filter_type($field, 'Contains');
        $this->index_page->specify_filter_value($field, $value);
        $this->index_page->filter();
    }
    #[When('I sort the product associations :sortType by :field')]
    public function i_sort_product_associations_by(string $sorting_order, string $field): void
    {
        $this->index_page->sort_by($field, $sorting_order === 'descending' ? 'desc' : 'asc');
    }
    #[Then('I should see a single product association type in the list')]
    #[Then('I should see only one product association type in the list')]
    #[Then('I should see :amount product association types in the list')]
    public function i_should_see_product_association_types_in_the_list(int $amount = 1): void
    {
        Assert::same($this->index_page->count_items(), $amount);
    }
    #[Then('I should see the product association type :name in the list')]
    public function i_should_see_the_product_association_type_in_the_list(string $name): void
    {
        $this->i_want_to_browse_product_association_types();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $name]));
    }
    #[Then('the product association type :productAssociationType should appear in the store')]
    public function the_product_association_type_should_appear_in_the_store(Product_Association_Type_Interface $product_association_type): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $product_association_type->get_name()]));
    }
    #[Then('/^(this product association type) name should be "([^"]+)"$/')]
    #[Then('/^(this product association type) should still be named "([^"]+)"$/')]
    public function this_product_association_type_name_should_be(Product_Association_Type_Interface $product_association_type, string $product_association_type_name): void
    {
        $this->i_want_to_browse_product_association_types();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $product_association_type->get_code(), 'name' => $product_association_type_name]));
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->form_element->is_code_disabled());
    }
    #[Then('/^(this product association type) should no longer exist in the registry$/')]
    public function this_product_association_type_should_no_longer_exist_in_the_registry(Product_Association_Type_Interface $product_association_type): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['code' => $product_association_type->get_code(), 'name' => $product_association_type->get_name()]));
    }
    #[Then('I should be notified that product association type with this code already exists')]
    public function i_should_be_notified_that_product_association_type_with_this_code_already_exists(): void
    {
        Assert::same($this->form_element->get_validation_message('code'), 'The association type with given code already exists.');
    }
    #[Then('there should still be only one product association type with a :element :code')]
    public function there_should_still_be_only_one_product_association_type_with(string $element, string $code): void
    {
        $this->i_want_to_browse_product_association_types();
        Assert::true($this->index_page->is_single_resource_on_page([$element => $code]));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        /** @var LocaleInterface $locale */
        $locale = $this->shared_storage->get('locale');
        Assert::same($this->form_element->get_validation_message($element, ['%locale%' => $locale->get_code()]), sprintf('Please enter association type %s.', $element));
    }
    #[Then('the product association type with :element :value should not be added')]
    public function the_product_association_type_with_element_value_should_not_be_added(string $element, string $value): void
    {
        $this->i_want_to_browse_product_association_types();
        Assert::false($this->index_page->is_single_resource_on_page([$element => $value]));
    }
    #[Then('the first product association on the list should have :field :value')]
    public function the_first_product_association_on_the_list_should_have(string $field, string $value): void
    {
        $fields = $this->index_page->get_column_fields($field);
        Assert::same(reset($fields), $value);
    }
    #[Then('the last product association on the list should have :field :value')]
    public function the_last_product_association_on_the_list_should_have(string $field, string $value): void
    {
        $fields = $this->index_page->get_column_fields($field);
        Assert::same(end($fields), $value);
    }
}
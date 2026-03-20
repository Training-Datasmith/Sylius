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
use Sylius\Behat\Element\Admin\Tax_Category\Form_Element_Interface;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Component\Taxation\Model\Tax_Category_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Tax_Categories_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page, private Create_Page_Interface $create_page, private Update_Page_Interface $update_page, private Form_Element_Interface $form_element)
    {
    }
    #[When('I want to create a new tax category')]
    public function i_want_to_create_new_tax_category(): void
    {
        $this->create_page->open();
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->form_element->set_code($code ?? '');
    }
    #[When('I specify a too long :field')]
    public function i_specify_a_too_long_code(string $field): void
    {
        $this->form_element->fill_element(str_repeat('a', 256), $field);
    }
    #[When('I name it :name')]
    #[When('I rename it to :name')]
    #[When('I do not name it')]
    #[When('I remove its name')]
    public function i_name_it($name = null): void
    {
        $this->form_element->set_name($name ?? '');
    }
    #[When('I describe it as :description')]
    public function i_describe_it_as(string $description): void
    {
        $this->form_element->set_description($description);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I want to modify a tax category :taxCategory')]
    #[When('/^I want to modify (this tax category)$/')]
    public function i_want_to_modify_tax_category(Tax_Category_Interface $tax_category): void
    {
        $this->update_page->open(['id' => $tax_category->get_id()]);
    }
    #[Given('I am browsing tax categories')]
    #[When('I browse tax categories')]
    public function i_want_to_browse_tax_categories(): void
    {
        $this->index_page->open();
    }
    #[When('I check (also) the :taxCategoryName tax category')]
    public function i_check_the_tax_category(string $tax_category_name): void
    {
        $this->index_page->check_resource_on_page(['nameAndDescription' => $tax_category_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[When('I delete tax category :taxCategory')]
    public function i_deleted_tax_category(Tax_Category_Interface $tax_category): void
    {
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['code' => $tax_category->get_code()]);
    }
    #[Then('/^(this tax category) should no longer exist in the registry$/')]
    public function this_tax_category_should_no_longer_exist_in_the_registry(Tax_Category_Interface $tax_category): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['code' => $tax_category->get_code()]));
    }
    #[Then('I should see the tax category :taxCategoryName in the list')]
    #[Then('the tax category :taxCategoryName should appear in the registry')]
    public function the_tax_category_should_appear_in_the_registry(string $tax_category_name): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['nameAndDescription' => $tax_category_name]));
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->form_element->is_code_disabled());
    }
    #[Then('/^(this tax category) name should be "([^"]+)"$/')]
    #[Then('/^(this tax category) should still be named "([^"]+)"$/')]
    public function this_tax_category_name_should_be(Tax_Category_Interface $tax_category, $tax_category_name): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $tax_category->get_code(), 'nameAndDescription' => $tax_category_name]));
    }
    #[Then('I should be notified that tax category with this code already exists')]
    public function i_should_be_notified_that_tax_category_with_this_code_already_exists(): void
    {
        Assert::same($this->form_element->get_validation_message('code'), 'The tax category with given code already exists.');
    }
    #[Then('there should still be only one tax category with :element :code')]
    public function there_should_still_be_only_one_tax_category_with($element, $code): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page([$element => $code]));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        Assert::same($this->form_element->get_validation_message($element), sprintf('Please enter tax category %s.', $element));
    }
    #[Then('tax category with :element :name should not be added')]
    public function tax_category_with_element_value_should_not_be_added($element, $name): void
    {
        $this->index_page->open();
        Assert::false($this->index_page->is_single_resource_on_page([$element => $name]));
    }
    #[Then('I should see a single tax category in the list')]
    #[Then('I should see :amount tax categories in the list')]
    public function i_should_see_tax_categories_in_the_list(int $amount = 1): void
    {
        Assert::same($this->index_page->count_items(), $amount);
    }
    #[Then('I should see the tax category :taxCategoryName')]
    public function i_should_see_the_tax_category(string $tax_category_name): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['nameAndDescription' => $tax_category_name]));
    }
    #[Then('I should not see the tax category :taxCategoryName')]
    public function i_should_not_see_the_tax_category(string $tax_category_name): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['nameAndDescription' => $tax_category_name]));
    }
    #[Then('I should be notified that :field is too long')]
    public function i_should_be_notified_that_is_too_long(string $field): void
    {
        Assert::contains($this->form_element->get_validation_message($field), 'must not be longer than 255 characters.');
    }
}
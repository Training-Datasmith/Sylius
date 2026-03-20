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
use Sylius\Behat\Context\Ui\Admin\Helper\Validation_Trait;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Shipping_Category\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Shipping_Category\Update_Page_Interface;
use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Component\Shipping\Model\Shipping_Category_Interface;
use Webmozart\Assert\Assert;
class Managing_Shipping_Categories_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Index_Page_Interface $index_page, private Create_Page_Interface $create_page, private Update_Page_Interface $update_page)
    {
    }
    #[When('I want to create a new shipping category')]
    public function i_want_to_create_a_new_shipping_category(): void
    {
        $this->create_page->open();
    }
    #[When('/^I browse shipping categories$/')]
    public function i_want_to_browse_shipping_categories(): void
    {
        $this->index_page->open();
    }
    #[Then('I should see a single shipping category in the list')]
    #[Then('I should see :numberOfShippingCategories shipping categories in the list')]
    public function i_should_see_shipping_categories_in_the_list(int $number_of_shipping_categories = 1): void
    {
        Assert::same($this->index_page->count_items(), $number_of_shipping_categories);
    }
    #[When('I specify its description as :shippingCategoryDescription')]
    public function i_specify_its_description_as(string $shipping_category_description): void
    {
        $this->create_page->specify_description($shipping_category_description);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_code_is_required(string $element): void
    {
        Assert::same($this->update_page->get_validation_message($element), sprintf('Please enter shipping category %s.', $element));
    }
    #[When('I do not specify its code')]
    #[When('I specify its code as :code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->create_page->specify_code($code ?? '');
    }
    #[When('I name it :shippingCategoryName')]
    #[When('I do not specify its name')]
    public function i_name_it($shipping_category_name = null): void
    {
        $this->create_page->name_it($shipping_category_name ?? '');
    }
    #[Then('I should see the shipping category :shippingCategoryName in the list')]
    public function i_should_see_the_shipping_category_in_the_list(string $shipping_category_name): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $shipping_category_name]));
    }
    #[Then('/^the (shipping category "([^"]+)") should be in the registry$/')]
    #[Then('/^the (shipping category "([^"]+)") should appear in the registry$/')]
    public function the_shipping_category_should_appear_in_the_registry(Shipping_Category_Interface $shipping_category): void
    {
        $this->i_want_to_browse_shipping_categories();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $shipping_category->get_code()]));
    }
    #[When('I delete shipping category :shippingCategoryName')]
    public function i_delete_shipping_category(string $shipping_category_name): void
    {
        $this->i_want_to_browse_shipping_categories();
        $this->index_page->delete_resource_on_page(['name' => $shipping_category_name]);
    }
    #[Then('/^(this shipping category) should no longer exist in the registry$/')]
    public function this_shipping_category_should_no_longer_exist_in_the_registry(Shipping_Category_Interface $shipping_category): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['code' => $shipping_category->get_code()]));
    }
    #[Then('shipping category with name :shippingCategoryName should not be added')]
    public function shipping_category_with_name_should_not_be_added(string $shipping_category_name): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['name' => $shipping_category_name]));
    }
    #[When('/^I modify a (shipping category "([^"]+)")$/')]
    #[When('/^I want to modify a (shipping category "([^"]+)")$/')]
    public function i_want_to_modify_a_shipping_category(Shipping_Category_Interface $shipping_category): void
    {
        $this->update_page->open(['id' => $shipping_category->get_id()]);
    }
    #[When('I rename it to :name')]
    public function i_name_it_in(string $name): void
    {
        $this->create_page->name_it($name ?? '');
    }
    #[When('I check (also) the :shippingCategoryName shipping category')]
    public function i_check_the_shipping_category(string $shipping_category_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $shipping_category_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->update_page->is_code_disabled(), 'Shipping category code should be disabled');
    }
    #[Then('this shipping category name should be :shippingCategoryName')]
    public function this_shipping_category_name_should_be(string $shipping_category_name): void
    {
        Assert::true($this->update_page->has_resource_values(['name' => $shipping_category_name]));
    }
    #[Then('I should be notified that shipping category with this code already exists')]
    public function i_should_be_notified_that_shipping_category_with_this_code_already_exists(): void
    {
        Assert::same($this->create_page->get_validation_message('code'), 'The shipping category with given code already exists.');
    }
    #[Then('there should still be only one shipping category with code :code')]
    public function there_should_still_be_only_one_shipping_category_with(string $code): void
    {
        $this->i_want_to_browse_shipping_categories();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $code]));
    }
    protected function resolve_current_page(): Sylius_Page_Interface
    {
        return $this->create_page;
    }
}
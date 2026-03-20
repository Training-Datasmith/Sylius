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
use Sylius\Behat\Context\Ui\Admin\Helper\Product_Type_Enum;
use Sylius\Behat\Page\Admin\Product\Create_Configurable_Product_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Create_Simple_Product_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Show_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Update_Simple_Product_Page_Interface;
use Sylius\Behat\Page\Admin\Product_Variant\Update_Page_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Webmozart\Assert\Assert;
final class Navigating_Between_Product_Show_And_Edit_Pages_Context implements Context
{
    private Product_Type_Enum $current_product_type;
    public function __construct(private readonly Update_Simple_Product_Page_Interface $update_simple_product_page, private readonly Update_Page_Interface $update_variant_product_page, private readonly Show_Page_Interface $product_show_page, private readonly Create_Simple_Product_Page_Interface $create_simple_product_page, private readonly Create_Configurable_Product_Page_Interface $create_configurable_product_page)
    {
    }
    #[When('I access the :product product')]
    public function i_access_the_product(Product_Interface $product): void
    {
        $this->product_show_page->open(['id' => $product->get_id()]);
    }
    #[When('I go to edit page')]
    public function i_go_to_edit_page(): void
    {
        $this->product_show_page->switch_to_edit_page();
    }
    #[When('I go to show page')]
    public function i_go_to_show_page(): void
    {
        $this->update_simple_product_page->switch_to_show_page();
    }
    #[When('I go to edit page of :variant variant')]
    public function i_go_to_edit_page_of_variant(Product_Variant_Interface $variant): void
    {
        $this->product_show_page->show_variant_edit_page($variant);
    }
    #[When('/^I want to create a new (simple|configurable) product$/')]
    public function i_want_to_create_a_new_product(string $product_type): void
    {
        $product_type = Product_Type_Enum::from($product_type);
        match ($product_type) {
            Product_Type_Enum::simple => $this->create_simple_product_page->open(),
            Product_Type_Enum::configurable => $this->create_configurable_product_page->open(),
        };
        $this->current_product_type = $product_type;
    }
    #[When('I want to modify the :product product')]
    public function i_want_to_modify_a_product(Product_Interface $product): void
    {
        $this->update_simple_product_page->open(['id' => $product->get_id()]);
    }
    #[When('I change position of the :image image to :position')]
    public function i_change_position_of_the_image_to(string $image, int $position): void
    {
        $this->update_simple_product_page->change_image_position($image, $position);
    }
    #[Then('I should be on :product product edit page')]
    public function i_should_be_on_product_edit_page(Product_Interface $product): void
    {
        Assert::true($this->update_simple_product_page->is_open(['id' => $product->get_id()]));
    }
    #[Then('I should be on :variant variant edit page')]
    public function i_should_be_on_variant_edit_page(Product_Variant_Interface $variant): void
    {
        Assert::true($this->update_variant_product_page->is_open(['productId' => $variant->get_product()->get_id(), 'id' => $variant->get_id()]));
    }
    #[Then('I should be on :product product show page')]
    public function i_should_be_on_product_show_page(Product_Interface $product): void
    {
        Assert::true($this->product_show_page->is_open(['id' => $product->get_id()]));
    }
    #[Then('I should not be able to open the product show page')]
    public function i_should_not_be_able_to_access_the_product_show_page(): void
    {
        match (Product_Type_Enum::from($this->current_product_type->value)) {
            Product_Type_Enum::simple => Assert::false($this->update_simple_product_page->has_show_page_button()),
            Product_Type_Enum::configurable => Assert::false($this->create_configurable_product_page->has_show_page_button()),
        };
    }
}
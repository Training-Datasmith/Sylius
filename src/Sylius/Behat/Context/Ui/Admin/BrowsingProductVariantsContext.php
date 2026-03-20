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
use Sylius\Behat\Page\Admin\Product_Variant\Index_Page_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Product\Resolver\Product_Variant_Resolver_Interface;
use Webmozart\Assert\Assert;
final readonly class Browsing_Product_Variants_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page, private Product_Variant_Resolver_Interface $default_product_variant_resolver)
    {
    }
    #[When('I start sorting variants by :field')]
    public function i_sort_products_by(string $field): void
    {
        $this->index_page->sort_by($field);
    }
    #[Then('the :productVariantCode variant of the :product product should appear in the store')]
    public function the_product_variant_should_appear_in_the_shop($product_variant_code, Product_Interface $product): void
    {
        $this->index_page->open(['productId' => $product->get_id()]);
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $product_variant_code]));
    }
    #[Then('I should see the product variant :productVariantName in the list')]
    public function i_should_see_the_product_variant_in_the_list(string $product_variant_name): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $product_variant_name]));
    }
    #[Then('the :productVariantCode variant of the :product product should not appear in the store')]
    public function the_product_variant_should_not_appear_in_the_shop($product_variant_code, Product_Interface $product): void
    {
        $this->index_page->open(['productId' => $product->get_id()]);
        Assert::false($this->index_page->is_single_resource_on_page(['code' => $product_variant_code]));
    }
    #[Then('the :product product should have no variants')]
    public function the_product_should_have_no_variants(Product_Interface $product): void
    {
        $this->index_page->open(['productId' => $product->get_id()]);
        $this->assert_number_of_variants_on_product_page(0);
    }
    #[Then('the :product product should have only one variant')]
    public function the_product_should_have_only_one_variant(Product_Interface $product): void
    {
        $this->index_page->open(['productId' => $product->get_id()]);
        $this->assert_number_of_variants_on_product_page(1);
    }
    #[When('/^I browse variants of (this product)$/')]
    #[When('/^I (?:|want to )view all variants of (this product)$/')]
    #[When('/^I view(?:| all) variants of the (product "[^"]+")(?:| again)$/')]
    public function i_want_to_view_all_variants_of_this_product(Product_Interface $product): void
    {
        $this->index_page->open(['productId' => $product->get_id()]);
    }
    #[Then('I should see :numberOfProductVariants variants in the list')]
    #[Then('I should see :numberOfProductVariants variant in the list')]
    #[Then('I should not see any variants in the list')]
    public function i_should_see_product_variants_in_the_list($number_of_product_variants = 0): void
    {
        Assert::same($this->index_page->count_items(), (int) $number_of_product_variants);
    }
    #[Then('I should see a single product variant in the list')]
    public function i_should_see_a_single_product_variant_in_the_list(): void
    {
        $this->i_should_see_product_variants_in_the_list(1);
    }
    #[Then('/^(this variant) should not exist in the product catalog$/')]
    public function product_variant_should_not_exist(Product_Variant_Interface $product_variant): void
    {
        $this->index_page->open(['productId' => $product_variant->get_product()->get_id()]);
        Assert::false($this->index_page->is_single_resource_on_page(['name' => $product_variant->get_name()]));
    }
    #[Then('/^(this variant) should still exist in the product catalog$/')]
    public function product_should_exist_in_the_product_catalog(Product_Variant_Interface $product_variant): void
    {
        $this->the_product_variant_should_appear_in_the_shop($product_variant->get_code(), $product_variant->get_product());
    }
    #[Then('/^the variant "([^"]+)" should have (\d+) items on hand$/')]
    public function this_variant_should_have_items_on_hand($product_variant_name, string $quantity): void
    {
        Assert::true($this->index_page->is_single_resource_with_specific_element_on_page(['name' => $product_variant_name], sprintf('[data-test-on-hand]:contains("%s")', $quantity)));
    }
    #[Then('/^the "([^"]+)" variant of ("[^"]+" product) should have (\d+) items on hand$/')]
    public function the_variant_of_product_should_have_items_on_hand($product_variant_name, Product_Interface $product, string $quantity): void
    {
        $this->index_page->open(['productId' => $product->get_id()]);
        Assert::true($this->index_page->is_single_resource_with_specific_element_on_page(['name' => $product_variant_name], sprintf('[data-test-on-hand]:contains("%s")', $quantity)));
    }
    #[Then('/^I should see that the ("([^"]+)" variant) is not tracked$/')]
    public function i_should_see_that_is_not_tracked(Product_Variant_Interface $product_variant): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $product_variant->get_name(), 'inventory' => 'Not tracked']));
    }
    #[Then('/^I should see that the ("[^"]+" variant) has zero on hand quantity$/')]
    public function i_should_see_that_the_variant_has_zero_on_hand_quantity(Product_Variant_Interface $product_variant): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $product_variant->get_name(), 'inventory' => '0 Available on hand']));
    }
    #[Then('/^(\d+) units of (this product) should be on hold$/')]
    public function units_of_this_product_should_be_on_hold($quantity, Product_Interface $product): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $this->default_product_variant_resolver->get_variant($product);
        $this->assert_on_hold_quantity_of_variant($quantity, $variant);
    }
    #[Then('/^(\d+) units of (this product) should be on hand$/')]
    public function units_of_this_product_should_be_on_hand($quantity, Product_Interface $product): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $this->default_product_variant_resolver->get_variant($product);
        Assert::same($this->index_page->get_on_hand_quantity_for($variant), (int) $quantity);
    }
    #[Then('/^there should be no units of (this product) on hold$/')]
    public function there_should_be_no_units_of_this_product_on_hold(Product_Interface $product): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $this->default_product_variant_resolver->get_variant($product);
        $this->assert_on_hold_quantity_of_variant(0, $variant);
    }
    #[Then('the :variant variant should have :amount items on hold')]
    public function this_variant_should_have_items_on_hold(Product_Variant_Interface $variant, $amount): void
    {
        $this->assert_on_hold_quantity_of_variant((int) $amount, $variant);
    }
    #[Then('the :variant variant of :product product should have :amount items on hold')]
    public function the_variant_of_product_should_have_items_on_hold(Product_Variant_Interface $variant, Product_Interface $product, $amount): void
    {
        $this->index_page->open(['productId' => $product->get_id()]);
        $this->assert_on_hold_quantity_of_variant((int) $amount, $variant);
    }
    #[Then('the first variant in the list should have :field :value')]
    public function the_first_variant_in_the_list_should_have(string $field, $value): void
    {
        Assert::same($this->index_page->get_column_fields($field)[0], $value);
    }
    #[Then('the last variant in the list should have :field :value')]
    public function the_last_variant_in_the_list_should_have(string $field, $value): void
    {
        $values = $this->index_page->get_column_fields($field);
        Assert::same(end($values), $value);
    }
    #[Then('/^(this variant) should have a (\d+) item currently in stock$/')]
    public function this_variant_should_have_a_item_currently_in_stock(Product_Variant_Interface $product_variant, $amount_in_stock): void
    {
        $this->index_page->open(['productId' => $product_variant->get_product()->get_id()]);
        Assert::same($this->index_page->get_on_hand_quantity_for($product_variant), (int) $amount_in_stock);
    }
    #[Then('/^I should be on the list of (this product)\'s variants$/')]
    public function i_should_be_on_the_list_of_this_product_variants(Product_Interface $product): void
    {
        Assert::true($this->index_page->is_open(['productId' => $product->get_id()]));
    }
    #[Then('/^I should see that the ("([^"]*)" variant) is enabled$/')]
    public function i_should_see_that_the_variant_is_enabled(Product_Variant_Interface $product_variant): void
    {
        Assert::true($this->index_page->is_single_resource_with_specific_element_on_page(['name' => $product_variant->get_name()], '[data-test-status-enabled]'));
    }
    /**
     * @param int $expectedAmount
     *
     * @throws \InvalidArgumentException
     */
    private function assert_on_hold_quantity_of_variant($expected_amount, \Sylius\Component\Core\Model\Product_Variant_Interface $variant): void
    {
        $actual_amount = $this->index_page->get_on_hold_quantity_for($variant);
        Assert::same($actual_amount, (int) $expected_amount, sprintf('Unexpected on hold quantity for "%s" variant. It should be "%s" but is "%s"', $variant->get_name(), $expected_amount, $actual_amount));
    }
    private function assert_number_of_variants_on_product_page(int $amount): void
    {
        Assert::same($this->index_page->count_items(), $amount, 'Product has %d variants, but should have %d');
    }
}
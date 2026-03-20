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
use Sylius\Behat\Page\Admin\Catalog_Promotion\Product_Variant\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Show_Page_Interface;
use Sylius\Component\Core\Model\Catalog_Promotion_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Webmozart\Assert\Assert;
final readonly class Browsing_Catalog_Promotion_Product_Variants_Context implements Context
{
    public function __construct(private Index_Page_Interface $catalog_promotion_product_variant_index_page, private Show_Page_Interface $product_show_page)
    {
    }
    #[Given('I am browsing variants affected by catalog promotion :catalogPromotion')]
    #[When('I browse variants affected by catalog promotion :catalogPromotion')]
    public function i_browse_variants_affected_by_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->catalog_promotion_product_variant_index_page->open(['id' => $catalog_promotion->get_id()]);
    }
    #[When('I want to view the product of variant :variant')]
    public function i_want_to_view_the_product_of_variant(Product_Variant_Interface $variant): void
    {
        $this->catalog_promotion_product_variant_index_page->show_product_of($variant->get_id());
    }
    #[When('I filter by code containing :phrase')]
    public function i_filter_by_code_containing(string $phrase): void
    {
        $this->catalog_promotion_product_variant_index_page->filter_by_code($phrase);
        $this->catalog_promotion_product_variant_index_page->filter();
    }
    #[When('I filter by name containing :phrase')]
    public function i_filter_by_name_containing(string $phrase): void
    {
        $this->catalog_promotion_product_variant_index_page->filter_by_name($phrase);
        $this->catalog_promotion_product_variant_index_page->filter();
    }
    #[Then('/^there should be (\d+) product variants? on the list$/')]
    public function there_should_be_product_variants_on_the_list(int $count): void
    {
        Assert::same($this->catalog_promotion_product_variant_index_page->count_items(), $count);
    }
    #[Then('it should be the :variantName product variant')]
    #[Then('it should be :firstVariant and :secondVariant product variants')]
    public function the_product_variant_should_be_in_the_registry(string ...$variants_names): void
    {
        foreach ($variants_names as $variant_name) {
            Assert::true($this->catalog_promotion_product_variant_index_page->is_single_resource_on_page(['name' => $variant_name]));
        }
    }
    #[Then('I should be viewing the details of product :product')]
    public function i_should_be_viewing_the_details_of_product(Product_Interface $product): void
    {
        Assert::true($this->product_show_page->is_open(['id' => $product->get_id()]));
    }
}
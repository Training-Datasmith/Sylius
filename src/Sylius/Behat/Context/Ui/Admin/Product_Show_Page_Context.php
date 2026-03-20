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
use Sylius\Behat\Element\Product\Show_Page\Associations_Element_Interface;
use Sylius\Behat\Element\Product\Show_Page\Attributes_Element_Interface;
use Sylius\Behat\Element\Product\Show_Page\Details_Element_Interface;
use Sylius\Behat\Element\Product\Show_Page\Media_Element_Interface;
use Sylius\Behat\Element\Product\Show_Page\Options_Element_Interface;
use Sylius\Behat\Element\Product\Show_Page\Pricing_Element_Interface;
use Sylius\Behat\Element\Product\Show_Page\Shipping_Element_Interface;
use Sylius\Behat\Element\Product\Show_Page\Taxonomy_Element_Interface;
use Sylius\Behat\Element\Product\Show_Page\Translations_Element_Interface;
use Sylius\Behat\Element\Product\Show_Page\Variants_Element_Interface;
use Sylius\Behat\Page\Admin\Product\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Show_Page_Interface;
use Sylius\Component\Core\Model\Catalog_Promotion_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Symfony\Component\Routing\Generator\Url_Generator_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Show_Page_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page, private Show_Page_Interface $product_show_page, private Associations_Element_Interface $associations_element, private Attributes_Element_Interface $attributes_element, private Details_Element_Interface $details_element, private Media_Element_Interface $media_element, private Translations_Element_Interface $translations_element, private Pricing_Element_Interface $pricing_element, private Shipping_Element_Interface $shipping_element, private Taxonomy_Element_Interface $taxonomy_element, private Options_Element_Interface $options_element, private Variants_Element_Interface $variants_element, private Url_Generator_Interface $url_generator)
    {
    }
    #[Given('I am browsing products')]
    public function i_am_browsing_products(): void
    {
        $this->index_page->open();
    }
    #[When('I try to reach nonexistent product')]
    public function i_try_to_reach_nonexistent_product_page(): void
    {
        $this->product_show_page->try_to_open(['id' => 0, '_locale' => 'en_US']);
    }
    #[When('I access :product product page')]
    #[When('I access the :product product')]
    public function i_access_the_product(Product_Interface $product): void
    {
        $this->index_page->show_product_page($product->get_name());
    }
    #[When('I show this product in the :channel channel')]
    public function i_show_this_product_in_the_channel(string $channel): void
    {
        $this->product_show_page->show_product_in_channel($channel);
    }
    #[When('I show this product in this channel')]
    public function i_show_this_product_in_this_channel(): void
    {
        $this->product_show_page->show_product_in_single_channel();
    }
    #[When('I access :product product')]
    public function i_access_product(Product_Interface $product): void
    {
        $this->product_show_page->open(['id' => $product->get_id()]);
    }
    #[When('I access the price history of a simple product for :channel channel')]
    public function i_access_the_price_history_index_page_of_simple_product_for_channel(Channel_Interface $channel): void
    {
        $pricing_row = $this->pricing_element->get_simple_product_pricing_row_for_channel($channel->get_code());
        $pricing_row->find('css', '[data-test-price-history]')->click();
    }
    #[When('I access the price history of a product variant :variant for :channel channel')]
    public function i_access_the_price_history_index_page_of_variant_for_channel(Product_Variant_Interface $variant, Channel_Interface $channel): void
    {
        $pricing_row = $this->pricing_element->get_variant_pricing_row_for_channel($variant->get_code(), $channel->get_code());
        $pricing_row->find('css', '[data-test-price-history]')->click();
    }
    #[Then('I should see this product\'s product page')]
    public function i_should_see_this_product_page(Product_Interface $product): void
    {
        Assert::true($this->product_show_page->is_open(['id' => $product->get_id()]));
    }
    #[Then('I should see product show page without variants')]
    public function i_should_see_product_show_page_without_variants(): void
    {
        Assert::true($this->product_show_page->is_simple_product_page());
    }
    #[Then('I should see product show page with variants')]
    public function i_should_see_product_show_page_with_variants(): void
    {
        Assert::false($this->product_show_page->is_simple_product_page());
    }
    #[Then('I should see product name :productName')]
    public function i_should_see_product_name(string $product_name): void
    {
        Assert::same($product_name, $this->product_show_page->get_name());
    }
    #[Then('I should see product breadcrumb :breadcrumb')]
    public function i_should_see_breadcrumb(string $breadcrumb): void
    {
        Assert::contains($this->product_show_page->get_breadcrumb(), $breadcrumb);
    }
    #[Then('I should see price :price for channel :channel')]
    public function i_should_see_price_for_channel(string $price, Channel_Interface $channel): void
    {
        Assert::same($this->pricing_element->get_price_for_channel($channel->get_code()), $price);
    }
    #[Then('I should see :lowestPriceBeforeDiscount as its lowest price before the discount in :channel channel')]
    public function i_should_see_as_its_lowest_price_before_the_discount_in_channel(string $lowest_price_before_discount, Channel_Interface $channel): void
    {
        Assert::same($this->pricing_element->get_lowest_price_before_discount_for_channel($channel->get_code()), $lowest_price_before_discount);
    }
    #[Then('I should not see the lowest price before the discount in :channel channel')]
    public function i_should_not_see_the_lowest_price_before_the_discount_in_channel(Channel_Interface $channel): void
    {
        Assert::same($this->pricing_element->get_lowest_price_before_discount_for_channel($channel->get_code()), '-');
    }
    #[Then('I should see the lowest price before the discount of :lowestPriceBeforeDiscount for :variant variant in :channel channel')]
    public function i_should_see_variant_with_the_lowest_price_before_the_discount_of_in_channel(string $lowest_price_before_discount, Product_Variant_Interface $variant, Channel_Interface $channel): void
    {
        Assert::true($this->variants_element->has_product_variant_with_lowest_price_before_discount_in_channel($variant->get_code(), $lowest_price_before_discount, $channel->get_code()));
    }
    #[Then('I should not see the lowest price before the discount for :variant variant in :channel channel')]
    public function i_should_not_see_the_lowest_price_before_the_discount_for_variant_in_channel(Product_Variant_Interface $variant, Channel_Interface $channel): void
    {
        Assert::true($this->variants_element->has_product_variant_with_lowest_price_before_discount_in_channel($variant->get_code(), '-', $channel->get_code()));
    }
    #[Then('I should not see price for channel :channelName')]
    public function i_should_not_see_price_for_channel(string $channel_name): void
    {
        Assert::same($this->pricing_element->get_price_for_channel($channel_name), '');
    }
    #[Then('I should see original price :price for channel :channel')]
    public function i_should_see_original_price_for_channel(string $original_price, Channel_Interface $channel): void
    {
        Assert::same($this->pricing_element->get_original_price_for_channel($channel->get_code()), $original_price);
    }
    #[Then('I should see product\'s code is :code')]
    public function i_should_see_product_code_is(string $code): void
    {
        Assert::same($this->details_element->get_product_code(), $code);
    }
    #[Then('I should see the product is enabled for channel :channel')]
    public function i_should_see_product_is_enabled_for_channels(Channel_Interface $channel): void
    {
        Assert::true($this->details_element->has_channel($channel->get_code()));
    }
    #[Then('I should see the product in neither channel')]
    public function i_should_see_the_product_in_neither_channel(): void
    {
        Assert::same($this->details_element->count_channels(), 0);
    }
    #[Then('I should see :currentStock as a current stock of this product')]
    public function i_should_see_as_a_current_stock_of_this_product(int $current_stock): void
    {
        Assert::same($this->details_element->get_product_current_stock(), $current_stock);
    }
    #[Then('I should see product\'s tax category is :taxCategory')]
    public function i_should_see_product_tax_category_is(string $tax_category): void
    {
        Assert::same($this->details_element->get_product_tax_category(), $tax_category);
    }
    #[Then('I should see main taxon is :mainTaxonName')]
    public function i_should_see_main_taxon_is(string $main_taxon_name): void
    {
        Assert::same($this->taxonomy_element->get_product_main_taxon(), $main_taxon_name);
    }
    #[Then('I should see product taxon :taxonName')]
    public function i_should_see_product_taxon(string $taxon_name): void
    {
        Assert::contains($this->taxonomy_element->get_product_taxons(), $taxon_name);
    }
    #[Then('I should see product\'s shipping category is :shippingCategory')]
    public function i_should_see_product_shipping_category_is(string $shipping_category): void
    {
        Assert::same($this->shipping_element->get_product_shipping_category(), $shipping_category);
    }
    #[Then('I should see product\'s width is :width')]
    public function i_should_see_product_width_is(float $width): void
    {
        Assert::same($this->shipping_element->get_product_width(), $width);
    }
    #[Then('I should see product\'s height is :height')]
    public function i_should_see_product_height_is(float $height): void
    {
        Assert::same($this->shipping_element->get_product_height(), $height);
    }
    #[Then('I should see product\'s depth is :depth')]
    public function i_should_see_product_depth_is(float $depth): void
    {
        Assert::same($this->shipping_element->get_product_depth(), $depth);
    }
    #[Then('I should see product\'s weight is :weight')]
    public function i_should_see_product_weight_is(float $weight): void
    {
        Assert::same($this->shipping_element->get_product_weight(), $weight);
    }
    #[Then('I should see an image related to this product')]
    public function i_should_see_image_related_to_this_product(): void
    {
        Assert::true($this->media_element->is_image_displayed());
    }
    #[Then('I should see product name is :name')]
    public function i_should_see_product_name_is(string $name): void
    {
        Assert::same($this->translations_element->get_name(), $name);
    }
    #[Then('I should see product slug is :slug')]
    public function i_should_see_product_slug_is(string $slug): void
    {
        Assert::same($this->translations_element->get_slug(), $slug);
    }
    #[Then('I should see product\'s description is :description')]
    public function i_should_see_product_s_description_is(string $description): void
    {
        Assert::same($this->translations_element->get_description(), $description);
    }
    #[Then('I should see product\'s meta keyword(s) is/are :metaKeywords')]
    public function i_should_see_product_meta_keywords_are(string $meta_keywords): void
    {
        Assert::same($this->translations_element->get_product_meta_keywords(), $meta_keywords);
    }
    #[Then('I should see product\'s short description is :shortDescription')]
    public function i_should_see_product_short_description_is(string $short_description): void
    {
        Assert::same($this->translations_element->get_short_description(), $short_description);
    }
    #[Then('I should see product association :association with :productName')]
    public function i_should_see_product_association_with(string $association, string $product_name): void
    {
        Assert::true($this->associations_element->is_associated_with($association, $product_name));
    }
    #[Then('I should see product association type :association')]
    public function i_should_see_product_association_type(string $association): void
    {
        Assert::true($this->associations_element->has_association($association));
    }
    #[Then('I should see option :optionName')]
    public function i_should_see_option(string $option_name): void
    {
        Assert::true($this->options_element->is_option_defined($option_name));
    }
    #[Then('I should see :count variants')]
    public function i_should_see_variants(int $count): void
    {
        Assert::same($this->variants_element->count_variants_on_page(), $count);
    }
    #[Then('I should see :variantName variant with code :code, priced :price and current stock :currentStock and in :channel channel')]
    public function i_should_see_variant_with_code_price_and_current_stock(string $variant_name, string $code, string $price, string $current_stock, Channel_Interface $channel): void
    {
        Assert::true($this->variants_element->has_product_variant_with_code_price_and_current_stock($variant_name, $code, $price, $current_stock, $channel->get_code()));
    }
    #[Then('I should see the :variant variant')]
    public function i_should_see_the_variant(Product_Variant_Interface $variant): void
    {
        Assert::true($this->variants_element->has_product_variant($variant->get_code()));
    }
    #[Then('I should see attribute :attribute with value :value in :locale locale')]
    public function i_should_see_attribute_with_value_in_locale(string $attribute, string $value, Locale_Interface $locale): void
    {
        Assert::true($this->attributes_element->has_attribute_in_locale($attribute, $locale->get_code(), $value));
    }
    #[Then('/^I should see non-translatable attribute "([^"]+)" with value ([^"]+)%$/')]
    public function i_should_see_non_translatable_attribute_with_value(string $attribute, string $value): void
    {
        Assert::true($this->attributes_element->has_non_translatable_attribute($attribute, (float) $value / 100));
    }
    #[Then('I should not be able to show this product in shop')]
    public function i_should_not_be_able_to_show_this_product_in_shop(): void
    {
        Assert::true($this->product_show_page->is_show_in_shop_button_disabled());
    }
    #[Then('this product price should be decreased by catalog promotion :catalogPromotion in :channel channel')]
    public function this_product_price_should_be_decreased_by_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): void
    {
        Assert::in_array($catalog_promotion->get_name(), $this->pricing_element->get_catalog_promotions_names_for_channel($channel->get_code()));
        $url = $this->url_generator->generate('sylius_admin_catalog_promotion_show', ['id' => $catalog_promotion->get_id()]);
        Assert::in_array($url, $this->pricing_element->get_catalog_promotion_links_for_channel($channel->get_code()));
    }
    #[Then(':variantName variant price should be decreased by catalog promotion :catalogPromotion in :channelName channel')]
    public function variant_price_should_be_decreased_by_catalog_promotion(string $variant_name, Catalog_Promotion_Interface $catalog_promotion, string $channel_name): void
    {
        Assert::in_array($catalog_promotion->get_name(), $this->product_show_page->get_applied_catalog_promotions_names($variant_name, $channel_name));
        $url = $this->url_generator->generate('sylius_admin_catalog_promotion_show', ['id' => $catalog_promotion->get_id()]);
        Assert::in_array($url, $this->product_show_page->get_applied_catalog_promotions_links($variant_name, $channel_name));
    }
    #[Then(':variantName variant price should not be decreased by catalog promotion :catalogPromotion in :channelName channel')]
    public function variant_price_should_not_be_decreased_by_catalog_promotion(string $variant_name, Catalog_Promotion_Interface $catalog_promotion, string $channel_name): void
    {
        $applied_promotions = $this->product_show_page->get_applied_catalog_promotions_names($variant_name, $channel_name);
        Assert::false(in_array($catalog_promotion->get_name(), $applied_promotions));
    }
}
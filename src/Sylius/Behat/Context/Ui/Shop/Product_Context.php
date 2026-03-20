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
namespace Sylius\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Product\Index_Page\Vertical_Menu_Element_Interface;
use Sylius\Behat\Element\Product\Show_Page\Lowest_Price_Information_Element_Interface;
use Sylius\Behat\Page\Error_Page_Interface;
use Sylius\Behat\Page\Shop\Product\Index_Page_Interface;
use Sylius\Behat\Page\Shop\Product\Show_Page_Interface;
use Sylius\Behat\Page\Shop\Product_Review\Index_Page_Interface as ProductReviewIndexPageInterface;
use Sylius\Behat\Service\Setter\Channel_Context_Setter_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Context implements Context
{
    public function __construct(private Show_Page_Interface $show_page, private Index_Page_Interface $index_page, private Product_Review_Index_Page_Interface $product_reviews_index_page, private Error_Page_Interface $error_page, private Vertical_Menu_Element_Interface $vertical_menu_element, private Channel_Context_Setter_Interface $channel_context_setter, private Lowest_Price_Information_Element_Interface $lowest_price_information_element)
    {
    }
    #[When('I open page :url')]
    public function i_open_page(string $url): void
    {
        $this->show_page->visit($url);
    }
    #[When('I try to reach nonexistent product')]
    public function i_try_to_reach_nonexistent_product_page(?string $locale_code = 'en_US'): void
    {
        $this->show_page->try_to_open(['slug' => 'nonexistent_product', '_locale' => $locale_code]);
    }
    #[When('/^I try to browse products from (taxon "([^"]+)")$/')]
    public function i_try_to_browse_products_from(Taxon_Interface $taxon): void
    {
        $this->index_page->try_to_open(['slug' => $taxon->get_slug()]);
    }
    #[When('/^I check (this product)\'s details$/')]
    #[When('/^I check (this product)\'s details in the ("([^"]+)" locale)$/')]
    #[When('I view product :product')]
    #[When('I view product :product in the :localeCode locale')]
    #[When('customer view product :product')]
    public function i_open_product_page(Product_Interface $product, string $locale_code = 'en_US'): void
    {
        $this->show_page->open(['slug' => $product->get_translation($locale_code)->get_slug(), '_locale' => $locale_code]);
    }
    #[When('/^I try to check (this product)\'s details in the ("([^"]+)" locale)$/')]
    public function i_try_to_open_product_page(Product_Interface $product, string $locale_code = 'en_US'): void
    {
        $this->show_page->try_to_open(['slug' => $product->get_translation($locale_code)->get_slug(), '_locale' => $locale_code]);
    }
    #[When('I update the quantity of this product to :quantity')]
    public function i_update_the_quantity_of_this_product(int $quantity): void
    {
        $this->show_page->update_quantity($quantity);
    }
    #[Then('/^("[^"]+" variant) and ("[^"]+" variant) should be discounted$/')]
    #[Then('/^("[^"]+" variant) should be discounted$/')]
    public function variant_and_variant_should_be_discounted(Product_Variant_Interface ...$variants): void
    {
        /** @var ProductVariantInterface $variant */
        foreach ($variants as $variant) {
            $this->show_page->open(['slug' => $variant->get_product()->get_translation('en_US')->get_slug(), '_locale' => 'en_US']);
            $this->show_page->select_variant($variant->get_name());
            Assert::greater_than($this->show_page->get_original_price(), $this->show_page->get_price());
        }
    }
    #[Then('/^("[^"]+" variant) and ("[^"]+" variant) should not be discounted$/')]
    #[Then('/^("[^"]+" variant) should not be discounted$/')]
    public function variant_and_variant_should_not_be_discounted(Product_Variant_Interface ...$variants): void
    {
        /** @var ProductVariantInterface $variant */
        foreach ($variants as $variant) {
            $this->show_page->open(['slug' => $variant->get_product()->get_translation('en_US')->get_slug(), '_locale' => 'en_US']);
            $this->show_page->select_variant($variant->get_name());
            Assert::is_empty($this->show_page->get_original_price());
        }
    }
    #[Then('/^I should not be able to view (this product) in the ("([^"]+)" locale)$/')]
    public function i_should_not_be_able_to_view_this_product_in_locale(Product_Interface $product, string $locale_code = 'en_US'): void
    {
        Assert::false($this->show_page->is_open(['slug' => $product->get_translation($locale_code)->get_slug(), '_locale' => $locale_code]));
    }
    #[Then('I should see the product name :name')]
    public function i_should_see_product_name(string $name): void
    {
        Assert::same($this->show_page->get_name(), $name);
    }
    #[Then('I should see the product description :description')]
    public function i_should_see_the_product_description(string $description): void
    {
        Assert::same($this->show_page->get_description(), $description);
    }
    #[Then('I should be on :product product detailed page')]
    #[Then('I should still be on product :product page')]
    public function i_should_be_on_product_detailed_page(Product_Interface $product): void
    {
        Assert::true($this->show_page->is_open(['slug' => $product->get_slug()]));
    }
    #[When('I browse products from taxon :taxon')]
    #[When('I browse products from product taxon code :taxon')]
    public function i_check_list_of_products_for_taxon(Taxon_Interface $taxon): void
    {
        $this->index_page->open(['slug' => $taxon->get_slug()]);
    }
    #[When('I try to browse products from taxon :taxon with a trailing slash in the path')]
    public function i_try_to_browse_products_from_taxon_with_a_trailing_slash_in_the_path(Taxon_Interface $taxon): void
    {
        $this->index_page->try_to_open(['slug' => $taxon->get_slug() . '/']);
    }
    #[When('I search for products with name :name')]
    public function i_search_for_products_with_name(string $name): void
    {
        $this->index_page->search($name);
    }
    #[When('/^I sort products by the (oldest|newest) date first$/')]
    public function i_sort_products_by_the_date_first(string $sort_direction): void
    {
        $sort_direction = 'oldest' === $sort_direction ? 'Oldest first' : 'Newest first';
        $this->index_page->sort($sort_direction);
    }
    #[When('I sort products by the lowest price first')]
    public function i_sort_products_by_the_lowest_price_first(): void
    {
        $this->index_page->sort('Cheapest first');
    }
    #[When('I sort products by the highest price first')]
    public function i_sort_products_by_the_highest_price_first(): void
    {
        $this->index_page->sort('Most expensive first');
    }
    #[When('I sort products alphabetically from a to z')]
    public function i_sort_products_alphabetically_from_a_to_z(): void
    {
        $this->index_page->sort('From A to Z');
    }
    #[When('I sort products alphabetically from z to a')]
    public function i_sort_products_alphabetically_from_z_to_a(): void
    {
        $this->index_page->sort('From Z to A');
    }
    #[When('I clear filter')]
    public function i_clear_filter(): void
    {
        $this->index_page->clear_filter();
    }
    #[Then('I should see the product :productName')]
    public function i_should_see_product(string $product_name): void
    {
        Assert::true($this->index_page->is_product_on_list($product_name));
    }
    #[Then('I should not see the product :productName')]
    public function i_should_not_see_product(string $product_name): void
    {
        Assert::false($this->index_page->is_product_on_list($product_name));
    }
    #[Then('I should see empty list of products')]
    public function i_should_see_empty_list_of_products(): void
    {
        Assert::true($this->index_page->is_empty());
    }
    #[Then('I should see that it is out of stock')]
    public function i_should_see_it_is_out_of_stock(): void
    {
        Assert::true($this->show_page->is_out_of_stock());
    }
    #[Then('I should be unable to add it to the cart')]
    public function i_should_be_unable_to_add_it_to_the_cart(): void
    {
        Assert::false($this->show_page->has_add_to_cart_button());
    }
    #[Then('the product price should be :price')]
    #[Then('the product variant price should be :price')]
    #[Then('this product variant price should be :price')]
    #[Then('I should see the product price :price')]
    #[Then('I should see that the combination is :price')]
    #[Then('customer should see the product price :price')]
    public function i_should_see_the_product_price(string $price): void
    {
        Assert::same($this->show_page->get_price(), $price);
    }
    #[Then('the product original price should be :price')]
    #[Then('this product original price should be :price')]
    #[Then('I should see the product original price :price')]
    #[Then('/^customer should see the product original price ("[^"]+")$/')]
    public function i_should_see_the_product_original_price(string $price): void
    {
        Assert::true($this->show_page->is_original_price_visible());
        Assert::same($this->show_page->get_original_price(), $price);
    }
    #[Then('I should see :product product discounted from :originalPrice to :price by :promotionLabel on the list')]
    #[Then('I should see :product product discounted from :originalPrice to :price')]
    public function i_should_see_product_discounted_on_the_list(Product_Interface $product, string $original_price, string $price, ?string $promotion_label = null): void
    {
        Assert::same($this->index_page->get_product_price($product->get_code()), $price);
        Assert::same($this->index_page->get_product_original_price($product->get_code()), $original_price);
        if ($promotion_label !== null) {
            Assert::same($this->index_page->get_product_promotion_label($product->get_name()), $promotion_label);
        }
    }
    #[Then('I should see :product product not discounted on the list')]
    public function i_should_see_product_not_discounted_on_the_list(Product_Interface $product): void
    {
        $original_price = $this->index_page->get_product_original_price($product->get_code());
        Assert::null($original_price);
    }
    #[Then('I should see this product is not discounted')]
    public function i_should_see_product_is_not_discounted(): void
    {
        Assert::null($this->show_page->get_original_price());
    }
    #[Then('/^I should see ("[^"]+" variant) is not discounted$/')]
    #[Then('/^I should see (this variant) is not discounted$/')]
    public function i_should_see_variant_is_not_discounted(Product_Variant_Interface $variant): void
    {
        $this->show_page->select_variant($variant->get_name());
        Assert::null($this->show_page->get_original_price());
    }
    #[Then('I should not see any original price')]
    #[Then('I should see this product has no catalog promotion applied')]
    public function i_should_not_see_the_product_original_price(): void
    {
        Assert::false($this->show_page->is_original_price_visible());
    }
    #[Then('/^the visitor should(?:| still) see "([^"]+)" as the (price|original price) of the ("[^"]+" product) in the ("[^"]+" channel)$/')]
    public function the_visitor_should_see_as_the_price_of_the_product_in_the_channel(string $price, string $price_type, Product_Interface $product, Channel_Interface $channel): void
    {
        $this->channel_context_setter->set_channel($channel);
        $locale_code = $channel->get_default_locale()->get_code();
        $this->show_page->open(['slug' => $product->get_translation($locale_code)->get_slug(), '_locale' => $locale_code]);
        if ($price_type === 'original price') {
            Assert::same($this->show_page->get_original_price(), $price);
            return;
        }
        if ($price_type === 'price') {
            Assert::same($this->show_page->get_price(), $price);
            return;
        }
        throw new \InvalidArgumentException('Not recognized price type');
    }
    #[Then('the original price of the :product product in the :channel channel should be empty')]
    public function the_original_price_of_the_product_in_the_channel_should_be_empty(Product_Interface $product, Channel_Interface $channel): void
    {
        $this->channel_context_setter->set_channel($channel);
        $locale_code = $channel->get_default_locale()->get_code();
        $this->show_page->open(['slug' => $product->get_translation($locale_code)->get_slug(), '_locale' => $locale_code]);
        Assert::null($this->show_page->get_original_price());
    }
    #[When('I select its :optionName as :optionValue')]
    public function i_select_its_option_as(string $option_name, string $option_value): void
    {
        $this->show_page->select_option($option_name, $option_value);
    }
    #[When('I select :variantName variant')]
    #[When('I view :variantName variant')]
    public function i_select_variant(string $variant_name): void
    {
        $this->show_page->select_variant($variant_name);
    }
    #[When('the visitor view :variant variant')]
    public function the_visitor_view_variant(Product_Variant_Interface $variant): void
    {
        $this->show_page->open(['slug' => $variant->get_product()->get_translation('en_US')->get_slug(), '_locale' => 'en_US']);
        $this->show_page->select_variant($variant->get_name());
    }
    #[When('I view :variantName variant of the :product product')]
    public function i_view_variant_of_product(string $variant_name, Product_Interface $product): void
    {
        $this->show_page->open(['slug' => $product->get_translation('en_US')->get_slug(), '_locale' => 'en_US']);
        $this->show_page->select_variant($variant_name);
    }
    #[Then('/^I should see ("[^"]+" product) is discounted from "([^"]+)" to "([^"]+)" with "([^"]+)" promotion$/')]
    #[Then('/^I should see (this product) is discounted from "([^"]+)" to "([^"]+)" with "([^"]+)" promotion$/')]
    #[Then('/^I should see (this product) is discounted from "([^"]+)" to "([^"]+)" with "([^"]+)" and "([^"]+)" promotions$/')]
    #[Then('/^I should see (this product) is discounted from "([^"]+)" to "([^"]+)" with "([^"]+)", "([^"]+)" and "([^"]+)" promotions$/')]
    #[Then('/^I should see (this product) is discounted from "([^"]+)" to "([^"]+)" with "([^"]+)", "([^"]+)", "([^"]+)" and "([^"]+)" promotions$/')]
    public function i_should_see_product_is_discounted_from_to_with_promotions(Product_Interface $product, string $original_price, string $price, string ...$promotions_names): void
    {
        Assert::same($this->show_page->get_price(), $price);
        Assert::same($this->show_page->get_original_price(), $original_price);
        foreach ($promotions_names as $promotion_name) {
            Assert::true($this->show_page->has_catalog_promotion_applied($promotion_name), sprintf("Catalog promotion '%s' does not found ", $promotion_name));
        }
    }
    #[Then('/^I should see ("[^"]+" variant) is discounted from "([^"]+)" to "([^"]+)" with "([^"]+)" promotion$/')]
    #[Then('/^I should see (this variant) is discounted from "([^"]+)" to "([^"]+)" with "([^"]+)" promotion$/')]
    #[Then('/^I should see (this variant) is discounted from "([^"]+)" to "([^"]+)" with "([^"]+)" and "([^"]+)" promotions$/')]
    #[Then('/^I should see (this variant) is discounted from "([^"]+)" to "([^"]+)" with "([^"]+)", "([^"]+)" and "([^"]+)" promotions$/')]
    #[Then('/^I should see (this variant) is discounted from "([^"]+)" to "([^"]+)" with "([^"]+)", "([^"]+)", "([^"]+)" and "([^"]+)" promotions$/')]
    public function i_should_see_variant_is_discounted_from_to_with_promotions(Product_Variant_Interface $variant, string $original_price, string $price, string ...$promotions_names): void
    {
        $this->show_page->select_variant($variant->get_name());
        Assert::same($this->show_page->get_price(), $price);
        Assert::same($this->show_page->get_original_price(), $original_price);
        foreach ($promotions_names as $promotion_name) {
            Assert::true($this->show_page->has_catalog_promotion_applied($promotion_name));
        }
    }
    #[Then('/^I should see (this variant) is discounted from "([^"]+)" to "([^"]+)" with ([^"]+) promotions$/')]
    public function i_should_see_variant_is_discounted_from_to_with_number_of_promotions(Product_Variant_Interface $variant, string $original_price, string $price, int $number_of_promotions): void
    {
        $this->show_page->select_variant($variant->get_name());
        Assert::same($this->show_page->get_price(), $price);
        Assert::same($this->show_page->get_original_price(), $original_price);
        Assert::count($this->show_page->get_catalog_promotion_names(), $number_of_promotions);
    }
    #[Then('/^I should see (this variant) is discounted from "([^"]+)" to "([^"]+)" with only "([^"]+)" promotion$/')]
    public function i_should_see_variant_is_discounted_from_to_with_only_promotion(Product_Variant_Interface $variant, string $original_price, string $price, string $promotion_name): void
    {
        $this->show_page->select_variant($variant->get_name());
        Assert::same(count($this->show_page->get_catalog_promotions()), 1);
        Assert::same($this->show_page->get_catalog_promotion_name(), $promotion_name);
        Assert::same($this->show_page->get_price(), $price);
        Assert::same($this->show_page->get_original_price(), $original_price);
    }
    #[Then('/^the visitor should(?:| still) see that the ("[^"]+" variant) is discounted from "([^"]+)" to "([^"]+)" with "([^"]+)" promotion$/')]
    public function the_visitor_should_see_that_the_variant_is_discounted_from_to_with_promotion(Product_Variant_Interface $variant, string $original_price, string $price, string $promotion_name): void
    {
        /** @var ProductInterface $product */
        $product = $variant->get_product();
        $this->i_open_product_page($product);
        $this->i_should_see_variant_is_discounted_from_to_with_promotions($variant, $original_price, $price, $promotion_name);
    }
    #[Then('/^the visitor should(?:| still) see that the ("[^"]+" variant) is discounted from "([^"]+)" to "([^"]+)" with ([^"]+) promotions$/')]
    public function the_visitor_should_see_variant_is_discounted_from_to_with_number_of_promotions(Product_Variant_Interface $variant, string $original_price, string $price, int $number_of_promotions): void
    {
        /** @var ProductInterface $product */
        $product = $variant->get_product();
        $this->i_open_product_page($product);
        $this->i_should_see_variant_is_discounted_from_to_with_number_of_promotions($variant, $original_price, $price, $number_of_promotions);
    }
    #[Then('its current variant should be named :name')]
    public function its_current_variant_should_be_named(string $name): void
    {
        Assert::same($this->show_page->get_current_variant_name(), $name);
    }
    #[Then('I should see the product :product with price :productPrice')]
    public function i_should_see_the_product_with_price(Product_Interface $product, string $product_price): void
    {
        Assert::same($this->index_page->get_product_price($product->get_code()), $product_price);
    }
    #[Then('/^I should be notified that (this product) does not have sufficient stock$/')]
    public function i_should_be_notified_that_this_product_does_not_have_sufficient_stock(Product_Interface $product): void
    {
        Assert::same($this->show_page->get_validation_message('quantity'), sprintf('%s does not have sufficient stock.', $product->get_name()));
    }
    #[Then('/^I should not be notified that (this product) does not have sufficient stock$/')]
    public function i_should_not_be_notified_that_this_product_does_not_have_sufficient_stock(Product_Interface $product): void
    {
        try {
            $validation_message = $this->show_page->get_validation_message('quantity');
        } catch (Element_Not_Found_Exception) {
            $validation_message = '';
        }
        Assert::not_contains($validation_message, sprintf('%s does not have sufficient stock.', $product->get_name()));
    }
    #[Then('I should be notified that the quantity of this product must be between 1 and 9999')]
    public function i_should_be_notified_that_the_quantity_of_this_product_must_be_between1and9999(): void
    {
        Assert::same($this->show_page->get_validation_message('quantity'), 'Quantity must be between 1 and 9999.');
    }
    #[Then('I should not be able to add it')]
    public function i_should_not_be_able_to_add_it(): void
    {
        Assert::false($this->show_page->has_add_to_cart_button_enabled(), 'Add to cart button should be disabled.');
    }
    #[Then('I should be able to see a main image of type :type')]
    public function i_should_see_a_main_image_of_type(string $type): void
    {
        Assert::true($this->show_page->is_main_image_of_type_displayed($type));
    }
    #[Then('the main image should be of type :type')]
    public function the_main_image_should_be_of_type(string $type): void
    {
        Assert::true($this->show_page->is_main_image_of_type_displayed($type), sprintf('Main image should be of type "%s" but it is not.', $type));
    }
    #[Then('the first thumbnail image should be of type :type')]
    public function the_first_thumbnail_image_should_be_of_type(string $type): void
    {
        Assert::true($this->show_page->get_first_thumbnails_image_type() === $type);
    }
    #[Then('the second thumbnail image should be of type :type')]
    public function the_second_thumbnail_image_should_be_of_type(string $type): void
    {
        Assert::true($this->show_page->get_second_thumbnails_image_type() === $type);
    }
    #[Then('I should see :numberOfProducts products in the list')]
    public function i_should_see_products_in_the_list(string $number_of_products): void
    {
        Assert::same($this->index_page->count_products_items(), (int) $number_of_products);
    }
    #[Then('I should see a product with name :name')]
    public function i_should_see_product_with_name(string $name): void
    {
        Assert::true($this->index_page->is_product_on_page_with_name($name));
    }
    #[Then('the first product on the list should have name :name')]
    public function the_first_product_on_the_list_should_have_name(string $name): void
    {
        Assert::same($this->index_page->get_first_product_name_from_list(), $name);
    }
    #[Then('the first product on the list should have name :product and price :price')]
    public function the_first_product_on_the_list_should_have_name_and_price(Product_Interface $product, string $price): void
    {
        Assert::same($this->index_page->get_first_product_name_from_list(), $product->get_name());
        Assert::same($this->index_page->get_product_price($product->get_code()), $price);
    }
    #[Then('the last product on the list should have name :name')]
    public function the_last_product_on_the_list_should_have_name($name): void
    {
        Assert::same($this->index_page->get_last_product_name_from_list(), $name);
    }
    #[Then('the last product on the list should have name :product and price :price')]
    public function the_last_product_on_the_list_should_have_name_and_price(Product_Interface $product, string $price): void
    {
        Assert::same($this->index_page->get_last_product_name_from_list(), $product->get_name());
        Assert::same($this->index_page->get_product_price($product->get_code()), $price);
    }
    #[Then('I should see :count product reviews')]
    public function i_should_see_product_reviews(string $count): void
    {
        Assert::same($this->show_page->count_reviews(), (int) $count);
    }
    #[Then('I should see reviews titled :firstReview, :secondReview and :thirdReview')]
    public function i_should_see_reviews_titled(...$reviews): void
    {
        foreach ($reviews as $review) {
            Assert::true($this->show_page->has_review_titled($review), sprintf('Product should have review titled "%s" but it does not.', $review));
        }
    }
    #[Then('I should not see review titled :title')]
    public function i_should_not_see_review_titled(string $title): void
    {
        Assert::false($this->show_page->has_review_titled($title));
    }
    #[When('/^I check (this product)\'s reviews$/')]
    public function i_check_this_product_s_reviews(Product_Interface $product): void
    {
        $this->product_reviews_index_page->open(['slug' => $product->get_slug()]);
    }
    #[Then('/^I should see (\d+) product reviews in the list$/')]
    public function i_should_see_number_of_product_reviews_in_the_list(int $count): void
    {
        Assert::same($this->product_reviews_index_page->count_reviews(), $count);
    }
    #[Then('I should not see review titled :title in the list')]
    public function i_should_not_see_review_titled_in_the_list(string $title): void
    {
        Assert::false($this->product_reviews_index_page->has_review_titled($title));
    }
    #[Then('/^I should be notified that there are no reviews$/')]
    public function i_should_be_notified_that_there_are_no_reviews(): void
    {
        Assert::true($this->product_reviews_index_page->has_no_reviews_message());
    }
    #[Then('I should see :rating as its average rating')]
    public function i_should_see_as_its_average_rating(string $rating): void
    {
        Assert::same($this->show_page->get_average_rating(), (float) $rating);
    }
    #[Then('/^I should(?:| also) see the product association "([^"]+)" with (products "[^"]+" and "[^"]+")$/')]
    public function i_should_see_the_product_association_with_products(string $product_association_name, array $products): void
    {
        Assert::true($this->show_page->has_association($product_association_name), sprintf('There should be an association named "%s" but it does not.', $product_association_name));
        foreach ($products as $product) {
            $this->assert_product_is_in_association($product->get_name(), $product_association_name);
        }
    }
    #[Then('/^I should not see the product association "([^"]+)"$/')]
    public function i_should_not_see_the_product_association_with_products(string $product_association_name): void
    {
        Assert::false($this->show_page->has_association($product_association_name), sprintf('There should not be an association named "%s" but it does.', $product_association_name));
    }
    #[Then('/^I should(?:| also) see the product association "([^"]+)" with (product "[^"]+")$/')]
    public function i_should_see_the_product_association_with_product(string $product_association_name, Product_Interface $product): void
    {
        $this->i_should_see_the_product_association_with_products($product_association_name, [$product]);
    }
    #[Then('/^I should(?:| also) not see the product association "([^"]+)" with (product "[^"]+")$/')]
    public function i_should_not_see_the_product_association_with_product(string $product_association_name, Product_Interface $product): void
    {
        Assert::true($this->show_page->has_association($product_association_name), sprintf('Association "%s" has not been found.', $product_association_name));
        $this->assert_product_is_not_in_association($product->get_name(), $product_association_name);
    }
    #[Then('/^average rating of (product "[^"]+") should be (\d+)$/')]
    public function this_product_average_rating_should_be(Product_Interface $product, string $average_rating): void
    {
        $this->show_page->try_to_open(['slug' => $product->get_slug()]);
        $this->i_should_see_as_its_average_rating($average_rating);
    }
    #[Then('they should have order like :firstProductName, :secondProductName and :thirdProductName')]
    public function they_should_have_order_like_and(string ...$product_names): void
    {
        Assert::true($this->index_page->has_products_in_order($product_names));
    }
    #[Then('I should be informed that the product does not exist')]
    public function i_should_be_informed_that_the_product_does_not_exist(): void
    {
        Assert::same($this->error_page->get_code(), 404);
    }
    #[Then('I should be redirected on the product list from taxon :taxon')]
    public function i_should_be_redirected_on_the_product_list_from_taxon(Taxon_Interface $taxon): void
    {
        $this->index_page->verify(['slug' => $taxon->get_slug()]);
    }
    #[Then('/^I should be able to select between (\d+) variants$/')]
    public function i_should_be_able_to_select_between_variants(int $count): void
    {
        Assert::count($this->show_page->get_variants_names(), $count);
    }
    #[Then('/^I should not be able to select the ("([^"]*)" variant)$/')]
    public function i_should_not_be_able_to_select_the_variant(Product_Variant_Interface $product_variant): void
    {
        Assert::true(!in_array($product_variant->get_name(), $this->show_page->get_variants_names(), true));
    }
    #[Then('/^I should not be able to select the "([^"]*)" ([^\s]+) option value$/')]
    public function i_should_not_be_able_to_select_the_option_value(string $option_value, string $option_name): void
    {
        Assert::false(in_array($option_value, $this->show_page->get_option_values($option_name), true));
    }
    #[Then('/^I should be able to select the "([^"]*)" and "([^"]*)" ([^\s]+) option values$/')]
    public function i_should_be_able_to_select_the_and_color_option_values(string $option_value1, string $option_value2, string $option_name): void
    {
        Assert::true(in_array($option_value1, $this->show_page->get_option_values($option_name), true));
        Assert::true(in_array($option_value2, $this->show_page->get_option_values($option_name), true));
    }
    #[Then('I should be informed that the taxon does not exist')]
    public function i_should_be_informed_that_the_taxon_does_not_exist(): void
    {
        Assert::same($this->error_page->get_code(), 404);
    }
    #[Then('I should see :firstMenuItem and :secondMenuItem in the vertical menu')]
    public function i_should_see_in_the_vertical_menu(string ...$menu_items): void
    {
        Assert::all_one_of($menu_items, $this->vertical_menu_element->get_menu_items());
    }
    #[Then('I should not see :firstMenuItem in the vertical menu')]
    public function i_should_not_see_in_the_vertical_menu(string ...$menu_items): void
    {
        $actual_menu_items = $this->vertical_menu_element->get_menu_items();
        foreach ($menu_items as $menu_item) {
            if (in_array($menu_item, $actual_menu_items)) {
                throw new \InvalidArgumentException(sprintf('Vertical menu should not contain %s element', $menu_item));
            }
        }
    }
    #[Then('I should not be able to navigate to parent taxon')]
    public function i_should_not_be_able_to_navigate_to_parent_taxon(): void
    {
        Assert::false($this->vertical_menu_element->can_navigate_to_parent_taxon());
    }
    #[Then('the visitor should see this variant is not discounted')]
    public function i_should_see_this_variant_is_not_discounted(): void
    {
        Assert::null($this->show_page->get_original_price());
    }
    #[Then('/^the visitor should see that the ("([^"]*)" variant) is not discounted$/')]
    public function the_visitor_should_see_that_the_variant_is_not_discounted(Product_Variant_Interface $variant): void
    {
        /** @var ProductInterface $product */
        $product = $variant->get_product();
        $this->i_open_product_page($product);
        $this->i_should_see_this_variant_is_not_discounted();
    }
    #[Then('I should not be able to click disabled main taxon :taxonName in the breadcrumb')]
    public function i_should_not_be_able_to_click_disabled_main_taxon_in_the_breacrumb(string $taxon_name): void
    {
        Assert::false($this->show_page->has_breadcrumb_link($taxon_name));
    }
    #[Then('/^I should see "([^"]+)" as its lowest price before the discount$/')]
    public function i_should_see_as_its_lowest_price_before_the_discount(string $lowest_price_before_discount): void
    {
        Assert::true($this->lowest_price_information_element->is_there_information_about_product_lowest_price_with_price($lowest_price_before_discount));
    }
    #[Then('I should not see information about its lowest price')]
    public function i_should_not_see_information_about_its_lowest_price(): void
    {
        Assert::false($this->lowest_price_information_element->is_there_information_about_product_lowest_price());
    }
    #[Then('I should be able to access product :product')]
    public function i_should_be_able_to_access_product(Product_Interface $product): void
    {
        $this->show_page->try_to_open(['slug' => $product->get_slug()]);
        Assert::true($this->show_page->is_open(['slug' => $product->get_slug()]));
    }
    #[Then('I should not be able to access product :product')]
    public function i_should_not_be_able_to_access_product(Product_Interface $product): void
    {
        $this->show_page->try_to_open(['slug' => $product->get_slug()]);
        Assert::false($this->show_page->is_open(['slug' => $product->get_slug()]));
    }
    /**
     * @throws \InvalidArgumentException
     */
    private function assert_product_is_in_association(string $product_name, string $product_association_name): void
    {
        Assert::true($this->show_page->has_product_in_association($product_name, $product_association_name), sprintf('There should be an associated product "%s" under association "%s" but it does not.', $product_name, $product_association_name));
    }
    private function assert_product_is_not_in_association(string $product_name, string $product_association_name): void
    {
        Assert::false($this->show_page->has_product_in_association($product_name, $product_association_name), sprintf('Association "%s" should not contain product "%s" but it does.', $product_name, $product_association_name));
    }
}
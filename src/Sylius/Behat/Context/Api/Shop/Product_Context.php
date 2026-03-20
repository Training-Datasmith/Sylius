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
namespace Sylius\Behat\Context\Api\Shop;

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\Common\Collections\Array_Collection;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Setter\Channel_Context_Setter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Product\Model\Product_Association_Type_Interface;
use Sylius\Component\Product\Model\Product_Variant_Interface;
use Sylius\Component\Product\Resolver\Product_Variant_Resolver_Interface;
use Symfony\Component\Http_Foundation\Request as HttpRequest;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final readonly class Product_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private Iri_Converter_Interface $iri_converter, private Channel_Context_Setter_Interface $channel_context_setter, private Request_Factory_Interface $request_factory, private Object_Manager $object_manager, private string $api_url_prefix, private Product_Variant_Resolver_Interface $product_variant_resolver)
    {
    }
    #[When('/^I check (this product)\'s details$/')]
    #[When('I view product :product')]
    #[When('customer view product :product')]
    public function i_view_product(Product_Interface $product): void
    {
        $this->object_manager->clear();
        // it's needed to clear the entity manager to receive the product images in correct order, as the images are using fallback order when added programmatically
        $this->client->show(Resources::PRODUCTS, $product->get_code());
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->product_variant_resolver->get_variant($product);
        $this->shared_storage->set('product', $product);
        $this->shared_storage->set('product_variant', $product_variant);
        $this->shared_storage->remove('product_attributes');
    }
    #[When('I try to reach nonexistent product')]
    public function i_try_to_reach_nonexistent_product(): void
    {
        $this->client->show(Resources::PRODUCTS, 'nonexistent');
    }
    #[When('I view product :product in the :localeCode locale')]
    #[When('/^I check (this product)\'s details in the ("([^"]+)" locale)$/')]
    #[When('/^I try to check (this product)\'s details in the ("([^"]+)" locale)$/')]
    public function i_view_product_in_the_locale(Product_Interface $product, string $locale_code): void
    {
        $this->shared_storage->set('current_locale_code', $locale_code);
        $this->i_view_product($product);
        $this->shared_storage->remove('current_locale_code');
    }
    #[When('I view product :product using slug')]
    public function i_view_product_using_slug(Product_Interface $product): void
    {
        $this->client->show_by_iri(sprintf('%s/shop/products-by-slug/%s', $this->api_url_prefix, $product->get_slug()));
        $this->shared_storage->set('product', $product);
    }
    #[Then('I should be redirected to :product product')]
    public function i_should_be_redirected_to_product(Product_Interface $product): void
    {
        $response = $this->client->get_last_response();
        Assert::eq($response->headers->get('Location'), sprintf('%s/shop/products/%s', $this->api_url_prefix, $product->get_code()));
    }
    #[When('I browse products from taxon :taxon')]
    #[When('I browse products')]
    public function i_browse_products_from_taxon(?Taxon_Interface $taxon = null): void
    {
        $this->client->index(Resources::PRODUCTS);
        if ($taxon !== null) {
            $this->client->add_filter('taxon', $this->iri_converter->get_iri_from_resource($taxon));
            $this->client->filter();
        }
    }
    #[When('I browse products from product taxon code :taxon')]
    public function i_browse_products_from_product_taxon_code(Taxon_Interface $taxon): void
    {
        $this->client->index(Resources::PRODUCTS);
        $this->client->add_filter('productTaxons.taxon.code', $taxon->get_code());
        $this->client->filter();
    }
    #[When('/^I browse products from ("([^"]+)" and "([^"]+)" taxons)$/')]
    public function i_browse_products_from_product_taxon_codes(iterable $taxons): void
    {
        $this->client->index(Resources::PRODUCTS);
        foreach ($taxons as $index => $taxon) {
            $this->client->add_filter('productTaxons.taxon.code[' . $index . ']', $taxon->get_code());
        }
        $this->client->filter();
    }
    #[When('I browse products from non existing taxon')]
    public function i_browse_products_from_non_existing_taxon(): void
    {
        $this->client->index(Resources::PRODUCTS);
        $this->client->add_filter('taxon', 'non-existing-taxon');
        $this->client->filter();
    }
    #[When('/^I sort products by the (oldest|newest) date first$/')]
    public function i_sort_products_by_the_date_first(string $sort_direction): void
    {
        $sort_direction = 'oldest' === $sort_direction ? 'asc' : 'desc';
        $this->client->sort(['createdAt' => $sort_direction]);
    }
    #[When('I sort products by the lowest price first')]
    public function i_sort_products_by_the_lowest_price_first(): void
    {
        $this->client->sort(['price' => 'asc']);
    }
    #[When('I sort products by the highest price first')]
    public function i_sort_products_by_the_highest_price_first(): void
    {
        $this->client->sort(['price' => 'desc']);
    }
    #[When('I sort products alphabetically from a to z')]
    public function i_sort_products_alphabetically_from_a_to_z(): void
    {
        $this->client->sort(['translation.name' => 'asc']);
    }
    #[When('I sort products alphabetically from z to a')]
    public function i_sort_products_alphabetically_from_z_to_a(): void
    {
        $this->client->sort(['translation.name' => 'desc']);
    }
    #[When('I clear filter')]
    public function i_clear_filter(): void
    {
        $this->client->clear_parameters();
        $this->client->filter();
    }
    #[When('I search for products with name :name')]
    public function i_search_for_products_with_name(string $name): void
    {
        $this->client->add_filter('translations.name', $name);
        $this->client->filter();
    }
    #[Then('I should see :rating as its average rating')]
    public function i_should_see_as_its_average_rating(float $rating): void
    {
        Assert::same(round($this->response_checker->get_value($this->client->get_last_response(), 'averageRating'), 2), $rating);
    }
    #[Then('I should see the product :name')]
    public function i_should_see_the_product(string $name): void
    {
        Assert::true($this->has_product_with_name($this->response_checker->get_collection($this->client->get_last_response()), $name));
    }
    #[Then('I should see a product with code :code')]
    public function i_should_see_a_product_with_code(string $code): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'code', $code));
    }
    #[Then('I should see a product with name :name')]
    public function i_should_see_a_product_with_name(string $name): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'name', $name));
    }
    #[Then('I should see that it is out of stock')]
    public function i_should_see_it_is_out_of_stock(): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->shared_storage->get('product_variant');
        $variant_response = $this->client->show_by_iri($this->iri_converter->get_iri_from_resource($product_variant));
        Assert::false($this->response_checker->get_value($variant_response, 'inStock'));
    }
    #[Then('I should not see the product :name')]
    public function i_should_not_see_the_product(string $name): void
    {
        Assert::false($this->has_product_with_name($this->response_checker->get_collection($this->client->get_last_response()), $name));
    }
    #[Then('/^I should see the product price ("[^"]+")$/')]
    #[Then('/^customer should see the product price ("[^"]+")$/')]
    public function i_should_see_the_product_price(int $price): void
    {
        /** @var ProductVariantInterface $checkedVariant */
        $checked_variant = $this->shared_storage->get('product_variant');
        $variant = $this->fetch_item_by_iri($this->iri_converter->get_iri_from_resource($checked_variant));
        Assert::same($variant['price'], $price);
        Assert::same($variant['code'], $checked_variant->get_code());
    }
    #[Then('/^I should see the product original price ("[^"]+")$/')]
    #[Then('/^customer should see the product original price ("[^"]+")$/')]
    public function i_should_see_the_product_original_price(int $original_price): void
    {
        /** @var ProductVariantInterface $checkedVariant */
        $checked_variant = $this->shared_storage->get('product_variant');
        $variant = $this->response_checker->get_response_content($this->client->get_last_response());
        Assert::same($variant['originalPrice'], $original_price);
        Assert::same($variant['code'], $checked_variant->get_code());
    }
    #[Then('I should see this product has no catalog promotion applied')]
    public function i_should_see_this_product_has_no_catalog_promotion_applied(): void
    {
        $variant = $this->response_checker->get_response_content($this->client->get_last_response());
        Assert::same($variant['originalPrice'], $variant['price']);
        Assert::key_not_exists($variant, 'appliedPromotions');
    }
    #[Then('I should not see any original price')]
    public function i_should_not_see_any_original_price(): void
    {
        $product = $this->response_checker->get_response_content($this->client->get_last_response());
        Assert::same($product['defaultVariantData']['originalPrice'], $product['defaultVariantData']['price']);
    }
    #[Then('/^I should see ("[^"]+" product) discounted from ("[^"]+") to ("[^"]+")$/')]
    public function i_should_see_product_discounted_from_to(Product_Interface $product, int $original_price, int $price): void
    {
        $last_response = $this->client->get_last_response();
        $this->i_should_see_the_product_with_price($product, $price);
        Assert::true($this->has_product_with_price($this->response_checker->get_collection($last_response), $original_price, $product->get_code(), 'originalPrice'), sprintf('There is no product with %s code and %s original price', $product->get_code(), $original_price));
    }
    #[Then('/^I should see the (product "[^"]+") with price ("[^"]+")$/')]
    public function i_should_see_the_product_with_price(Product_Interface $product, int $price): void
    {
        Assert::true($this->has_product_with_price($this->response_checker->get_collection($this->client->get_last_response()), $price, $product->get_code()), sprintf('There is no product with %s code and %s price', $product->get_code(), $price));
    }
    #[Then('I should see the product :product with short description :shortDescription')]
    public function i_should_see_the_product_with_short_description(Product_Interface $product, string $short_description): void
    {
        Assert::true($this->has_product_with_name_and_short_description($this->response_checker->get_collection($this->client->get_last_response()), $product->get_name(), $short_description), sprintf('There is no product with %s name and %s short description', $product->get_name(), $short_description));
    }
    #[Then('the first product on the list should have code :code')]
    public function the_first_product_on_the_list_should_have_code(string $code): void
    {
        $products = $this->response_checker->get_collection($this->client->get_last_response());
        Assert::same($products[0]['code'], $code);
    }
    #[Then('the last product on the list should have code :value')]
    public function the_last_product_on_the_list_should_have_code(string $code): void
    {
        $products = $this->response_checker->get_collection($this->client->get_last_response());
        Assert::same(end($products)['code'], $code);
    }
    #[Then('the first product on the list should have name :name')]
    public function the_first_product_on_the_list_should_have_name(string $name): void
    {
        $products = $this->response_checker->get_collection($this->client->get_last_response());
        Assert::same($products[0]['name'], $name);
    }
    #[Then('/^the first product on the list should have name "([^"]+)" and price ("[^"]+")$/')]
    public function the_first_product_on_the_list_should_have_name_and_price(string $name, int $price): void
    {
        $product = $this->response_checker->get_collection($this->client->resend())[0];
        Assert::same($product['name'], $name);
        Assert::same($product['defaultVariantData']['price'], $price);
    }
    #[Then('the last product on the list should have name :name')]
    public function the_last_product_on_the_list_should_have_name(string $name): void
    {
        $products = $this->response_checker->get_collection($this->client->get_last_response());
        Assert::same(end($products)['name'], $name);
    }
    #[Then('/^the last product on the list should have name "([^"]+)" and price ("[^"]+")$/')]
    public function the_last_product_on_the_list_should_have_name_and_price(string $name, int $price): void
    {
        $products = $this->response_checker->get_collection($this->client->resend());
        $product = end($products);
        Assert::same($product['name'], $name);
        Assert::same($product['defaultVariantData']['price'], $price);
    }
    #[When('/^I should see only (\d+) product(s)$/')]
    public function i_should_see_only_products(int $count): void
    {
        Assert::same(count($this->response_checker->get_collection($this->client->get_last_response())), $count, 'Number of products from response is different then expected');
    }
    #[Then('I should not see the product with name :name')]
    public function i_should_not_see_product_with_name(string $name): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->get_last_response(), 'name', $name));
    }
    #[Then('I should see the product name :name')]
    public function i_should_see_product_name(string $name): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'name', $name));
    }
    #[Then('the main image should be of type :type')]
    #[Then('I should be able to see a main image of type :type')]
    #[Then('the first thumbnail image should be of type :type')]
    public function the_image_should_be_of_type(string $type): void
    {
        $images = $this->response_checker->get_value($this->client->get_last_response(), 'images');
        Assert::same($images[0]['type'], $type);
    }
    #[Then('the second thumbnail image should be of type :type')]
    public function the_second_thumbnail_image_should_be_of_type(string $type): void
    {
        $images = $this->response_checker->get_value($this->client->get_last_response(), 'images');
        Assert::same($images[1]['type'], $type);
    }
    #[Then('/^I should not be able to view (this product) in the ("([^"]+)" locale)$/')]
    public function i_should_not_be_able_to_view_this_product_in_locale(Product_Interface $product, string $locale_code): void
    {
        Assert::false($this->response_checker->has_value($this->client->get_last_response(), 'name', $product->get_translation($locale_code)->get_name()));
    }
    #[Then('its current variant should be named :variantName')]
    public function its_current_variant_should_be_named(string $variant_name): void
    {
        $response = $this->client->get_last_response();
        $product_variant = $this->response_checker->get_value($response, 'variants');
        $request = $this->request_factory->custom($product_variant[0], Http_Request::METHOD_GET);
        $this->client->execute_custom_request($request);
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'name', $variant_name));
    }
    #[Then('I should see empty list of products')]
    public function i_should_see_empty_list_of_products(): void
    {
        Assert::same($this->response_checker->count_total_collection_items($this->client->get_last_response()), 0);
    }
    #[Then('I should see :count products in the list')]
    public function i_should_see_products_in_the_list(int $count): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('they should have order like :firstProductName, :secondProductName and :thirdProductName')]
    public function they_should_have_order_like_and(string ...$product_names): void
    {
        $product_names_from_response = new Array_Collection();
        foreach ($this->response_checker->get_collection($this->client->get_last_response()) as $product_item) {
            $product_names_from_response->add($product_item['name']);
        }
        foreach ($product_names_from_response as $key => $name) {
            Assert::same($name, $product_names[$key]);
        }
    }
    #[Then('/^the product price should be ("[^"]+")$/')]
    public function the_product_price_should_be(int $price): void
    {
        $default_variant = $this->response_checker->get_value($this->client->get_last_response(), 'defaultVariantData');
        Assert::same($default_variant['price'], $price);
    }
    #[Then('I should see the product description :description')]
    public function i_should_see_the_product_description(string $description): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'description'), $description);
    }
    #[Then('/^the visitor should(?:| still) see ("[^"]+") as the (price|original price) of the ("[^"]+" product) in the ("[^"]+" channel)$/')]
    public function the_visitor_should_see_as_the_price_of_the_product_in_the_channel(int $price, string $price_type, Product_Interface $product, Channel_Interface $channel): void
    {
        $this->shared_storage->set('token', null);
        $this->shared_storage->set('hostname', $channel->get_hostname());
        $this->channel_context_setter->set_channel($channel);
        Assert::true($this->has_product_with_price([$this->response_checker->get_response_content($this->client->show(Resources::PRODUCTS, $product->get_code()))], $price, null, String_Inflector::name_to_camel_case($price_type)));
    }
    #[Then('I should see a main image')]
    public function i_should_see_a_main_image(): void
    {
        Assert::true($this->has_product_with_main_image());
    }
    #[Then('/^I should not be able to select the "([^"]+)" ([^\s]+) option value$/')]
    public function i_should_not_be_able_to_select_the_option_value(string $option_value_value, string $option_name): void
    {
        Assert::false($this->has_product_option_with_name_and_value($option_name, $option_value_value));
    }
    #[Then('/^I should be able to select the "([^"]+)" and "([^"]+)" ([^\s]+) option values$/')]
    public function i_should_be_able_to_select_the_and_color_option_values(string $option_value_value1, string $option_value_value2, string $option_name): void
    {
        Assert::true($this->has_product_option_with_name_and_value($option_name, $option_value_value1));
        Assert::true($this->has_product_option_with_name_and_value($option_name, $option_value_value2));
    }
    #[Then('I should be able to select between :count variants')]
    public function i_should_be_able_to_select_between_variants(int $count): void
    {
        $response = $this->client->get_last_response();
        $variants = $this->response_checker->get_value($response, 'variants');
        Assert::count($variants, $count);
    }
    #[Then('I should not be able to select the :productVariantName variant')]
    public function i_should_not_be_able_to_select_the_variant(string $product_variant_name): void
    {
        $response = $this->client->get_last_response();
        $variants = $this->response_checker->get_value($response, 'variants');
        Assert::false($this->product_has_product_variant_with_name($variants, $product_variant_name));
    }
    #[Then('/^I should(?:| also) see the product association "([^"]+)" with (products "[^"]+" and "[^"]+")$/')]
    public function i_should_see_the_product_association_with_products_and(string $product_association_name, array $products): void
    {
        Assert::true($this->is_product_association_with_products_available($product_association_name, $products));
    }
    #[Then('/^I should(?:| also) see the product association "([^"]+)" with (product "[^"]+")$/')]
    public function i_should_see_the_product_association_with_product(string $product_association_name, Product_Interface $product): void
    {
        Assert::true($this->is_product_association_with_products_available($product_association_name, [$product]));
    }
    #[Then('/^I should(?:| also) not see the product association "([^"]+)" with (product "[^"]+")$/')]
    public function i_should_not_see_the_product_association_with_product(string $product_association_name, Product_Interface $product): void
    {
        Assert::false($this->is_product_association_with_products_available($product_association_name, [$product]));
    }
    #[Then('/^I should not see the product (association "([^"]+)")$/')]
    public function i_should_not_see_the_product_association(Product_Association_Type_Interface $product_association_type): void
    {
        $product_association_type_iri = $this->iri_converter->get_iri_from_resource($product_association_type);
        /** @var ProductInterface $product */
        $product = $this->shared_storage->get('product');
        $response = $this->client->show(Resources::PRODUCTS, $product->get_code());
        $associations = $this->response_checker->get_value($response, 'associations');
        foreach ($associations as $association) {
            $association_response = $this->client->show_by_iri($association);
            $association_type_iri = $this->response_checker->get_value($association_response, 'type');
            Assert::not_same($association_type_iri, $product_association_type_iri);
        }
    }
    #[Then('I should not see information about its lowest price')]
    public function i_should_not_see_information_about_its_lowest_price(): void
    {
        $product = $this->response_checker->get_response_content($this->client->get_last_response());
        $variant = $product['defaultVariantData'];
        Assert::key_exists($variant, 'lowestPriceBeforeDiscount');
        Assert::same($variant['lowestPriceBeforeDiscount'], null);
    }
    #[Then('/^I should see ("[^"]+") as its lowest price before the discount$/')]
    public function i_should_see_as_its_lowest_price_before_the_discount(int $lowest_price_before_discount): void
    {
        $product = $this->response_checker->get_response_content($this->client->get_last_response());
        $variant = $product['defaultVariantData'];
        Assert::key_exists($variant, 'lowestPriceBeforeDiscount');
        Assert::same($variant['lowestPriceBeforeDiscount'], $lowest_price_before_discount);
    }
    #[Then('I should be informed that the product does not exist')]
    public function i_should_be_informed_that_the_product_does_not_exist(): void
    {
        Assert::same($this->client->get_last_response()->get_status_code(), Response::HTTP_NOT_FOUND);
    }
    #[Then('/^I should be informed that the taxon does not exist$/')]
    public function i_should_be_informed_that_the_taxon_does_not_exist(): void
    {
        Assert::same($this->client->get_last_response()->get_status_code(), Response::HTTP_NOT_FOUND);
    }
    private function has_product_with_price(array $products, int $price, ?string $product_code = null, string $price_type = 'price'): bool
    {
        foreach ($products as $product) {
            if ($product_code !== null && $product['code'] !== $product_code) {
                continue;
            }
            foreach ($product['variants'] as $variant_iri) {
                $request = $this->request_factory->custom($variant_iri, Http_Request::METHOD_GET);
                $response = $this->client->execute_custom_request($request);
                /** @var int $variantPrice */
                $variant_price = $this->response_checker->get_value($response, $price_type);
                if ($price === $variant_price) {
                    return true;
                }
            }
        }
        return false;
    }
    private function has_product_with_name(array $products, string $name): bool
    {
        foreach ($products as $product) {
            if ($product['name'] === $name) {
                return true;
            }
        }
        return false;
    }
    private function has_product_with_name_and_short_description(array $products, string $name, string $short_description): bool
    {
        foreach ($products as $product) {
            if ($product['name'] === $name && $product['shortDescription'] === $short_description) {
                return true;
            }
        }
        return false;
    }
    private function has_product_option_with_name_and_value(string $expected_option_name, string $expected_option_value_value): bool
    {
        $product_variants = $this->response_checker->get_collection($this->client->index(Resources::PRODUCT_VARIANTS, ['product' => $this->iri_converter->get_iri_from_resource($this->shared_storage->get('product'))]));
        foreach ($product_variants as $product_variant) {
            foreach ($product_variant['optionValues'] as $option_value_iri) {
                $option_value_data = $this->fetch_item_by_iri($option_value_iri);
                $option_data = $this->fetch_item_by_iri($option_value_data['option']);
                if ($option_data['name'] === $expected_option_name && $option_value_data['value'] === $expected_option_value_value) {
                    return true;
                }
            }
        }
        return false;
    }
    private function product_has_product_variant_with_name(array $variants, string $variant_name): bool
    {
        foreach ($variants as $variant_iri) {
            if ($this->response_checker->has_value($this->client->show_by_iri($variant_iri), 'name', $variant_name)) {
                return true;
            }
        }
        return false;
    }
    private function has_product_with_main_image(): bool
    {
        $images = $this->response_checker->get_value($this->client->get_last_response(), 'images');
        return $images[0]['type'] === 'main' && $images[0]['path'];
    }
    private function has_associations_with_products(array $associations_iris, string $product_association_type_name, array $products): bool
    {
        try {
            $associated_products = $this->provide_associated_products_of_association_type_name($associations_iris, $product_association_type_name);
        } catch (\InvalidArgumentException) {
            return false;
        }
        foreach ($products as $product) {
            if (!$this->is_product_associated($product, $associated_products)) {
                return false;
            }
        }
        return true;
    }
    private function provide_associated_products_of_association_type_name(array $associations_iris, string $product_association_type_name): array
    {
        foreach ($associations_iris as $association_iri) {
            $association_response = $this->client->show_by_iri($association_iri);
            $association_type_iri = $this->response_checker->get_value($association_response, 'type');
            $association_type_response = $this->client->show_by_iri($association_type_iri);
            if ($this->response_checker->has_value($association_type_response, 'name', $product_association_type_name)) {
                return $this->response_checker->get_value($association_response, 'associatedProducts');
            }
        }
        throw new \InvalidArgumentException(sprintf('There is no product association with name %s.', $product_association_type_name));
    }
    private function is_product_associated(Product_Interface $product, array $associated_products): bool
    {
        $product_iri = $this->iri_converter->get_iri_from_resource($product);
        return in_array($product_iri, $associated_products, true);
    }
    private function fetch_item_by_iri(string $iri): array
    {
        return $this->response_checker->get_response_content($this->client->show_by_iri($iri));
    }
    private function is_product_association_with_products_available(string $product_association_name, array $associated_products): bool
    {
        /** @var ProductInterface $product */
        $product = $this->shared_storage->get('product');
        $response = $this->client->show(Resources::PRODUCTS, $product->get_code());
        $associations = $this->response_checker->get_value($response, 'associations');
        return $this->has_associations_with_products($associations, $product_association_name, $associated_products);
    }
}
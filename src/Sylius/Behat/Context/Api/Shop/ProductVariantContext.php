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
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Product\Model\Product_Option_Value_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Variant_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private Iri_Converter_Interface $iri_converter)
    {
    }
    #[When('I select :variant variant')]
    #[When('I view :variant variant')]
    #[When('I view :variant variant of the :product product')]
    public function i_select_variant(Product_Variant_Interface $variant): void
    {
        $this->shared_storage->set('variant', $variant);
        $this->client->show(Resources::PRODUCT_VARIANTS, $variant->get_code());
    }
    #[When('the visitor view :variant variant')]
    public function visitor_view_variant(Product_Variant_Interface $variant): void
    {
        $this->shared_storage->set('token', null);
        $this->client->show(Resources::PRODUCT_VARIANTS, $variant->get_code());
    }
    #[When('I view variants')]
    public function i_view_variants(): void
    {
        $response = $this->client->index(Resources::PRODUCT_VARIANTS);
        $this->shared_storage->set('response', $response);
    }
    #[When('/^I view variants of the ("[^"]+" product)$/')]
    public function i_view_variants_of_the_product(Product_Interface $product): void
    {
        $response = $this->client->index(Resources::PRODUCT_VARIANTS, ['product' => $this->iri_converter->get_iri_from_resource($product)]);
        $this->shared_storage->set('product_variant_collection', $this->response_checker->get_collection($response));
    }
    #[When('/^I filter (?:them|variants) by ("[^"]+" option value)$/')]
    public function i_filter_variants_by_option(Product_Option_Value_Interface $option_value): void
    {
        $this->client->add_filter('optionValues[]', $this->iri_converter->get_iri_from_resource($option_value));
        $response = $this->client->filter();
        $this->shared_storage->set('product_variant_collection', $this->response_checker->get_collection($response));
    }
    #[Then('/^(?:the|this) product variant price should be ("[^"]+")$/')]
    #[Then('/^I should see the variant price ("[^"]+")$/')]
    public function the_product_variant_price_should_be(int $price): void
    {
        $response = $this->response_checker->get_response_content($this->client->get_last_response());
        Assert::same($response['price'], $price);
    }
    #[Then('/^(?:the|this) product original price should be ("[^"]+")$/')]
    public function the_product_original_price_should_be(int $original_price): void
    {
        $response = $this->response_checker->get_response_content($this->client->get_last_response());
        Assert::same($response['originalPrice'], $original_price);
    }
    #[Then('/^I should see ("[^"]+" variant) is discounted from ("[^"]+") to ("[^"]+") with "([^"]+)" promotion$/')]
    #[Then('/^I should see (this variant) is discounted from ("[^"]+") to ("[^"]+") with "([^"]+)" promotion$/')]
    #[Then('/^I should see (this variant) is discounted from ("[^"]+") to ("[^"]+") with "([^"]+)" and "([^"]+)" promotions$/')]
    #[Then('/^I should see (this variant) is discounted from ("[^"]+") to ("[^"]+") with "([^"]+)", "([^"]+)" and "([^"]+)" promotions$/')]
    #[Then('/^I should see (this variant) is discounted from ("[^"]+") to ("[^"]+") with "([^"]+)", "([^"]+)", "([^"]+)" and "([^"]+)" promotions$/')]
    public function i_should_see_variant_is_discounted_from_to_with_promotions(Product_Variant_Interface $variant, int $original_price, int $price, string ...$promotions_names): void
    {
        $content = $this->find_variant($variant);
        Assert::same($content['price'], $price);
        Assert::same($content['originalPrice'], $original_price);
        foreach ($content['appliedPromotions'] as $promotion_iri) {
            $catalog_promotion_content = $this->response_checker->get_response_content($this->client->show_by_iri($promotion_iri));
            Assert::in_array($catalog_promotion_content['label'], $promotions_names);
        }
    }
    #[Then('/^I should see (this variant) is discounted from ("[^"]+") to ("[^"]+") with ([^"]+) promotions$/')]
    public function i_should_see_variant_is_discounted_from_to_with_number_of_promotions(Product_Variant_Interface $variant, int $original_price, int $price, int $number_of_promotions): void
    {
        $content = $this->find_variant($variant);
        Assert::same($content['price'], $price);
        Assert::same($content['originalPrice'], $original_price);
        Assert::count($content['appliedPromotions'], $number_of_promotions);
    }
    #[Then('/^I should see (this variant) is discounted from ("[^"]+") to ("[^"]+") with only "([^"]+)" promotion$/')]
    public function i_should_see_variant_is_discounted_from_to_with_only_promotion(Product_Variant_Interface $variant, int $original_price, int $price, string $promotion_name): void
    {
        $variant_content = $this->find_variant($variant);
        $catalog_promotion_response = $this->client->show_by_iri($variant_content['appliedPromotions'][0]);
        $catalog_promotion_content = $this->response_checker->get_response_content($catalog_promotion_response);
        Assert::count($variant_content['appliedPromotions'], 1);
        Assert::same($variant_content['price'], $price);
        Assert::same($variant_content['originalPrice'], $original_price);
        Assert::same($catalog_promotion_content['label'], $promotion_name);
    }
    #[Then('/^the visitor should(?:| still) see that the ("[^"]+" variant) is discounted from ("[^"]+") to ("[^"]+") with "([^"]+)" promotion$/')]
    public function the_visitor_should_see_that_the_variant_is_discounted_with_promotion(Product_Variant_Interface $product_variant, int $original_price, int $price, string $promotion_name): void
    {
        $this->shared_storage->set('token', null);
        $this->client->show(Resources::PRODUCT_VARIANTS, $product_variant->get_code());
        $this->i_should_see_variant_is_discounted_from_to_with_promotions($product_variant, $original_price, $price, $promotion_name);
    }
    #[Then('/^the visitor should(?:| still) see that the ("[^"]+" variant) is discounted from ("[^"]+") to ("[^"]+") with ([^"]+) promotions$/')]
    public function the_visitor_should_see_variant_is_discounted_from_to_with_number_of_promotions(Product_Variant_Interface $variant, int $original_price, int $price, int $number_of_promotions): void
    {
        $this->shared_storage->set('token', null);
        $this->client->show(Resources::PRODUCT_VARIANTS, $variant->get_code());
        $this->i_should_see_variant_is_discounted_from_to_with_number_of_promotions($variant, $original_price, $price, $number_of_promotions);
    }
    #[Then('/^I should see ("[^"]+" variant) is not discounted$/')]
    public function i_should_see_variant_is_not_discounted(Product_Variant_Interface $variant): void
    {
        $response = $this->shared_storage->has('response') ? $this->shared_storage->get('response') : $this->client->get_last_response();
        $items = $this->response_checker->get_collection_items_with_value($response, 'code', $variant->get_code());
        $item = array_pop($items);
        Assert::key_not_exists($item, 'appliedPromotions');
    }
    #[Then('/^the visitor should see (this variant) is not discounted$/')]
    #[Then('/^the visitor should see that the ("[^"]+" variant) is not discounted$/')]
    public function the_visitor_should_see_that_the_variant_is_not_discounted(Product_Variant_Interface $variant): void
    {
        $this->shared_storage->set('token', null);
        $this->i_should_see_this_variant_is_not_discounted($variant);
    }
    #[Then('/^I should see (this variant) is not discounted$/')]
    public function i_should_see_this_variant_is_not_discounted(Product_Variant_Interface $variant): void
    {
        $content = $this->response_checker->get_response_content($this->client->show(Resources::PRODUCT_VARIANTS, $variant->get_code()));
        Assert::key_not_exists($content, 'appliedPromotions');
    }
    #[Then('/^("[^"]+" variant) and ("[^"]+" variant) should be discounted$/')]
    #[Then('/^("[^"]+" variant) should be discounted$/')]
    public function variant_and_variant_should_be_discounted(Product_Variant_Interface ...$variants): void
    {
        $this->shared_storage->set('token', null);
        /** @var ProductVariantInterface $variant */
        foreach ($variants as $variant) {
            $content = $this->response_checker->get_response_content($this->client->show(Resources::PRODUCT_VARIANTS, $variant->get_code()));
            Assert::key_exists($content, 'appliedPromotions', sprintf('%s variant should be discounted', $variant->get_name()));
        }
    }
    #[Then('/^("[^"]+" variant) and ("[^"]+" variant) should not be discounted$/')]
    #[Then('/^("[^"]+" variant) should not be discounted$/')]
    public function variant_and_variant_should_not_be_discounted(Product_Variant_Interface ...$variants): void
    {
        $this->shared_storage->set('token', null);
        /** @var ProductVariantInterface $variant */
        foreach ($variants as $variant) {
            $content = $this->response_checker->get_response_content($this->client->show(Resources::PRODUCT_VARIANTS, $variant->get_code()));
            Assert::key_not_exists($content, 'appliedPromotions', sprintf('%s variant should not be discounted', $variant->get_name()));
        }
    }
    #[Then('I should not see :variant variant')]
    public function i_should_not_see_variant(Product_Variant_Interface $variant): void
    {
        $response = $this->client->show(Resources::PRODUCT_VARIANTS, $variant->get_code());
        Assert::same($response->get_status_code(), 404, sprintf('%s variant should be disabled', $variant->get_name()));
    }
    #[Then('/^I should see ("([^"]+)", "([^"]+)" and "([^"]+)" variants)$/')]
    public function variant_and_variant_should_be_visible(array $variants): void
    {
        $this->shared_storage->set('token', null);
        /** @var ProductVariantInterface $variant */
        foreach ($variants as $variant) {
            $content = $this->response_checker->get_response_content($this->client->show(Resources::PRODUCT_VARIANTS, $variant->get_code()));
            Assert::same($content['name'], $variant->get_name(), sprintf('%s variant should be visible', $variant->get_name()));
        }
    }
    #[Then('/^I should see variant with ("[^"]+" option) and ("[^"]+" option value) priced at ("[^"]+") at (\d)(?:st|nd|rd|th) position$/')]
    public function i_should_see_variant_with_option_priced_at_at_position(string $expected_option_name, string $expected_option_value_value, int $price, int $position): void
    {
        $variants = $this->shared_storage->get('product_variant_collection');
        Assert::greater_than(count($variants), $position - 1, 'There are less variants than expected');
        $variant = $variants[$position - 1];
        Assert::same($variant['price'], $price);
        Assert::true($this->is_option_value_in_variant($variant['optionValues'], $expected_option_name, $expected_option_value_value), sprintf('There is no variant with "%s" option value', $expected_option_value_value));
    }
    #[Then('/^I should not see variant with "([^"]+)" option "([^"]+)"$/')]
    public function i_should_not_see_variant_with_option_priced_at(string $expected_option_name, string $expected_option_value_value): void
    {
        $variants = $this->shared_storage->get('product_variant_collection');
        foreach ($variants as $variant) {
            Assert::false($this->is_option_value_in_variant($variant['optionValues'], $expected_option_name, $expected_option_value_value), sprintf('There is a variant with "%s" option value', $expected_option_value_value));
        }
    }
    #[Then('I should not see any variants')]
    public function i_should_not_see_any_variants(): void
    {
        Assert::same(count($this->shared_storage->get('product_variant_collection')), 0);
    }
    private function find_variant(?Product_Variant_Interface $variant): array
    {
        $response = $this->shared_storage->has('response') ? $this->shared_storage->get('response') : $this->client->get_last_response();
        if ($variant !== null && $this->response_checker->has_value($response, '@type', 'hydra:Collection')) {
            $return_value = $this->response_checker->get_collection_items_with_value($response, 'code', $variant->get_code());
            return array_shift($return_value);
        }
        return $this->response_checker->get_response_content($response);
    }
    private function is_option_value_in_variant(array $option_value_iris, string $expected_option_name, string $expected_option_value_value): bool
    {
        foreach ($option_value_iris as $option_value_iri) {
            $parts = explode('/', (string) $option_value_iri);
            $product_option_code = $parts[5];
            $product_option_value_code = $parts[7];
            if (String_Inflector::name_to_uppercase_code($expected_option_name) == String_Inflector::name_to_uppercase_code($product_option_code)) {
                return String_Inflector::name_to_uppercase_code($product_option_value_code) == String_Inflector::name_to_uppercase_code($expected_option_value_value);
            }
        }
        return false;
    }
}
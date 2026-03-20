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
namespace Sylius\Behat\Context\Api\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Product\Model\Product_Option_Interface;
use Sylius\Component\Product\Model\Product_Option_Value_Interface;
use Sylius\Component\Product\Resolver\Product_Variant_Resolver_Interface;
use Sylius\Component\Shipping\Model\Shipping_Category_Interface;
use Webmozart\Assert\Assert;
final class Managing_Product_Variants_Context implements Context
{
    use Validation_Trait;
    private const FIRST_COLLECTION_ITEM = 0;
    private const HUGE_NUMBER = 2147483647;
    public function __construct(private Product_Variant_Resolver_Interface $variant_resolver, private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter)
    {
    }
    #[When('/^I want to create a new variant of (this product)$/')]
    public function i_want_to_create_a_new_product_variant(Product_Interface $product): void
    {
        $this->client->build_create_request(Resources::PRODUCT_VARIANTS);
        $this->client->add_request_data('product', $this->iri_converter->get_iri_from_resource($product));
    }
    #[When('I specify its code as :code')]
    public function i_specify_its_code_as(string $code): void
    {
        $this->client->add_request_data('code', $code);
    }
    #[When('I name it :name in :localeCode')]
    public function i_name_it_in(string $name, string $locale_code): void
    {
        $this->client->add_request_data('translations', [$locale_code => ['name' => $name]]);
    }
    #[When('/^I set its price to ("[^"]+") for ("[^"]+" channel)$/')]
    public function i_set_its_price_to_for_channel(int $price, Channel_Interface $channel): void
    {
        $this->client->add_request_data('channelPricings', [$channel->get_code() => ['price' => $price, 'channelCode' => $channel->get_code()]]);
    }
    #[When('I set its price to a huge number for the :channel channel')]
    public function i_set_its_price_to_huge_number_for_the_channel(Channel_Interface $channel): void
    {
        $this->i_set_its_price_to_for_channel(self::HUGE_NUMBER, $channel);
    }
    #[When('I set its original price to a huge number for the :channel channel')]
    public function i_set_its_original_price_to_huge_number_for_the_channel(Channel_Interface $channel): void
    {
        $this->i_set_its_original_price_to_for_channel(self::HUGE_NUMBER, $channel);
    }
    #[When('I set its minimum price to a huge number for the :channel channel')]
    public function i_set_its_minimum_price_as_out_of_range_value_for_channel(Channel_Interface $channel): void
    {
        $this->i_set_its_minimum_price_to_for_channel(self::HUGE_NUMBER, $channel);
    }
    #[When('I remove its price from :channel channel')]
    public function i_remove_its_price_for_channel(Channel_Interface $channel): void
    {
        $content = $this->client->get_content();
        $content['channelPricings'][$channel->get_code()]['price'] = null;
        $this->client->set_request_data($content);
    }
    #[When('I do not set its price')]
    #[When('I do not specify its code')]
    #[When('I do not set its :optionName option')]
    #[When('I do not set its :firstOptionName and :secondOptionName options')]
    public function i_do_not_set_value(): void
    {
        // Intentionally left blank
    }
    #[When('I do not specify its current stock')]
    public function i_do_not_specify_its_current_stock(): void
    {
        $this->client->add_request_data('onHand', null);
    }
    #[When('/^I set its original price to ("[^"]+") for ("[^"]+" channel)$/')]
    public function i_set_its_original_price_to_for_channel(int $original_price, Channel_Interface $channel): void
    {
        $this->client->add_request_data('channelPricings', [$channel->get_code() => ['originalPrice' => $original_price, 'channelCode' => $channel->get_code()]]);
    }
    #[When('/^I set its minimum price to ("[^"]+") for ("[^"]+" channel)$/')]
    public function i_set_its_minimum_price_to_for_channel(int $minimum_price, Channel_Interface $channel): void
    {
        $content = $this->client->get_content();
        $content['channelPricings'][$channel->get_code()]['minimumPrice'] = $minimum_price;
        $this->client->update_request_data($content);
    }
    #[When('I( try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I want to modify the :variant product variant')]
    public function i_want_to_modify_product_variant(Product_Variant_Interface $variant): void
    {
        $this->client->build_update_request(Resources::PRODUCT_VARIANTS, $variant->get_code());
    }
    #[When('/^I change its price to ("[^"]+") for ("[^"]+" channel)$/')]
    public function i_change_its_price_to_for_channel(int $original_price, Channel_Interface $channel): void
    {
        $this->client->add_request_data('channelPricings', [$channel->get_code() => ['price' => $original_price, 'channelCode' => $channel->get_code()]]);
    }
    #[When('I set its :optionName option to :optionValue')]
    public function i_set_its_option_as(string $option_name, Product_Option_Value_Interface $option_value): void
    {
        $content = $this->client->get_content();
        $content['optionValues'][] = $this->iri_converter->get_iri_from_resource_in_section($option_value, 'admin');
        $this->client->set_request_data($content);
    }
    #[When('I change its :productOption option to :productOptionValue')]
    public function i_change_its_option_to(Product_Option_Interface $product_option, Product_Option_Value_Interface $product_option_value): void
    {
        $content = $this->client->get_content();
        foreach ($content['optionValues'] as $key => $option_value_iri) {
            /** @var ProductOptionValueInterface $currentOptionValue */
            $current_option_value = $this->iri_converter->get_resource_from_iri($option_value_iri);
            if ($current_option_value->get_option_code() === $product_option->get_code()) {
                unset($content['optionValues'][$key]);
            }
        }
        $content['optionValues'][] = $this->iri_converter->get_iri_from_resource($product_option_value);
        $this->client->set_request_data($content);
    }
    #[When('I add additionally :productOptionValue value as :productOptionName option')]
    public function i_add_additionally_value_as_option(Product_Option_Value_Interface $product_option_value, string $product_option_name): void
    {
        $content = $this->client->get_content();
        $content['optionValues'][] = $this->iri_converter->get_iri_from_resource($product_option_value);
        $this->client->set_request_data($content);
    }
    #[When('I set its shipping category as :shippingCategory')]
    public function i_set_its_shipping_category_as(Shipping_Category_Interface $shipping_category): void
    {
        $this->client->add_request_data('shippingCategory', $this->iri_converter->get_iri_from_resource($shipping_category));
    }
    #[When('I do not want to have shipping required for this product variant')]
    public function i_do_not_want_to_have_shipping_required_for_this_product_variant(): void
    {
        $this->client->add_request_data('shippingRequired', false);
    }
    #[When('/^I want to view all variants of (this product)$/')]
    #[When('/^I view(?:| all) variants of the (product "[^"]+")(?:| again)$/')]
    public function i_want_to_view_all_variants_of_this_product(Product_Interface $product): void
    {
        $this->client->index(Resources::PRODUCT_VARIANTS);
        $this->client->add_filter('product', $this->iri_converter->get_iri_from_resource($product));
        $this->client->add_filter('order[position]', 'asc');
        $this->client->filter();
    }
    #[When('/^I delete the ("[^"]+" variant of product "[^"]+")$/')]
    #[When('/^I try to delete the ("[^"]+" variant of product "[^"]+")$/')]
    public function i_delete_the_variant_of_product(Product_Variant_Interface $product_variant): void
    {
        $this->client->delete(Resources::PRODUCT_VARIANTS, $product_variant->get_code());
    }
    #[When('I disable it')]
    public function i_disable_it(): void
    {
        $this->client->update_request_data(['enabled' => false]);
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->client->update_request_data(['enabled' => true]);
    }
    #[When('I disable its inventory tracking')]
    public function i_disable_its_tracking(): void
    {
        $this->client->update_request_data(['tracked' => false]);
    }
    #[When('I enable its inventory tracking')]
    public function i_enable_its_tracking(): void
    {
        $this->client->update_request_data(['tracked' => true]);
    }
    #[When('I set its height, width, depth and weight to :value')]
    public function i_set_its_dimensions_to(float $value): void
    {
        $this->client->update_request_data(['height' => $value, 'width' => $value, 'depth' => $value, 'weight' => $value]);
    }
    #[When('I change its quantity of inventory to :amount')]
    public function i_change_its_quantity_of_inventory_to(int $amount): void
    {
        $this->client->update_request_data(['onHand' => $amount]);
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), sprintf('Product Variant could not be created: %s', $this->response_checker->get_error($this->client->get_last_response())));
    }
    #[Then('the :productVariantCode variant of the :productName product should appear in the store')]
    public function the_product_variant_should_appear_in_the_shop(string $product_variant_code, string $product_name): void
    {
        $response = $this->client->index(Resources::PRODUCT_VARIANTS);
        Assert::true($this->response_checker->has_item_with_value($response, 'code', $product_variant_code));
    }
    #[Then('the :productVariantCode variant of the :productName product should not appear in the store')]
    public function the_product_variant_should_not_appear_in_the_shop(string $product_variant_code, string $product_name): void
    {
        $response = $this->client->index(Resources::PRODUCT_VARIANTS);
        Assert::false($this->response_checker->has_item_with_value($response, 'code', $product_variant_code));
    }
    #[Then('/^the (?:variant with code "[^"]+") should be named "([^"]+)" in ("([^"]+)" locale)$/')]
    public function the_variant_with_code_should_be_named_in(string $name, string $locale_code): void
    {
        $response = $this->response_checker->get_collection($this->client->index(Resources::PRODUCT_VARIANTS));
        $expected_translation = ['name' => $name];
        $translation_in_locale = $response[self::FIRST_COLLECTION_ITEM]['translations'][$locale_code];
        Assert::all_in_array($expected_translation, $translation_in_locale, sprintf('Expected translation %s, got %s', $expected_translation['name'], $translation_in_locale['name']));
    }
    #[Then('/^the variant with code "([^"]+)" should be priced at ("[^"]+") for (channel "[^"]+")$/')]
    #[Then('I should not have configured price for :channel channel')]
    public function the_variant_with_code_should_be_priced_at_for_channel(?string $variant_code = null, ?int $price = null, ?Channel_Interface $channel = null): void
    {
        $response = $this->response_checker->get_collection($this->client->index(Resources::PRODUCT_VARIANTS));
        Assert::same($response[self::FIRST_COLLECTION_ITEM]['channelPricings'][$channel->get_code()]['price'], $price);
    }
    #[Then('/^the variant with code "([^"]+)" should have an original price of ("[^"]+") for (channel "[^"]+")$/')]
    #[Then('/^the variant with code "([^"]+)" should be originally priced at ("[^"]+") for (channel "[^"]+")$/')]
    public function the_variant_with_code_should_have_an_original_price_of_for_channel(?string $variant_code, int $original_price, Channel_Interface $channel): void
    {
        $response = $this->response_checker->get_collection($this->client->index(Resources::PRODUCT_VARIANTS));
        Assert::same($response[self::FIRST_COLLECTION_ITEM]['channelPricings'][$channel->get_code()]['originalPrice'], $original_price);
    }
    #[Then('/^I should have original price equal to ("[^"]+") in ("[^"]+" channel)$/')]
    public function i_should_have_original_price_equal_to_in_channel(int $original_price, Channel_Interface $channel): void
    {
        $this->the_variant_with_code_should_have_an_original_price_of_for_channel(null, $original_price, $channel);
    }
    #[Then('/^the (variant with code "[^"]+") should have minimum price ("[^"]+") for (channel "([^"]+)")$/')]
    public function the_variant_with_code_should_have_minimum_price_for_channel(Product_Variant_Interface $product_variant, int $minimum_price, Channel_Interface $channel): void
    {
        $response = $this->response_checker->get_collection($this->client->index(Resources::PRODUCT_VARIANTS));
        Assert::same($response[self::FIRST_COLLECTION_ITEM]['channelPricings'][$channel->get_code()]['minimumPrice'], $minimum_price);
    }
    #[Then('/^the (variant with code "[^"]+") should not have shipping required$/')]
    public function the_variant_with_code_should_not_have_shipping_required(Product_Variant_Interface $product_variant): void
    {
        Assert::false($this->response_checker->get_value($this->client->get_last_response(), 'shippingRequired'));
    }
    #[Then('I should see :amount variant(s) in the list')]
    public function i_should_see_number_of_product_variants_in_the_list(int $amount): void
    {
        Assert::count($this->response_checker->get_collection($this->client->get_last_response()), $amount);
    }
    #[Then('I should see that the :productVariant variant is not tracked')]
    public function i_should_see_that_variant_is_not_tracked(Product_Variant_Interface $product_variant): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->get_last_response(), ['code' => $product_variant->get_code(), 'tracked' => false]));
    }
    #[Then('I should see that the :productVariant variant has zero on hand quantity')]
    public function i_should_see_that_the_variant_has_zero_on_hand_quantity(Product_Variant_Interface $product_variant): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->get_last_response(), ['code' => $product_variant->get_code(), 'onHand' => 0]));
    }
    #[Then('I should see that the :productVariant variant is enabled')]
    public function i_should_see_that_the_variant_is_enabled(Product_Variant_Interface $product_variant): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->get_last_response(), ['code' => $product_variant->get_code(), 'enabled' => true]));
    }
    #[Then('this variant should be disabled')]
    public function this_variant_should_be_disabled(): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'enabled', false));
    }
    #[Then('this variant should be enabled')]
    public function this_variant_should_be_enabled(): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'enabled', true));
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Product variant could not be deleted');
    }
    #[Then('/^(this variant) should not exist in the product catalog$/')]
    public function this_product_variant_should_not_exist_in_the_product_catalog(Product_Variant_Interface $product_variant): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::PRODUCT_VARIANTS), 'code', $product_variant->get_code()), 'The product variant still exists, but it should not');
    }
    #[Then('/^(this variant) should still exist in the product catalog$/')]
    public function this_product_variant_should_still_exist_in_the_product_catalog(Product_Variant_Interface $product_variant): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::PRODUCT_VARIANTS), 'code', $product_variant->get_code()), 'The product variant does not exist, but it should');
    }
    #[Then('I should be notified that this variant is in use and cannot be deleted')]
    public function i_should_be_notified_that_this_variant_is_in_use_and_cannot_be_deleted(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot delete, the product variant is in use.');
    }
    #[Then('inventory of this variant should not be tracked')]
    public function inventory_of_this_variant_should_not_be_tracked(): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'tracked', false));
    }
    #[Then('inventory of this variant should be tracked')]
    public function inventory_of_this_variant_should_be_tracked(): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'tracked', true));
    }
    #[Then('I should be notified that prices in :channel channel must be defined')]
    public function i_should_be_notified_that_prices_in_all_channels_must_be_defined(Channel_Interface $channel): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('channelPricings[%s].price: You must define price.', $channel->get_code()));
    }
    #[Then('I should be notified that price cannot be lower than 0')]
    public function i_should_be_notified_that_price_cannot_be_lower_than_zero(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Price cannot be lower than 0.');
    }
    #[Then('I should be notified that price cannot be greater than max value allowed')]
    public function i_should_be_notified_that_price_cannot_be_greater_than_max_value_allowed(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Value must be less than %s.', self::HUGE_NUMBER));
    }
    #[Then('I should be notified that code is required')]
    public function i_should_be_notified_that_code_is_required(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Please enter the code.');
    }
    #[Then('I should be notified that current stock is required')]
    public function i_should_be_notified_that_current_stock_is_required(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The type of the "onHand" attribute must be "int", "NULL" given.');
    }
    #[Then('the :product product should have no variants')]
    public function the_product_should_have_no_variants(Product_Interface $product): void
    {
        $this->i_want_to_view_all_variants_of_this_product($product);
        $this->i_should_see_number_of_product_variants_in_the_list(0);
    }
    #[Then('the :product product should have only one variant')]
    public function the_product_should_have_only_one_variant(Product_Interface $product): void
    {
        $this->i_want_to_view_all_variants_of_this_product($product);
        $this->i_should_see_number_of_product_variants_in_the_list(1);
    }
    #[Then('/^(\d+) units of (this product) should be (on hand|on hold)$/')]
    public function units_of_this_product_should_be_on(int $quantity, Product_Interface $product, string $field): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $this->variant_resolver->get_variant($product);
        Assert::is_instance_of($variant, Product_Variant_Interface::class);
        $this->i_want_to_view_all_variants_of_this_product($product);
        $this->the_variant_should_have_items_on($variant, $quantity, $field);
    }
    #[Then('/^there should be no units of (this product) on hold$/')]
    public function there_should_be_no_units_of_this_product_on_hold(Product_Interface $product): void
    {
        $this->units_of_this_product_should_be_on(0, $product, 'on hold');
    }
    #[Then('/^the ("[^"]+" variant) should have (\d+) items (on hand|on hold)$/')]
    #[Then('/^the (variant "[^"]+") should have (\d+) items (on hand|on hold)$/')]
    public function the_variant_should_have_items_on(Product_Variant_Interface $variant, int $quantity, string $field): void
    {
        $variants_data = $this->response_checker->get_collection_items_with_value($this->client->get_last_response(), 'code', $variant->get_code());
        $variant_data = array_pop($variants_data);
        Assert::same((int) $variant_data[String_Inflector::name_to_camel_case($field)], $quantity);
    }
    #[Then('/^the ("[^"]+" variant of product "[^"]+") should have (\d+) items (on hand|on hold)$/')]
    #[Then('/^the ("[^"]+" variant of "[^"]+" product) should have (\d+) items (on hand|on hold)$/')]
    #[Then('/^(this variant) should have a (\d+) item currently in stock$/')]
    public function the_variant_of_product_should_have_items_on(Product_Variant_Interface $variant, int $quantity, string $field = 'on hand'): void
    {
        $actual_quantity = $this->response_checker->get_value($this->client->show(Resources::PRODUCT_VARIANTS, $variant->get_code()), String_Inflector::name_to_camel_case($field));
        Assert::same((int) $actual_quantity, $quantity);
    }
    #[Then('I should be notified that code has to be unique')]
    public function i_should_be_notified_that_code_has_to_be_unique(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Product variant code must be unique.');
    }
    #[Then('I should be notified that this variant already exists')]
    public function i_should_be_notified_that_this_variant_already_exists(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Variant with this option set already exists.');
    }
    #[Then('I should be notified that height, width, depth and weight cannot be lower than 0')]
    public function i_should_be_notified_that_is_height_width_depth_and_weight_cannot_be_lower_than_zero(): void
    {
        $errors = $this->response_checker->get_error($this->client->get_last_response());
        Assert::contains($errors, 'Height cannot be negative.');
        Assert::contains($errors, 'Width cannot be negative.');
        Assert::contains($errors, 'Depth cannot be negative.');
        Assert::contains($errors, 'Weight cannot be negative.');
    }
    #[Then('the variant :productVariantName should have :optionName option as :optionValue')]
    public function the_variant_should_have_option_as(string $product_variant_name, string $option_name, Product_Option_Value_Interface $option_value): void
    {
        Assert::true($this->response_checker->has_value_in_collection($this->client->get_last_response(), 'optionValues', $this->iri_converter->get_iri_from_resource_in_section($option_value, 'admin')));
    }
    #[Then('I should be notified that the variant can have only one value configured for a single option')]
    public function i_should_be_notified_that_the_variant_can_have_only_one_value_configured_for_a_single_option(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The product variant can have only one value configured for a single option.');
    }
    #[Then('I should be notified that required options have not been configured')]
    public function i_should_be_notified_that_required_options_have_not_been_configured(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The product variant must have configured values for all options chosen on the product.');
    }
    #[Then('I should be notified that on hand quantity must be greater than the number of on hold units')]
    public function i_should_be_notified_that_on_hand_quantity_must_be_greater_than_the_number_of_on_hold_units(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'On hand must be greater than the number of on hold units');
    }
}
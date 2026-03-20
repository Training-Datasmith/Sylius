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
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request_Builder;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Attribute\Model\Attribute_Value_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Taxon_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Sylius\Component\Product\Model\Product_Association_Interface;
use Sylius\Component\Product\Model\Product_Association_Type_Interface;
use Sylius\Component\Product\Model\Product_Attribute_Interface;
use Sylius\Component\Product\Model\Product_Option_Interface;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final readonly class Managing_Products_Context implements Context
{
    use Validation_Trait;
    public const SORT_TYPES = ['ascending' => 'asc', 'descending' => 'desc'];
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Shared_Storage_Interface $shared_storage, private string $api_url_prefix)
    {
    }
    #[Given('the products are already sorted :sortType by name')]
    #[When('I start sorting products by name')]
    #[When('I sort the products :sortType by name')]
    #[When('I switch the way products are sorted :sortType by name')]
    public function i_start_sorting_products_by_name(string $sort_type = 'ascending'): void
    {
        $this->client->sort(['translation.name' => self::SORT_TYPES[$sort_type], 'localeCode' => $this->get_admin_locale_code()]);
        $this->shared_storage->set('response', $this->client->get_last_response());
    }
    #[Given('I am browsing products')]
    #[When('I browse products')]
    #[When('I want to browse products')]
    public function i_want_to_browse_products(): void
    {
        $this->client->index(Resources::PRODUCTS);
        $this->shared_storage->set('response', $this->client->get_last_response());
    }
    #[When('I change my locale to :localeCode')]
    public function i_switch_the_locale_to_the_locale(string $locale_code): void
    {
        /** @var AdminUserInterface $adminUser */
        $admin_user = $this->shared_storage->get('administrator');
        $this->client->build_update_request(Resources::ADMINISTRATORS, (string) $admin_user->get_id());
        $this->client->update_request_data(['localeCode' => $locale_code]);
        $this->client->update();
    }
    #[When('I want to create a new configurable product')]
    public function i_want_to_create_a_new_configurable_product(): void
    {
        $this->client->build_create_request(Resources::PRODUCTS);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->client->add_request_data('code', $code);
    }
    #[When('I do not name it')]
    public function i_do_not_name_it(): void
    {
        // Intentionally left blank.
    }
    #[When('I name it :name in :localeCode locale')]
    #[When('I rename it to :name in :localeCode locale')]
    public function i_rename_it_to_in_locale(string $name, string $locale_code): void
    {
        $data['translations'][$locale_code]['name'] = $name;
        $this->client->update_request_data($data);
    }
    #[When('I generate its slug in :localeCode locale')]
    public function i_generate_its_slug_in(string $locale_code): void
    {
        // Intentionally left blank, as this is a UI-specific action.
    }
    #[When('I set its slug to :slug')]
    #[When('I set its slug to :slug in :localeCode locale')]
    #[When('I remove its slug')]
    public function i_set_its_slug_to(?string $slug = null, $locale_code = 'en_US'): void
    {
        $data = ['translations' => [$locale_code => ['slug' => $slug]]];
        $this->client->update_request_data($data);
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I add the :productOption option to it')]
    public function i_add_the_option_to_it(Product_Option_Interface $product_option): void
    {
        $this->client->update_request_data(['options' => [$this->iri_converter->get_iri_from_resource_in_section($product_option, 'admin')]]);
    }
    #[When('/^I choose main (taxon "[^"]+")$/')]
    public function i_choose_main_taxon(Taxon_Interface $taxon): void
    {
        $this->client->update_request_data(['mainTaxon' => $this->iri_converter->get_iri_from_resource_in_section($taxon, 'admin')]);
    }
    #[When('I filter them by :taxon taxon')]
    public function i_filter_them_by_taxon(Taxon_Interface $taxon): void
    {
        $this->client->add_filter('productTaxons.taxon.code', $taxon->get_code());
        $this->client->filter();
        $this->shared_storage->set('response', $this->client->get_last_response());
    }
    #[When('I search for products with :name name')]
    public function i_search_for_products_with_name(string $name): void
    {
        $this->client->add_filter('translations.name', $name);
        $this->client->filter();
    }
    #[When('I search for products with :code code')]
    public function i_search_for_products_with_code(string $code): void
    {
        $this->client->add_filter('code', $code);
        $this->client->filter();
    }
    #[When('I filter them by :taxon main taxon')]
    public function i_filter_them_by_main_taxon(Taxon_Interface $taxon): void
    {
        $this->client->add_filter('mainTaxon.code', $taxon->get_code());
        $this->client->filter();
    }
    #[When('I start sorting products by code')]
    #[When('I switch the way products are sorted :sortType by code')]
    public function i_switch_the_way_products_are_sorted_by_code(string $sort_type = 'ascending'): void
    {
        $this->client->sort(['code' => self::SORT_TYPES[$sort_type]]);
        $this->shared_storage->set('response', $this->client->get_last_response());
    }
    #[When('I (try to) delete the :product product')]
    public function i_delete_product(Product_Interface $product): void
    {
        $this->client->delete(Resources::PRODUCTS, $product->get_code());
    }
    #[When('/^I want to modify (this product)$/')]
    #[When('I (want to) modify the :product product')]
    public function i_want_to_modify_a_product(Product_Interface $product): void
    {
        $this->client->build_update_request(Resources::PRODUCTS, $product->get_code());
    }
    #[Then('I should see the product :productName in the list')]
    #[Then('the product :productName should appear in the store')]
    #[Then('the product :productName should be in the shop')]
    #[Then('this product should still be named :productName')]
    public function the_product_should_appear_in_the_shop(string $product_name): void
    {
        $response = $this->client->index(Resources::PRODUCTS);
        Assert::true($this->response_checker->has_item_with_translation($response, 'en_US', 'name', $product_name));
    }
    #[When('I remove its name from :localeCode translation')]
    public function i_remove_its_name_from_translation(string $locale_code): void
    {
        $this->client->update_request_data(['translations' => [$locale_code => ['name' => '']]]);
    }
    #[When('I set its meta keywords to too long string in :localeCode')]
    public function i_set_its_meta_keywords_to_too_long_string_in(string $locale_code): void
    {
        $this->client->update_request_data(['translations' => [$locale_code => ['metaKeywords' => str_repeat('a', 256)]]]);
    }
    #[When('I set its meta description to too long string in :localeCode')]
    public function i_set_its_meta_description_to_too_long_string_in(string $locale_code): void
    {
        $this->client->update_request_data(['translations' => [$locale_code => ['metaDescription' => str_repeat('a', 256)]]]);
    }
    #[When('I set its non-translatable :attribute attribute to :value')]
    public function i_set_its_non_translatable_attribute_to(Product_Attribute_Interface $attribute, string $value): void
    {
        $this->client->add_sub_resource_data('attributes', ['attribute' => $this->iri_converter->get_iri_from_resource($attribute), 'value' => $this->get_attribute_value_in_proper_type($attribute, $value)]);
    }
    #[When('I set the invalid integer value of the non-translatable :attribute attribute to :value')]
    public function i_set_the_invalid_integer_value_of_the_non_translatable_attribute_to(Product_Attribute_Interface $attribute, int $value): void
    {
        $this->client->add_sub_resource_data('attributes', ['attribute' => $this->iri_converter->get_iri_from_resource($attribute), 'value' => $value]);
    }
    #[When('I set the invalid string value of the non-translatable :attribute attribute to :value')]
    public function i_set_the_invalid_string_value_of_the_non_translatable_attribute_to(Product_Attribute_Interface $attribute, string $value): void
    {
        $this->client->add_sub_resource_data('attributes', ['attribute' => $this->iri_converter->get_iri_from_resource($attribute), 'value' => $value]);
    }
    #[When('I want to modify the images of :product product')]
    public function i_want_to_modify_the_images_of_product(Product_Interface $product): void
    {
        $this->shared_storage->set('productIri', $this->iri_converter->get_iri_from_resource($product));
    }
    #[When('I change the :type image position to :position')]
    public function i_change_the_image_position_to(string $image_type, int $position): void
    {
        $images = $this->response_checker->get_value($this->client->show_by_iri($this->shared_storage->get('productIri')), 'images');
        $product_code = $this->response_checker->get_value($this->client->get_last_response(), 'code');
        foreach ($images as $image_data) {
            if ($image_data['type'] === $image_type) {
                $image_id = $image_data['id'];
            }
        }
        $builder = Request_Builder::create(sprintf('/api/v2/admin/products/%s/images/%s', $product_code, $image_id), Request::METHOD_PUT);
        $builder->with_content(['position' => $position]);
        $builder->with_header('HTTP_Authorization', 'Bearer ' . $this->shared_storage->get('token'));
        $builder->with_header('CONTENT_TYPE', 'application/ld+json');
        $this->client->request($builder->build());
    }
    #[When('I set its :attribute attribute to :value')]
    #[When('I set its :attribute attribute to :value in :localeCode locale')]
    #[When('I do not set its :attribute attribute in :localeCode locale')]
    #[When('I set the :attribute attribute value to :value in :localeCode locale')]
    public function i_set_its_attribute_to(Product_Attribute_Interface $attribute, ?string $value = null, string $locale_code = 'en_US'): void
    {
        $this->client->add_sub_resource_data('attributes', ['attribute' => $this->iri_converter->get_iri_from_resource_in_section($attribute, 'admin'), 'value' => $value !== null ? $this->get_attribute_value_in_proper_type($attribute, $value) : null, 'localeCode' => $locale_code]);
    }
    #[When('I remove its :attribute attribute')]
    public function i_remove_its_attribute(Product_Attribute_Interface $attribute): void
    {
        $attribute_iri = $this->iri_converter->get_iri_from_resource_in_section($attribute, 'admin');
        $content = $this->client->get_content();
        foreach ($content['attributes'] as $key => $attribute_value) {
            if ($attribute_value['attribute'] === $attribute_iri) {
                unset($content['attributes'][$key]);
            }
        }
        $this->client->set_request_data($content);
    }
    #[When('I add the :attributeName attribute')]
    #[When('I add the :attributeName attribute to it')]
    public function i_add_the_attribute(string $attribute_name): void
    {
        // Intentionally left blank
    }
    #[When('I select :value value in :localeCode for the :attribute attribute')]
    public function i_select_value_in_for_the_attribute(string $value, string $locale_code, Product_Attribute_Interface $attribute): void
    {
        $this->client->add_sub_resource_data('attributes', ['attribute' => $this->iri_converter->get_iri_from_resource($attribute), 'value' => [$this->get_select_attribute_value_uuid_by_choice_value($attribute, $value)], 'localeCode' => $locale_code]);
    }
    #[When('I select :value value for the :attribute attribute')]
    public function i_select_value_for_the_attribute(string $value, Product_Attribute_Interface $attribute): void
    {
        $this->client->add_sub_resource_data('attributes', ['attribute' => $this->iri_converter->get_iri_from_resource($attribute), 'value' => [$this->get_select_attribute_value_uuid_by_choice_value($attribute, $value)]]);
    }
    #[When('I enable it in channel :channel')]
    public function i_enable_it_in_channel(Channel_Interface $channel): void
    {
        $this->client->add_request_data('channels', [$this->iri_converter->get_iri_from_resource($channel)]);
    }
    #[When('I access the :product product')]
    public function i_access_the_product(Product_Interface $product): void
    {
        $this->client->show(Resources::PRODUCTS, $product->get_code());
    }
    #[When('I choose :channel as a channel filter')]
    public function i_choose_channel_as_a_channel_filter(Channel_Interface $channel): void
    {
        $this->client->add_filter('channel', $this->iri_converter->get_iri_from_resource($channel));
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->client->filter();
        $this->shared_storage->set('response', $this->client->get_last_response());
    }
    #[Then('I should see main taxon is :taxon')]
    public function i_should_see_main_taxon_is(Taxon_Interface $taxon): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'mainTaxon'), $this->iri_converter->get_iri_from_resource_in_section($taxon, 'admin'));
    }
    #[Then('I should see product taxon :taxon')]
    public function i_should_see_product_taxon(Taxon_Interface $taxon): void
    {
        $product = $this->shared_storage->get('product');
        Assert::is_instance_of($product, Product_Interface::class);
        $product_taxon = $product->get_product_taxons()->filter(fn(Product_Taxon_Interface $product_taxon): bool => $product_taxon->get_taxon()->get_code() === $taxon->get_code())->first();
        Assert::is_instance_of($product_taxon, Product_Taxon_Interface::class);
        Assert::true($this->response_checker->has_value_in_collection($this->client->get_last_response(), 'productTaxons', $this->iri_converter->get_iri_from_resource_in_section($product_taxon, 'admin')));
    }
    #[Then('I should see option :productOption')]
    public function i_should_see_option(Product_Option_Interface $product_option): void
    {
        Assert::true($this->response_checker->has_value_in_collection($this->client->get_last_response(), 'options', $this->iri_converter->get_iri_from_resource_in_section($product_option, 'admin')));
    }
    #[Then('I should see :count variants')]
    public function i_should_see_variants(int $count): void
    {
        Assert::count($this->response_checker->get_response_content($this->client->get_last_response())['variants'] ?? [], $count);
    }
    #[Then('I should see the :variant variant')]
    public function i_should_see_the_variant(Product_Variant_Interface $variant): void
    {
        Assert::true($this->response_checker->has_value_in_collection($this->client->get_last_response(), 'variants', $this->iri_converter->get_iri_from_resource_in_section($variant, 'admin')));
    }
    #[Then('I should see product :field is :value')]
    #[Then('I should see product\'s :field is :value')]
    public function i_should_see_product_field_is(string $field, string $value): void
    {
        $this->assert_response_has_translation_field_with_value($field, $value);
    }
    #[Then('I should see product\'s meta keyword(s) is/are :metaKeywords')]
    public function i_should_see_product_meta_keywords_are(string $meta_keywords): void
    {
        $this->assert_response_has_translation_field_with_value('metaKeywords', $meta_keywords);
    }
    #[Then('I should see product\'s short description is :shortDescription')]
    public function i_should_see_product_short_description_is(string $short_description): void
    {
        $this->assert_response_has_translation_field_with_value('shortDescription', $short_description);
    }
    #[Then('I should see product association type :productAssociationType')]
    public function i_should_see_product_association_type(Product_Association_Type_Interface $product_association_type): void
    {
        $associations = $this->response_checker->get_value($this->client->get_last_response(), 'associations');
        foreach ($associations as $association_iri) {
            /** @var ProductAssociationInterface $association */
            $association = $this->iri_converter->get_resource_from_iri($association_iri);
            if ($association->get_type()->get_code() === $product_association_type->get_code()) {
                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('Product association type "%s" not found.', $product_association_type->get_code()));
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()));
    }
    #[Then('I should be notified that this product is in use and cannot be deleted')]
    public function i_should_be_notified_that_this_product_is_in_use_and_cannot_be_deleted(): void
    {
        Assert::false($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Product can be deleted, but it should not');
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Product still exists, but it should not');
    }
    #[Then('/^I should be notified that (code|name) is required$/')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Please enter product %s.', $element));
    }
    #[Then('the one before last image on the list should have type :type with position :position')]
    public function the_one_before_last_image_on_the_list_should_have_name_with_position(string $image_type, int $position): void
    {
        $images = $this->response_checker->get_value($this->client->show_by_iri($this->shared_storage->get('productIri')), 'images');
        Assert::same($images[count($images) - 2]['type'], $image_type);
        Assert::same($images[count($images) - 2]['position'], $position);
    }
    #[Then('the last image on the list should have type :type with position :position')]
    public function the_last_image_on_the_list_should_have_name_with_position(string $image_type, int $position): void
    {
        $images = $this->response_checker->get_value($this->client->show_by_iri($this->shared_storage->get('productIri')), 'images');
        Assert::same($images[count($images) - 1]['type'], $image_type);
        Assert::same($images[count($images) - 1]['position'], $position);
    }
    #[Then('I should be notified that meta keywords are too long')]
    public function i_should_be_notified_that_meta_keywords_are_too_long(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Product meta keywords must not be longer than 255 characters.');
    }
    #[Then('I should be notified that meta description is too long')]
    public function i_should_be_notified_that_meta_description_is_too_long(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Product meta description must not be longer than 255 characters.');
    }
    #[Then('I should be notified that code has to be unique')]
    public function i_should_be_notified_that_code_has_to_be_unique(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Product code must be unique.');
    }
    #[Then('I should see a single product in the list')]
    #[Then('I should see :count products in the list')]
    public function i_should_see_products_in_the_list(int $count = 1): void
    {
        Assert::count($this->response_checker->get_collection($this->client->get_last_response()), $count);
    }
    #[Then('I should see a product with :field :value')]
    public function i_should_see_product_with(string $field, string $value): void
    {
        $response = $this->get_last_response();
        Assert::true($this->has_product_with_field_value($response, $field, $value), sprintf('Product has not %s with %s', $field, $value));
    }
    #[Then('I should not see any product with :field :value')]
    public function i_should_not_see_any_product_with(string $field, string $value): void
    {
        $response = $this->get_last_response();
        Assert::false($this->response_checker->has_item_with_translation($response, 'en_US', $field, $value), sprintf('Product with %s set as %s still exists, but it should not', $field, $value));
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->add_request_data('code', '_NEW');
        $this->client->update();
        $this->client->index(Resources::PRODUCTS);
        Assert::false($this->response_checker->has_item_on_position_with_value($this->client->get_last_response(), 0, 'code', sprintf('%s/admin/products/_NEW', $this->api_url_prefix)), sprintf('It was possible to change %s', '_NEW'));
    }
    #[Then('/^(this product) main (taxon should be "[^"]+")$/')]
    #[Then('main taxon of product :product should be :taxon')]
    public function this_product_main_taxon_should_be(Product_Interface $product, Taxon_Interface $taxon): void
    {
        $response = $this->client->show(Resources::PRODUCTS, $product->get_code());
        $main_taxon = $this->response_checker->get_value($response, 'mainTaxon');
        Assert::same($main_taxon, $this->iri_converter->get_iri_from_resource_in_section($taxon, 'admin'));
    }
    #[Then('the product :product should have the :taxon taxon')]
    public function this_product_taxon_should_be(Product_Interface $product, Taxon_Interface $taxon): void
    {
        $this->client->index(Resources::PRODUCT_TAXONS);
        Assert::true($this->response_checker->has_item_with_values($this->client->get_last_response(), ['product' => $this->iri_converter->get_iri_from_resource_in_section($product, 'admin'), 'taxon' => $this->iri_converter->get_iri_from_resource_in_section($taxon, 'admin')]));
    }
    #[Then('the product :product should not have the :taxon taxon')]
    public function this_product_taxon_should_have_not_the_taxon(Product_Interface $product, Taxon_Interface $taxon): void
    {
        $this->client->index(Resources::PRODUCT_TAXONS);
        Assert::false($this->response_checker->has_item_with_values($this->client->get_last_response(), ['product' => $this->iri_converter->get_iri_from_resource_in_section($product, 'admin'), 'taxon' => $this->iri_converter->get_iri_from_resource_in_section($taxon, 'admin')]));
    }
    #[Then('/^(this product) name should be "([^"]+)" in ("([^"]+)" locale)$/')]
    public function this_product_name_should_be(Product_Interface $product, string $name, string $locale_code): void
    {
        $response = $this->client->show(Resources::PRODUCTS, $product->get_code());
        Assert::true($this->response_checker->has_translation($response, $locale_code, 'name', $name), sprintf('Product\'s name %s does not exist', $name));
    }
    #[Then('/^(this product) should not exist in the product catalog$/')]
    public function product_should_not_exist(Product_Interface $product): void
    {
        $response = $this->client->index(Resources::PRODUCTS);
        Assert::false($this->response_checker->has_item_with_value($response, 'code', $product->get_code()), sprintf('Product with name %s still exists, but it should not', $product->get_name()));
    }
    #[Then('/^(this product) should have (?:a|an) ("[^"]+" option)$/')]
    public function this_product_should_have_option(Product_Interface $product, Product_Option_Interface $product_option): void
    {
        $response = $this->client->show(Resources::PRODUCTS, $product->get_code());
        $product_from_response = $this->response_checker->get_response_content($response);
        Assert::true(in_array($this->iri_converter->get_iri_from_resource_in_section($product_option, 'admin'), $product_from_response['options'], true), sprintf('Product with option %s does not exist', $product_option->get_name()));
    }
    #[Then('the first product on the list should have :field :value')]
    public function the_first_product_on_the_list_should_have(string $field, string $value): void
    {
        $products = $this->response_checker->get_collection($this->get_last_response());
        Assert::same($this->get_field_value_of_product($products[0], $field), $value);
    }
    #[Then('the last product on the list should have name :name')]
    public function the_last_product_on_the_list_should_have_name(string $name): void
    {
        $products = $this->response_checker->get_collection($this->get_last_response());
        Assert::same($this->get_field_value_of_product(end($products), 'name'), $name);
    }
    #[Then('/^the (first|last) product on the list shouldn\'t have a name$/')]
    public function the_product_on_the_list_should_not_have_a_name(string $position): void
    {
        $products = $this->response_checker->get_collection($this->get_last_response());
        $product = $position === 'last' ? end($products) : reset($products);
        Assert::null($this->get_field_value_of_product($product, 'name'));
    }
    #[Then('/^the slug of the ("[^"]+" product) should(?:| still) be "([^"]+)"$/')]
    #[Then('/^the slug of the ("[^"]+" product) should(?:| still) be "([^"]+)" (in the "[^"]+" locale)$/')]
    #[Then('/^(this product) should(?:| still) have slug "([^"]+)" in ("[^"]+" locale)$/')]
    public function product_slug_should_be(Product_Interface $product, string $slug, string $locale_code = 'en_US'): void
    {
        $response = $this->client->show(Resources::PRODUCTS, $product->get_code());
        Assert::true($this->response_checker->has_translation($response, $locale_code, 'slug', $slug), sprintf('Product\'s slug %s does not exist', $slug));
    }
    #[Then('/^there should be no reviews of (this product)$/')]
    public function there_are_no_product_reviews(Product_Interface $product): void
    {
        $response = $this->client->index(Resources::PRODUCT_REVIEWS);
        Assert::is_empty($this->response_checker->get_collection_items_with_value($response, 'reviewSubject', $this->iri_converter->get_iri_from_resource_in_section($product, 'admin')), 'Should be no reviews, but some exist');
    }
    #[Then('/^(this product) should still exist in the product catalog$/')]
    public function product_should_exist_in_the_product_catalog(Product_Interface $product): void
    {
        $response = $this->client->index(Resources::PRODUCTS);
        $code = $product->get_code();
        Assert::true($this->response_checker->has_item_with_value($response, 'code', $code), sprintf('Product with code %s does not exist', $code));
    }
    #[Then('/^the (product "[^"]+") should still have an accessible image$/')]
    public function product_should_still_have_an_accessible_image(Product_Interface $product): void
    {
        $response = $this->client->show(Resources::PRODUCTS, $product->get_code());
        Assert::true($this->has_product_image($response, $product), 'Image does not exists');
    }
    #[Then('/^product with (name|code) "([^"]+)" should not be added$/')]
    public function product_with_name_should_not_be_added(string $field, string $value): void
    {
        Assert::false($this->has_product_with_field_value($this->client->index(Resources::PRODUCTS), $field, $value));
    }
    #[Then('non-translatable attribute :attribute of product :product should be :value')]
    #[Then('select attribute :attribute of product :product should be :value')]
    public function non_translatable_attribute_of_product_should_be(Product_Attribute_Interface $attribute, Product_Interface $product, string $value): void
    {
        $this->client->show(Resources::PRODUCTS, $product->get_code());
        $this->has_attribute_with_value_in_last_response($attribute, $value);
    }
    #[Then('I should see non-translatable attribute :attribute with value :value%')]
    public function i_should_see_non_translatable_attribute_with_value(Product_Attribute_Interface $attribute, int $value): void
    {
        $this->has_attribute_with_value_in_last_response($attribute, (string) ($value / 100));
    }
    #[Then('attribute :attribute of product :product should be :value')]
    #[Then('attribute :attribute of product :product should be :value in :localeCode locale')]
    #[Then('select attribute :attribute of product :product should be :value in :localeCode locale')]
    public function attribute_of_product_should_be(Product_Attribute_Interface $attribute, Product_Interface $product, string $value, string $locale_code = 'en_US'): void
    {
        $this->client->show(Resources::PRODUCTS, $product->get_code());
        $this->has_attribute_with_value_in_last_response($attribute, $value, $locale_code);
    }
    #[Then('product :product should not have a :attribute attribute')]
    public function product_should_not_have_attribute(Product_Interface $product, Product_Attribute_Interface $attribute): void
    {
        $attributes = $this->response_checker->get_value($this->client->get_last_response(), 'attributes');
        foreach ($attributes as $attribute_value) {
            if ($attribute_value['attribute'] === $this->iri_converter->get_iri_from_resource_in_section($attribute, 'admin')) {
                throw new \InvalidArgumentException(sprintf('Product %s have attribute %s', $product->get_name(), $attribute->get_name()));
            }
        }
    }
    #[Then('I should not be able to edit its options')]
    public function i_should_not_be_able_to_edit_its_options(): void
    {
        $product_option = $this->shared_storage->get('product_option');
        $product_option_iri = $this->iri_converter->get_iri_from_resource_in_section($product_option, 'admin');
        $this->client->update_request_data(['options' => [$product_option_iri]]);
        $res = $this->client->update();
        Assert::false($this->response_checker->has_value_in_collection($res, 'options', $product_option_iri), 'The product options should not be changed, but they were');
    }
    #[Then('I should be notified that I have to define product variants\' prices for newly assigned channels first')]
    public function i_should_be_notified_that_i_have_to_define_product_variants_prices_for_newly_assigned_channels_first(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'You have to define product variants\' prices for newly assigned channels first.');
    }
    #[Then('I should be notified that slug has to be unique')]
    public function i_should_be_notified_that_slug_has_to_be_unique(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Product slug must be unique.');
    }
    #[Then('I should be notified that I have to define the :attributeName attribute in :localeCode locale')]
    public function i_should_be_notified_that_i_have_to_define_the_attribute_in_locale(string $attribute_name, string $locale_code): void
    {
        Assert::regex($this->response_checker->get_error($this->client->get_last_response()), '/attributes\[[\d+]\]\.value: This value should not be blank\./');
    }
    #[Then('I should be notified that the :attributeName attribute in :localeCode locale should be longer than :number')]
    public function i_should_be_notified_that_the_attribute_in_should_be_longer_than(string $attribute_name, string $locale_code, int $number): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('This value is too short. It should have %s characters or more.', $number));
    }
    #[Then('I should be notified that the value of the :attributeName attribute has invalid type')]
    public function i_should_be_notified_that_the_value_of_the_attribute_has_invalid_type(string $attribute_name): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('The value of attribute "%s" has an invalid type', $attribute_name));
    }
    #[Then('I should see an image related to this product')]
    public function i_should_see_image_related_to_this_product(): void
    {
        Assert::not_empty($this->response_checker->get_value($this->client->get_last_response(), 'images'));
    }
    #[Then('I should see attribute :attribute with value :value in :locale locale')]
    public function i_should_see_attribute_with_value_in_locale(Product_Attribute_Interface $attribute, string $value, Locale_Interface $locale): void
    {
        $this->has_attribute_with_value_in_last_response($attribute, $value, $locale->get_code());
    }
    private function get_admin_locale_code(): string
    {
        /** @var AdminUserInterface $adminUser */
        $admin_user = $this->shared_storage->get('administrator');
        $response = $this->client->show(Resources::ADMINISTRATORS, (string) $admin_user->get_id());
        return $this->response_checker->get_value($response, 'localeCode');
    }
    private function get_field_value_of_product(array $product, string $field): ?string
    {
        if ($field === 'code') {
            return $product['code'];
        }
        if ($field === 'name') {
            return $product['translations'][$this->get_admin_locale_code()]['name'] ?? null;
        }
        return null;
    }
    private function has_product_image(Response $response, Product_Interface $product): bool
    {
        $product_from_response = $this->response_checker->get_response_content($response);
        return isset($product_from_response['images'][0]) && str_contains((string) $product_from_response['images'][0]['path'], (string) $product->get_images()->first()->get_path());
    }
    private function has_product_with_field_value(Response $response, string $field, string $value): bool
    {
        if ($field === 'code') {
            return $this->response_checker->has_item_with_value($response, $field, $value);
        }
        if ($field === 'name') {
            return $this->response_checker->has_item_with_translation($response, $this->get_admin_locale_code(), $field, $value);
        }
        return false;
    }
    private function get_last_response(): Response
    {
        return $this->shared_storage->has('response') ? $this->shared_storage->get('response') : $this->client->get_last_response();
    }
    private function get_attribute_value_in_proper_type(Product_Attribute_Interface $product_attribute, string $value): bool|float|int|string
    {
        return match ($product_attribute->get_storage_type()) {
            Attribute_Value_Interface::STORAGE_BOOLEAN => (bool) $value,
            Attribute_Value_Interface::STORAGE_FLOAT => (float) $value,
            Attribute_Value_Interface::STORAGE_INTEGER => (int) $value,
            default => $value,
        };
    }
    private function get_select_attribute_value_uuid_by_choice_value(Product_Attribute_Interface $attribute, string $value): string
    {
        $choices = $attribute->get_configuration()['choices'] ?? [];
        foreach ($choices as $uuid => $choice) {
            if (in_array($value, $choice, true)) {
                return $uuid;
            }
        }
        throw new \InvalidArgumentException(sprintf('Value "%s" not found in attribute "%s"', $value, $attribute->get_name()));
    }
    private function has_attribute_with_value_in_last_response(Product_Attribute_Interface $attribute, string $value, ?string $locale_code = null): void
    {
        $attribute_iri = $this->iri_converter->get_iri_from_resource_in_section($attribute, 'admin');
        $attributes = $this->response_checker->get_value($this->client->get_last_response(), 'attributes');
        foreach ($attributes as $attribute_value) {
            if ($attribute_value['attribute'] === $attribute_iri && $attribute_value['localeCode'] === $locale_code) {
                $this->assert_attribute_value($value, $attribute_value['value']);
                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('The given product does not have attribute %s', $attribute->get_name()));
    }
    private function assert_attribute_value(string $expected_value, $value): void
    {
        if (is_array($value)) {
            Assert::all_in_array($value, [$expected_value]);
            return;
        }
        Assert::same((string) $value, $expected_value);
    }
    private function assert_response_has_translation_field_with_value(string $field, string $value): void
    {
        Assert::same($this->response_checker->get_translation_value($this->client->get_last_response(), $field), $value);
    }
}
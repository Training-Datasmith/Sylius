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

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Product\Model\Product_Option_Interface;
use Sylius\Component\Product\Model\Product_Option_Value_Interface;
use Webmozart\Assert\Assert;
final class Managing_Product_Options_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private Iri_Converter_Interface $iri_converter)
    {
    }
    #[Given('I want to create a new product option')]
    public function i_want_to_create_a_new_product_option(): void
    {
        $this->client->build_create_request(Resources::PRODUCT_OPTIONS);
    }
    #[Given('I am browsing product options')]
    #[When('I browse product options')]
    public function i_browse_product_options(): void
    {
        $this->client->index(Resources::PRODUCT_OPTIONS);
    }
    #[When('I want to modify the :productOption product option')]
    public function i_want_to_modify_product_option(Product_Option_Interface $product_option): void
    {
        $this->shared_storage->set('product_option', $product_option);
        $this->client->build_update_request(Resources::PRODUCT_OPTIONS, $product_option->get_code());
    }
    #[When('I name it :name in :localeCode')]
    #[When('I do not name it')]
    public function i_name_it_in_language(?string $name = null, ?string $locale_code = 'en_US'): void
    {
        $data = ['translations' => [$locale_code => []]];
        if ($name !== null) {
            $data['translations'][$locale_code]['name'] = $name;
        }
        $this->client->update_request_data($data);
    }
    #[When('I rename it to :name in :localeCode')]
    public function i_rename_it_in_language(string $name, string $locale_code): void
    {
        $this->client->update_request_data(['translations' => [$locale_code => ['name' => $name]]]);
    }
    #[When('I remove its name from :localeCode translation')]
    public function i_remove_its_name_from_translation(string $locale_code): void
    {
        $this->client->update_request_data(['translations' => [$locale_code => ['name' => '']]]);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        if ($code !== null) {
            $this->client->add_request_data('code', $code);
        }
    }
    #[When('I add the :value option value identified by :code')]
    #[When('I add the :value option value identified by :code in :localeCode')]
    public function i_add_the_option_value_with_code_and_value(string $value, string $code, string $locale_code = 'en_US'): void
    {
        $this->client->add_sub_resource_data('values', ['code' => $code, 'translations' => [$locale_code => ['value' => $value]]]);
    }
    #[When('I delete the :optionValue option value of this product option')]
    public function i_delete_the_option_value_of_this_product_option(Product_Option_Value_Interface $option_value): void
    {
        $option_value_iri = $this->iri_converter->get_iri_from_resource($option_value);
        $this->client->remove_sub_resource_object('values', $option_value_iri, 'value');
    }
    #[When('I do not add an option value')]
    public function i_do_not_add_an_option_value(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('/^I search for product options with "([^"]+)" (code|name)$/')]
    public function i_search_for_product_options_with(string $phrase, string $field): void
    {
        $this->client->add_filter($field === 'name' ? 'translations.name' : 'code', $phrase);
        $this->client->filter();
    }
    #[Then('I should see :count product options in the list')]
    public function i_should_see_product_options_in_the_list(int $count): void
    {
        $items_count = $this->response_checker->count_collection_items($this->client->get_last_response());
        Assert::eq($count, $items_count, sprintf('Expected %d product options, but got %d', $count, $items_count));
    }
    #[Then('the product option :productOption should be in the registry')]
    #[Then('the product option :productOption should appear in the registry')]
    public function the_product_option_should_appear_in_the_registry(Product_Option_Interface $product_option): void
    {
        $this->shared_storage->set('product_option', $product_option);
        $response = $this->client->index(Resources::PRODUCT_OPTIONS);
        Assert::true($this->response_checker->has_item_with_value($response, 'name', $product_option->get_name()), sprintf('Product option should have name "%s", but it does not.', $product_option->get_name()));
    }
    #[Then('the first product option in the list should have :field :value')]
    public function the_first_product_option_in_the_list_should_have(string $field, string $value): void
    {
        Assert::true($this->response_checker->has_item_on_position_with_value($this->client->get_last_response(), 0, $field, $value), sprintf('There should be product option with %s "%s" on position %d, but it does not.', $field, $value, 1));
    }
    #[Then('the last product option in the list should have :field :value')]
    public function the_last_product_option_in_the_list_should_have(string $field, string $value): void
    {
        $count = $this->response_checker->count_collection_items($this->client->get_last_response());
        Assert::true($this->response_checker->has_item_on_position_with_value($this->client->get_last_response(), $count - 1, $field, $value), sprintf('There should be product option with %s "%s" on position %d, but it does not.', $field, $value, $count - 1));
    }
    #[Then('the product option with :element :value should not be added')]
    public function the_product_option_with_element_value_should_not_be_added(string $element, string $value): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::PRODUCT_OPTIONS), $element, $value), sprintf('Product option should not have %s "%s", but it does,', $element, $value));
    }
    #[Then('there should still be only one product option with :element :value')]
    public function there_should_still_be_only_one_product_option_with(string $element, string $value): void
    {
        $response = $this->client->index(Resources::PRODUCT_OPTIONS);
        $items_count = $this->response_checker->count_collection_items($response);
        Assert::same($items_count, 1, sprintf('Expected 1 product options, but got %d', $items_count));
        Assert::true($this->response_checker->has_item_with_value($response, $element, $value));
    }
    #[Then('/^(this product option) name should be "([^"]+)"$/')]
    #[Then('/^(this product option) should still be named "([^"]+)"$/')]
    public function this_product_option_name_should_be(Product_Option_Interface $product_option, string $name): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::PRODUCT_OPTIONS, $product_option->get_code()), 'name', $name));
    }
    #[Then('/^(product option "[^"]+") should have the "([^"]+)" option value$/')]
    #[Then('/^(product option "[^"]+") should still have the "([^"]+)" option value$/')]
    #[Then('/^(this product option) should have the "([^"]*)" option value$/')]
    public function product_option_should_have_the_option_value(Product_Option_Interface $product_option, string $option_value_name): void
    {
        Assert::true($this->response_checker->has_item_with_translation($this->client->sub_resource_index(Resources::PRODUCT_OPTIONS, 'values', $product_option->get_code()), 'en_US', 'value', $option_value_name));
    }
    /**
     *      * @Then /^(this product option) should not have the "([^"]*)" option value in ("([^"]+)" locale)$/
     */
    #[Then('/^(this product option) should not have the "([^"]*)" option value$/')]
    public function this_product_option_should_not_have_the_option_value(Product_Option_Interface $product_option, string $option_value_name): void
    {
        Assert::false($this->response_checker->has_item_with_translation_in_collection($this->response_checker->get_value($this->client->show(Resources::PRODUCT_OPTIONS, $product_option->get_code()), 'values'), 'en_US', 'value', $option_value_name));
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->update_request_data(['code' => 'NEW_CODE']);
        $res = $this->client->update();
        Assert::false($this->response_checker->has_value($res, 'code', 'NEW_CODE'));
    }
    #[Then('I should be notified that product option with this code already exists')]
    public function i_should_be_notified_that_product_option_with_this_code_already_exists(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Product option has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: The option with given code already exists.');
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s: Please enter option %s.', $element, $element));
    }
    #[Then('I should be notified that it is in use')]
    public function i_should_be_notified_that_it_is_in_use(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot delete, the product option value is in use.');
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Product option could not be created');
    }
}
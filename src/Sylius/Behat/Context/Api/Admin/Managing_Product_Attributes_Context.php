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
use Ramsey\Uuid\Uuid;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Product\Model\Product_Attribute_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Product_Attributes_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I want to see all product attributes in store')]
    #[When('I am browsing product attributes')]
    public function i_want_to_browse_product_attributes(): void
    {
        $this->client->index(Resources::PRODUCT_ATTRIBUTES);
    }
    #[When('/^I(?:| try to) delete (this product attribute)$/')]
    public function i_delete_this_product_attribute(Product_Attribute_Interface $attribute): void
    {
        $this->client->delete(Resources::PRODUCT_ATTRIBUTES, $attribute->get_code());
    }
    #[When('I want to create a new :type product attribute')]
    public function i_want_to_create_a_new_typed_product_attribute(string $type): void
    {
        $this->client->build_create_request(Resources::PRODUCT_ATTRIBUTES);
        $this->client->add_request_data('type', $type);
    }
    #[When('I specify its code as :code')]
    public function i_specify_its_code_as(string $code): void
    {
        $this->client->add_request_data('code', $code);
    }
    #[When('/^I search by "([^"]+)" (code|name)$/')]
    public function i_search_by(string $phrase, string $field): void
    {
        $field = $field === 'name' ? 'translations.name' : $field;
        $this->client->add_filter($field, $phrase);
        $this->client->filter();
    }
    #[When('I choose :type in the type filter')]
    #[When('I choose :firstType and :secondType in the type filter')]
    public function i_choose_in_the_type_filter(string ...$types): void
    {
        foreach ($types as $type) {
            $this->client->add_filter('type[]', $type);
        }
    }
    #[When('I choose :translatable in the translatable filter')]
    public function i_choose_in_the_translatable_filter(string $translatable): void
    {
        match ($translatable) {
            'Yes' => $this->client->add_filter('translatable', 1),
            'No' => $this->client->add_filter('translatable', 0),
            default => throw new \InvalidArgumentException(sprintf('Translatable value "%s" is not supported.', $translatable)),
        };
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->client->filter();
    }
    #[When('I name it :name in :localeCode')]
    #[When('I change its name to :name in :localeCode')]
    #[When('I do not name it')]
    #[When('I remove its name from :localeCode translation')]
    public function i_name_it_in(string $name = '', string $locale_code = 'en_US'): void
    {
        $this->client->update_request_data(['translations' => [$locale_code => ['name' => $name]]]);
    }
    #[When('I (also) add value :value in :localeCode')]
    public function i_add_value_in(string $value, string $locale_code): void
    {
        $uuid = Uuid::uuid4()->to_string();
        $this->client->add_request_data('configuration', ['choices' => [$uuid => [$locale_code => $value]]]);
    }
    #[When('I disable its translatability')]
    public function i_disable_its_translatability(): void
    {
        $this->client->add_request_data('translatable', false);
    }
    #[When('I check multiple option')]
    public function i_check_multiple_option(): void
    {
        $this->client->add_request_data('configuration', ['multiple' => true]);
    }
    #[When('I do not check multiple option')]
    #[When('I do not specify its code')]
    public function intentionally_blank(): void
    {
        // Intentionally left blank
    }
    #[When('I specify its :limitType entries value as :count')]
    #[When('I specify its :limitType length as :count')]
    public function i_specify_its_limit_type_entries_as(string $limit_type, int $count): void
    {
        $this->client->add_request_data('configuration', [$limit_type => $count]);
    }
    #[When('/^I want to edit (this product attribute)$/')]
    public function i_want_to_edit_this_product_attribute(Product_Attribute_Interface $product_attribute): void
    {
        $this->shared_storage->set('product_attribute', $product_attribute);
        $this->client->build_update_request(Resources::PRODUCT_ATTRIBUTES, $product_attribute->get_code());
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('/^I change (its) value "([^"]+)" to "([^"]+)"$/')]
    public function i_change_its_value_to(Product_Attribute_Interface $product_attribute, string $old_value, string $new_value): void
    {
        $response = $this->client->show(Resources::PRODUCT_ATTRIBUTES, $product_attribute->get_code());
        $configuration = $this->response_checker->get_value($response, 'configuration');
        $choices = $configuration['choices'];
        foreach ($choices as $key => $choice) {
            if ($choice['en_US'] === $old_value) {
                $choices[$key]['en_US'] = $new_value;
                break;
            }
        }
        $this->client->update_request_data(['configuration' => ['choices' => $choices]]);
    }
    #[When('I delete value :value')]
    public function i_delete_value(string $value): void
    {
        /** @var ProductAttributeInterface $productAttribute */
        $product_attribute = $this->shared_storage->get('product_attribute');
        $response = $this->client->show(Resources::PRODUCT_ATTRIBUTES, $product_attribute->get_code());
        $configuration = $this->response_checker->get_value($response, 'configuration');
        $choices = $configuration['choices'];
        foreach ($choices as $key => $choice) {
            if ($choice['en_US'] === $value) {
                unset($choices[$key]);
                break;
            }
        }
        $this->client->set_request_data(['configuration' => ['choices' => $choices]]);
    }
    #[Then('I should see :count product attributes in the list')]
    #[Then('I should see a single product attribute in the list')]
    public function i_should_see_count_product_attributes_in_the_list(int $count = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('the first product attribute on the list should have name :name')]
    public function the_first_product_attribute_on_the_list_should_have_name(string $name): void
    {
        $first = $this->response_checker->get_collection($this->client->get_last_response())[0];
        Assert::same($first['translations']['en_US']['name'], $name);
    }
    #[Then('the last product attribute on the list should have name :name')]
    public function the_last_product_attribute_on_the_list_should_have_name(string $name): void
    {
        $collection = $this->response_checker->get_collection($this->client->get_last_response());
        $last = end($collection);
        Assert::same($last['translations']['en_US']['name'], $name);
    }
    #[Then('/^I should(?:| also) see the product attribute "([^"]+)" in the list$/')]
    public function i_should_see_the_product_attribute_in_the_list(string $attribute_name): void
    {
        Assert::true($this->response_checker->has_item_with_translation($this->client->get_last_response(), 'en_US', 'name', $attribute_name));
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        $this->response_checker->is_deletion_successful($this->client->get_last_response());
    }
    #[Then('/^(this product attribute) should no longer exist in the registry$/')]
    public function this_product_attribute_should_no_longer_exist_in_the_registry(Product_Attribute_Interface $product_attribute): void
    {
        $response = $this->client->index(Resources::PRODUCT_ATTRIBUTES);
        Assert::false($this->response_checker->has_item_with_value($response, 'code', $product_attribute->get_code()), sprintf('Product attribute with code %s exists, but should not', $product_attribute->get_code()));
    }
    #[Then('I should be notified that it is in use')]
    public function i_should_be_notified_that_it_is_in_use(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot delete, the product attribute is in use.');
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Product attribute could not be created');
    }
    #[Then('the :type attribute :name should appear in the store')]
    #[Then('the :type attribute :name should still be in the store')]
    public function the_attribute_should_appear_in_the_store(string $type, string $name): void
    {
        $response = $this->client->index(Resources::PRODUCT_ATTRIBUTES);
        /** @var array<string, mixed> $item */
        foreach ($this->response_checker->get_collection($response) as $item) {
            if ($item['type'] === $type && $item['translations']['en_US']['name'] === $name) {
                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('Product attribute of type "%s" with name "%s" has not been found', $type, $name));
    }
    #[Then('the attribute with :field :value should not appear in the store')]
    public function the_attribute_with_code_should_not_appear_in_the_store(string $field, string $value): void
    {
        $response = $this->client->index(Resources::PRODUCT_ATTRIBUTES);
        Assert::false($this->response_checker->has_item_with_value($response, $field, $value), sprintf('Product attribute with %s %s exists, but should not', $field, $value));
    }
    #[Then('I should see the value :value in :localeCode locale')]
    public function i_should_see_the_value_in_locale(string $value, string $locale_code): void
    {
        $content = $this->response_checker->get_response_content($this->client->get_last_response());
        $choices = $content['configuration']['choices'];
        foreach ($choices as $values) {
            if ($values[$locale_code] === $value) {
                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('Product attribute value "%s" has not been found in choices: %s', $value, json_encode($choices)));
    }
    #[Then('/^(this product attribute) should have value "([^"]+)"$/')]
    #[Then('/^the ("[^"]+" product attribute) should(?:| also) have value "([^"]+)"/')]
    public function this_product_attribute_should_have_value(Product_Attribute_Interface $product_attribute, string $value): void
    {
        $this->client->show(Resources::PRODUCT_ATTRIBUTES, $product_attribute->get_code());
        $this->i_should_see_the_value_in_locale($value, 'en_US');
    }
    #[Then('/^(this product attribute) should not have value "([^"]+)"$/')]
    public function this_product_attribute_should_not_have_value(Product_Attribute_Interface $product_attribute, string $value): void
    {
        $response = $this->client->show(Resources::PRODUCT_ATTRIBUTES, $product_attribute->get_code());
        $content = $this->response_checker->get_response_content($response);
        $choices = $content['configuration']['choices'];
        foreach ($choices as $values) {
            if (in_array($value, $values)) {
                throw new \InvalidArgumentException(sprintf('Product attribute value "%s" has been found but should not', $value));
            }
        }
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_field_is_required(string $field): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Please enter attribute %s.', $field));
    }
    #[Then('I should be notified that product attribute with this code already exists')]
    public function i_should_be_notified_that_product_attribute_with_this_code_already_exists(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'This code is already in use.');
    }
    #[Then('I should be notified that max length must be greater or equal to the min length')]
    public function i_should_be_notified_that_max_length_must_be_greater_or_equal_to_the_min_length(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Configuration max length must be greater or equal to the min length.');
    }
    #[Then('there should still be only one product attribute with code :code')]
    public function there_should_still_be_only_one_product_attribute_with_code(string $code): void
    {
        $items = $this->response_checker->get_collection_items_with_value($this->client->index(Resources::PRODUCT_ATTRIBUTES), 'code', $code);
        Assert::count($items, 1, sprintf('More than one attribute with code %s found', $code));
    }
    #[Then('I should be notified that max entries value must be greater or equal to the min entries value')]
    public function i_should_be_notified_that_max_entries_value_must_be_greater_or_equal_to_the_min_entries_value(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Configuration max entries value must be greater or equal to the min entries value.');
    }
    #[Then('I should be notified that min entries value must be lower or equal to the number of added choices')]
    public function i_should_be_notified_that_min_entries_value_must_be_lower_or_equal_to_the_number_of_added_choices(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Configuration min entries value must be lower or equal to the number of added choices.');
    }
    #[Then('I should be notified that multiple must be true if min or max entries values are specified')]
    public function i_should_be_notified_that_multiple_must_be_true_if_min_or_max_entries_values_are_specified(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Configuration multiple must be true if min or max entries values are specified.');
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->update_request_data(['code' => 'NEW_CODE']);
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'), 'The code field with value NEW_CODE exist');
    }
    #[Then('I should not be able to edit its type')]
    public function i_should_not_be_able_to_edit_its_type(): void
    {
        $this->client->update_request_data(['type' => 'percent']);
        Assert::false($this->response_checker->has_value($this->client->update(), 'type', 'percent'), 'The product attribute has new type select set.');
    }
}
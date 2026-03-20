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
use Sylius\Behat\Context\Api\Resources;
use Sylius\Component\Product\Model\Product_Association_Type_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Product_Association_Types_Context implements Context
{
    public const SORT_TYPES = ['ascending' => 'asc', 'descending' => 'desc'];
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[When('I want to create a new product association type')]
    public function i_want_to_create_a_new_product_association_type(): void
    {
        $this->client->build_create_request(Resources::PRODUCT_ASSOCIATION_TYPES);
    }
    #[When('I specify its code as :productAssociationTypeCode')]
    public function i_specify_its_code_as(string $product_association_type_code): void
    {
        $this->client->add_request_data('code', $product_association_type_code);
    }
    #[When('I name it :productAssociationTypeName in :localeCode')]
    #[When('I do not name it')]
    public function i_name_it_in(?string $product_association_type_name = null, string $locale_code = 'en_US'): void
    {
        $this->client->update_request_data(['translations' => [$locale_code => ['name' => $product_association_type_name]]]);
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I remove its name from :localeCode translation')]
    public function i_remove_its_name_from_translation(string $locale_code): void
    {
        $this->client->update_request_data(['translations' => [$locale_code => ['name' => null]]]);
    }
    #[When('I sort the product associations :sortType by code')]
    public function i_sort_product_associations_by_code(string $sort_type = 'ascending'): void
    {
        $this->client->sort(['code' => self::SORT_TYPES[$sort_type]]);
    }
    #[When('I am browsing product association types')]
    #[When('I want to browse product association types')]
    public function i_browse_product_association_types(): void
    {
        $this->client->index(Resources::PRODUCT_ASSOCIATION_TYPES);
    }
    #[When('I delete the :productAssociationType product association type')]
    public function i_delete_the_product_association_type(Product_Association_Type_Interface $product_association_type): void
    {
        $this->client->delete(Resources::PRODUCT_ASSOCIATION_TYPES, $product_association_type->get_code());
    }
    #[When('I want to modify the :productAssociationType product association type')]
    public function i_want_to_modify_the_product_association_type(Product_Association_Type_Interface $product_association_type): void
    {
        $this->client->build_update_request(Resources::PRODUCT_ASSOCIATION_TYPES, $product_association_type->get_code());
    }
    #[When('I rename it to :name in :localeCode')]
    public function i_rename_it_to_in(string $name, string $locale_code): void
    {
        $this->client->update_request_data(['translations' => [$locale_code => ['name' => $name]]]);
    }
    #[When('I filter product association types with code containing :value')]
    public function i_filter_product_association_types_with_code_containing(string $value): void
    {
        $this->client->add_filter('code', $value);
        $this->client->filter();
    }
    #[When('I filter product association types with name containing :value')]
    public function i_filter_product_association_types_with_name_containing(string $value): void
    {
        $this->client->add_filter('translations.name', $value);
        $this->client->filter();
    }
    #[When('I do not specify its code')]
    public function i_do_not_specify_its_code(): void
    {
        // Intentionally left blank
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Product association type could not be created');
    }
    #[Then('the product association type :name should appear in the store')]
    public function the_product_association_type_should_appear_in_the_store(string $name): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::PRODUCT_ASSOCIATION_TYPES), 'name', $name), sprintf('There is no product association type with name "%s"', $name));
    }
    #[Then('I should see :count product association types in the list')]
    #[Then('I should see a single product association type in the list')]
    public function i_should_see_product_association_types_in_the_list(int $count = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('I should see the product association type :name in the list')]
    #[Then('this product association type should still be named :name')]
    public function i_should_see_the_product_association_type_in_the_list(string $name): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::PRODUCT_ASSOCIATION_TYPES), 'name', $name), sprintf('There is no product association type with name "%s"', $name));
    }
    #[Then('/^I should be notified that it has been successfully deleted$/')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Product association type could not be deleted');
    }
    #[Then('/^(this product association type) should no longer exist in the registry$/')]
    public function this_product_association_type_should_no_longer_exist_in_the_registry(Product_Association_Type_Interface $product_association_type): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::PRODUCT_ASSOCIATION_TYPES), 'code', $product_association_type->get_code()), sprintf('Product association type with code %s exist', $product_association_type->get_code()));
    }
    #[Then('/^(this product association type) name should be "([^"]+)"$/')]
    public function this_product_association_type_name_should_be(Product_Association_Type_Interface $product_association_type, string $name): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::PRODUCT_ASSOCIATION_TYPES, $product_association_type->get_code()), 'name', $name), sprintf('Product association type name is not %s', $name));
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->add_request_data('code', 'NEW_CODE');
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'), 'The shipping category code should not be changed to "NEW_CODE", but it is');
    }
    #[Then('I should see only one product association type in the list')]
    public function i_should_see_only_one_product_association_type_in_the_list(): void
    {
        Assert::count($this->response_checker->get_collection($this->client->get_last_response()), 1);
    }
    #[Then('I should be notified that product association type with this code already exists')]
    public function i_should_be_notified_that_product_association_type_with_this_code_already_exists(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The association type with given code already exists.');
    }
    #[Then('there should still be only one product association type with a code :code')]
    public function there_should_still_be_only_one_product_association_type_with_a_code(string $code): void
    {
        Assert::count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::PRODUCT_ASSOCIATION_TYPES), 'code', $code), 1, sprintf('More then one Product association type have code %s.', $code));
    }
    #[Then('I should be notified that :type is required')]
    public function i_should_be_notified_that_code_is_required(string $type): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Please enter association type %s.', $type));
    }
    #[Then('the product association type with :type :value should not be added')]
    public function the_product_association_type_with_name_should_not_be_added(string $type, string $value): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::PRODUCT_ASSOCIATION_TYPES), $type, $value), sprintf('Product association type with %s %s exist', $type, $value));
    }
    #[Then('the first product association on the list should have code :value')]
    public function the_first_product_association_on_the_list_should_have(string $value): void
    {
        $product_associations = $this->response_checker->get_collection($this->client->get_last_response());
        Assert::same(reset($product_associations)['code'], $value);
    }
    #[Then('the last product association on the list should have code :value')]
    public function the_last_product_association_on_the_list_should_have(string $value): void
    {
        $product_associations = $this->response_checker->get_collection($this->client->get_last_response());
        Assert::same(end($product_associations)['code'], $value);
    }
}
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
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Component\Taxation\Model\Tax_Category_Interface;
use Webmozart\Assert\Assert;
final class Managing_Tax_Categories_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[Given('I am browsing tax categories')]
    #[When('I browse tax categories')]
    public function i_want_to_browse_tax_categories(): void
    {
        $this->client->index(Resources::TAX_CATEGORIES);
    }
    #[When('I want to create a new tax category')]
    public function i_want_to_create_new_tax_category(): void
    {
        $this->client->build_create_request(Resources::TAX_CATEGORIES);
    }
    #[When('I want to modify a tax category :taxCategory')]
    #[When('/^I want to modify (this tax category)$/')]
    public function i_want_to_modify_tax_category(Tax_Category_Interface $tax_category): void
    {
        $this->client->build_update_request(Resources::TAX_CATEGORIES, $tax_category->get_code());
    }
    #[When('I delete tax category :taxCategory')]
    public function i_delete_tax_category(Tax_Category_Interface $tax_category): void
    {
        $this->client->delete(Resources::TAX_CATEGORIES, $tax_category->get_code());
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        if ($code !== null) {
            $this->client->add_request_data('code', $code);
        }
    }
    #[When('I name it :name')]
    #[When('I rename it to :name')]
    #[When('I do not name it')]
    public function i_name_it(?string $name = null): void
    {
        if ($name !== null) {
            $this->client->add_request_data('name', $name);
        }
    }
    #[When('I remove its name')]
    public function i_remove_its_name(): void
    {
        $this->client->add_request_data('name', '');
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I describe it as :description')]
    public function i_describe_it_as(string $description): void
    {
        $this->client->add_request_data('description', $description);
    }
    #[When('/^I search by "([^"]+)" (code|name)$/')]
    public function i_search_by_name(string $phrase, string $field): void
    {
        $this->client->add_filter($field, $phrase);
        $this->client->filter();
    }
    #[Then('/^(this tax category) should no longer exist in the registry$/')]
    public function this_tax_category_should_no_longer_exist_in_the_registry(Tax_Category_Interface $tax_category): void
    {
        $code = $tax_category->get_code();
        Assert::false($this->is_item_on_index('code', $code), sprintf('Tax category with code %s exist', $code));
    }
    #[Then('I should see the tax category :taxCategoryName in the list')]
    #[Then('I should see the tax category :taxCategoryName')]
    #[Then('the tax category :taxCategoryName should appear in the registry')]
    public function the_tax_category_should_appear_in_the_registry(string $tax_category_name): void
    {
        Assert::true($this->is_item_on_index('name', $tax_category_name), sprintf('Tax category with name %s does not exist', $tax_category_name));
    }
    #[Then('I should not see the tax category :taxCategoryName')]
    public function i_should_not_see_the_tax_category(string $tax_category_name): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->get_last_response(), 'name', $tax_category_name), sprintf('Tax category with name %s exist', $tax_category_name));
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->add_request_data('code', 'NEW_CODE');
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'), 'The code field with value NEW_CODE exist');
    }
    #[Then('/^(this tax category) name should be "([^"]+)"$/')]
    #[Then('/^(this tax category) should still be named "([^"]+)"$/')]
    public function this_tax_category_name_should_be(Tax_Category_Interface $tax_category, string $tax_category_name): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::TAX_CATEGORIES, $tax_category->get_code()), 'name', $tax_category_name), sprintf('Tax category name is not %s', $tax_category_name));
    }
    #[Then('I should be notified that tax category with this code already exists')]
    public function i_should_be_notified_that_tax_category_with_this_code_already_exists(): void
    {
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'code: The tax category with given code already exists.');
    }
    #[Then('there should still be only one tax category with :element :value')]
    public function there_should_still_be_only_one_tax_category_with(string $element, string $value): void
    {
        Assert::same(count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::TAX_CATEGORIES), $element, $value)), 1);
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s: Please enter tax category %s.', $element, $element));
    }
    #[Then('tax category with :element :name should not be added')]
    public function tax_category_with_named_element_should_not_be_added(string $element, string $name): void
    {
        Assert::false($this->is_item_on_index($element, $name), sprintf('Tax category with %s %s does not exist', $element, $name));
    }
    #[Then('I should see :count tax categories in the list')]
    #[Then('I should see a single tax category in the list')]
    public function i_should_see_count_tax_categories_in_the_list(int $count = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Tax category could not be created');
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Tax category could not be deleted');
    }
    private function is_item_on_index(string $property, string $value): bool
    {
        return $this->response_checker->has_item_with_value($this->client->index(Resources::TAX_CATEGORIES), $property, $value);
    }
}
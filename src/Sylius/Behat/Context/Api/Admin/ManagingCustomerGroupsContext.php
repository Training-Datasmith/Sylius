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
use Sylius\Component\Customer\Model\Customer_Group_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Customer_Groups_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[When('I want to create a new customer group')]
    public function i_want_to_create_a_new_customer_group(): void
    {
        $this->client->build_create_request(Resources::CUSTOMER_GROUPS);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        if ($code !== null) {
            $this->client->add_request_data('code', $code);
        }
    }
    #[When('I specify its name as :name')]
    #[When('I remove its name')]
    public function i_specify_its_name_as(string $name = ''): void
    {
        $this->client->add_request_data('name', $name);
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('/^I want to edit (this customer group)$/')]
    public function i_want_to_edit_this_customer_group(Customer_Group_Interface $customer_group): void
    {
        $this->client->build_update_request(Resources::CUSTOMER_GROUPS, $customer_group->get_code());
    }
    #[When('I browse customer groups')]
    #[When('I want to browse customer groups')]
    public function i_want_to_browse_customer_groups(): void
    {
        $this->client->index(Resources::CUSTOMER_GROUPS);
    }
    #[When('I delete the :customerGroup customer group')]
    public function i_delete_the_customer_group(Customer_Group_Interface $customer_group): void
    {
        $this->client->delete(Resources::CUSTOMER_GROUPS, $customer_group->get_code());
    }
    #[When('I search for them with :phrase name')]
    public function i_search_resource_with(string $phrase): void
    {
        $this->client->add_filter('name', $phrase);
        $this->client->filter();
    }
    #[When('/^I sort them by the (code|name) in (asc|desc)ending order$/')]
    public function i_sort_them_by_the_field(string $field, string $order): void
    {
        $this->client->sort([$field => str_starts_with($order, 'de') ? 'desc' : 'asc']);
        $this->client->filter();
    }
    #[Then('there should be :count customer groups in the list')]
    public function there_should_be_customer_groups_in_the_list(int $count): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('the customer group :customerGroup should appear in the store')]
    public function the_customer_group_should_appear_in_the_store(Customer_Group_Interface $customer_group): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::CUSTOMER_GROUPS), 'code', $customer_group->get_code()), sprintf('Customer group with code %s does not exist', $customer_group->get_code()));
    }
    #[Then('this customer group with name :name should appear in the store')]
    #[Then('I should see the customer group :name in the list')]
    public function this_customer_group_with_name_should_appear_in_the_store(string $name): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::CUSTOMER_GROUPS), 'name', $name), sprintf('Customer group with name %s does not exist', $name));
    }
    #[Then('I should see a single customer group in the list')]
    #[Then('I should see :amountOfCustomerGroups customer groups in the list')]
    public function i_should_see_customer_groups_in_the_list(int $amount_of_customer_groups = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->index(Resources::CUSTOMER_GROUPS)), $amount_of_customer_groups);
    }
    #[Then('/^(this customer group) should still be named "([^"]+)"$/')]
    public function this_customer_group_should_still_be_named(Customer_Group_Interface $customer_group, string $name): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::CUSTOMER_GROUPS, $customer_group->get_code()), 'name', $name), 'Customer groups name is not ' . $name);
    }
    #[Then('I should be notified that name is required')]
    public function i_should_be_notified_that_name_is_required(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'name: Please enter a customer group name.');
    }
    #[Then('I should be notified that customer group with this code already exists')]
    public function i_should_be_notified_that_customer_group_with_this_code_already_exists(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Customer group code has to be unique.');
    }
    #[Then('I should be informed that this form contains errors')]
    public function i_should_be_informed_that_this_form_contains_errors(): void
    {
        Assert::not_empty($this->response_checker->get_error($this->client->get_last_response()));
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->update_request_data(['code' => 'NEW_CODE']);
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'), 'The code field with value NEW_CODE exist');
    }
    #[Then('/^(this customer group) should no longer exist in the registry$/')]
    public function this_customer_group_should_no_longer_exist_in_the_registry(Customer_Group_Interface $customer_group): void
    {
        $code = $customer_group->get_code();
        Assert::false($this->is_item_on_index('code', $code), sprintf('Customer group with code %s exist', $code));
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Customer group could not be created');
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Customer group could not be deleted');
    }
    #[Then('/^the (\d+)(?:|st|nd|rd|th) customer group on the list should have (name|code) "([^"]+)" and (name|code) "([^"]+)"$/')]
    public function the_first_customer_group_on_the_list_should_have(int $position, string $first_field, string $first_value, string $second_field, string $second_value): void
    {
        $customer_group = $this->response_checker->get_collection($this->client->get_last_response())[$position - 1];
        Assert::same($customer_group[$first_field], $first_value);
        Assert::same($customer_group[$second_field], $second_value);
    }
    private function is_item_on_index(string $property, string $value): bool
    {
        return $this->response_checker->has_item_with_value($this->client->index(Resources::CUSTOMER_GROUPS), $property, $value);
    }
}
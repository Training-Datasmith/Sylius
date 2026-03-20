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
use Sylius\Component\Shipping\Model\Shipping_Category_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Shipping_Categories_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[When('I want to create a new shipping category')]
    public function i_want_to_create_a_new_shipping_category(): void
    {
        $this->client->build_create_request(Resources::SHIPPING_CATEGORIES);
    }
    #[When('I want to modify a shipping category :shippingCategory')]
    public function i_want_to_modify_a_shipping_category(Shipping_Category_Interface $shipping_category): void
    {
        $this->client->build_update_request(Resources::SHIPPING_CATEGORIES, $shipping_category->get_code());
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I delete shipping category :shippingCategory')]
    public function i_delete_shipping_category(Shipping_Category_Interface $shipping_category): void
    {
        $this->client->delete(Resources::SHIPPING_CATEGORIES, $shipping_category->get_code());
    }
    #[When('I browse shipping categories')]
    public function i_browse_shipping_categories(): void
    {
        $this->client->index(Resources::SHIPPING_CATEGORIES);
    }
    #[When('I do not specify its code')]
    #[When('I specify its code as :code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        if ($code !== null) {
            $this->client->add_request_data('code', $code);
        }
    }
    #[When('I name it :name')]
    #[When('I do not specify its name')]
    #[When('I rename it to :name')]
    public function i_name_it(?string $name = null): void
    {
        if ($name !== null) {
            $this->client->add_request_data('name', $name);
        }
    }
    #[When('I modify a shipping category :shippingCategory')]
    public function i_modify_a_shipping_category(Shipping_Category_Interface $shipping_category): void
    {
        $this->client->build_update_request(Resources::SHIPPING_CATEGORIES, $shipping_category->get_code());
    }
    #[When('I specify its description as :description')]
    public function i_specify_its_description_as(string $description): void
    {
        $this->client->add_request_data('description', $description);
    }
    #[Then('I should be notified that shipping category with this code already exists')]
    public function i_should_be_notified_that_shipping_category_with_this_code_already_exists(): void
    {
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'code: The shipping category with given code already exists.');
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s: Please enter shipping category %s.', $element, $element));
    }
    #[Then('I should see a single shipping category in the list')]
    #[Then('I should see :count shipping categories in the list')]
    public function i_should_see_shipping_categories_in_the_list(int $count = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->index(Resources::SHIPPING_CATEGORIES)), $count);
    }
    #[Then('the shipping category :shippingMethodName should be in the registry')]
    #[Then('the shipping category :shippingMethodName should appear in the registry')]
    public function the_shipping_category_should_appear_in_the_registry(string $shipping_category_name): void
    {
        Assert::true($this->is_item_on_index('name', $shipping_category_name), sprintf('Shipping category with name %s does not exists', $shipping_category_name));
    }
    #[Then('shipping category with name :name should not be added')]
    public function shipping_category_with_name_should_not_be_added(string $name): void
    {
        Assert::false($this->is_item_on_index('name', $name), sprintf('Shipping category with name %s exists', $name));
    }
    #[Then('/^(this shipping category) should no longer exist in the registry$/')]
    public function this_shipping_category_should_no_longer_exist_in_the_registry(Shipping_Category_Interface $shipping_category): void
    {
        $shipping_category_name = $shipping_category->get_name();
        Assert::false($this->is_item_on_index('name', $shipping_category_name), sprintf('Shipping category with name %s exist', $shipping_category_name));
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->add_request_data('code', 'NEW_CODE');
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'), 'The shipping category code should not be changed to "NEW_CODE", but it is');
    }
    #[Then('there should still be only one shipping category with code :code')]
    public function there_should_still_be_only_one_shipping_category_with(string $code): void
    {
        Assert::same(count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::SHIPPING_CATEGORIES), 'code', $code)), 1);
    }
    #[Then('this shipping category name should be :name')]
    public function this_shipping_category_name_should_be(string $name): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'name', $name), sprintf('Shipping category with name %s does not exists', $name));
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Shipping category could not be created');
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Shipping category could not be deleted');
    }
    private function is_item_on_index(string $property, string $value): bool
    {
        $this->client->index(Resources::SHIPPING_CATEGORIES);
        return $this->response_checker->has_item_with_value($this->client->get_last_response(), $property, $value);
    }
}
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

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Component\Core\Model\Address_Interface;
use Webmozart\Assert\Assert;
final readonly class Address_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Given('/^I am editing the (address of "([^"]+)")$/')]
    public function i_am_editing_the_address_of(Address_Interface $address): void
    {
        $this->client->build_update_request(Resources::ADDRESSES, (string) $address->get_id());
    }
    #[When('I want to add a new address to my address book')]
    public function i_want_to_add_a_new_address_to_my_address_book(): void
    {
        $this->client->build_create_request(Resources::ADDRESSES);
    }
    #[When('/^I specify the (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)")$/')]
    public function i_specify_the_address_as(Address_Interface $address): void
    {
        $this->client->set_request_data(['countryCode' => $address->get_country_code(), 'street' => $address->get_street(), 'city' => $address->get_city(), 'postcode' => $address->get_postcode(), 'provinceName' => $address->get_province_name(), 'firstName' => $address->get_first_name(), 'lastName' => $address->get_last_name()]);
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I leave every field empty')]
    public function i_leave_every_field_empty(): void
    {
        $this->client->set_request_data([]);
    }
    #[When('I choose :countryCode as my country')]
    public function i_choose_as_my_country(string $country_code): void
    {
        $this->client->add_request_data('countryCode', $country_code);
    }
    #[When('I do not specify province')]
    public function i_do_not_specify_province(): void
    {
        $this->client->add_request_data('provinceName', '');
        $this->client->add_request_data('provinceCode', '');
    }
    #[When('I remove the street')]
    public function i_remove_the_street(): void
    {
        $this->client->add_request_data('street', null);
    }
    #[When('I save my changed address')]
    public function i_save_my_changed_address(): void
    {
        $this->client->update();
    }
    #[When('I browse my address book')]
    public function i_browse_my_addresses(): void
    {
        $this->client->index(Resources::ADDRESSES);
    }
    #[When('I delete the :fullName address')]
    public function i_delete_the_address(string $full_name): void
    {
        $id = $this->get_address_id_from_address_book_by_full_name($full_name);
        $this->client->delete(Resources::ADDRESSES, $id);
    }
    #[When('/^I try to delete (address belongs to "([^"]+)")$/')]
    public function i_delete_the_address_belongs_to(Address_Interface $address): void
    {
        $this->client->delete(Resources::ADDRESSES, (string) $address->get_id());
    }
    #[When('I set the address of :fullName as default')]
    public function i_set_the_address_of_as_default(string $full_name): void
    {
        $address_iri = $this->get_address_iri_from_address_book_by_full_name($full_name);
        $this->client->build_update_request(Resources::CUSTOMERS, (string) $this->shared_storage->get('user')->get_customer()->get_id());
        $this->client->add_request_data('defaultAddress', $address_iri);
        $this->client->update();
    }
    #[When('/^I try to edit the (address of "([^"]+)")$/')]
    public function i_try_to_edit_the_address_of(Address_Interface $address): void
    {
        $this->client->build_update_request(Resources::ADDRESSES, (string) $address->get_id());
    }
    #[When('I change the first name to :firstName')]
    public function i_change_the_first_name_to(string $first_name): void
    {
        $this->client->add_request_data('firstName', $first_name);
    }
    #[When('I change the last name to :lastName')]
    public function i_change_the_last_name_to(string $last_name): void
    {
        $this->client->add_request_data('lastName', $last_name);
    }
    #[When('I change the street to :street')]
    public function i_change_the_street_to(string $street): void
    {
        $this->client->add_request_data('street', $street);
    }
    #[When('I change the city to :city')]
    public function i_change_the_city_to(string $city): void
    {
        $this->client->add_request_data('city', $city);
    }
    #[When('I change the postcode to :postcode')]
    public function i_change_the_postcode_to(string $postcode): void
    {
        $this->client->add_request_data('postcode', $postcode);
    }
    #[When('I choose :province as my province')]
    public function i_choose_as_my_province(Province_Interface $province): void
    {
        $this->client->add_request_data('provinceCode', $province->get_code());
    }
    #[When('I specify :provinceName as my province')]
    public function i_specify_province(string $province_name): void
    {
        $this->client->add_request_data('provinceName', $province_name);
    }
    #[Then('it should contain country :countryCode')]
    public function it_should_contain_country(string $country_code): void
    {
        $this->it_should_contain($country_code);
    }
    #[Then('it should contain province :province')]
    public function it_should_contain_province(Province_Interface $province): void
    {
        $this->it_should_contain($province->get_code());
    }
    #[Then('it should contain :value')]
    public function it_should_contain(string $value): void
    {
        Assert::true($this->contains_value($this->response_checker->get_collection($this->client->get_last_response())[0], $value));
    }
    #[Then('I should be notified that the address has been successfully updated')]
    public function i_should_be_notified_that_the_address_has_been_successfully_updated(): void
    {
        Assert::true($this->response_checker->is_update_successful($this->client->get_last_response()));
    }
    #[Then('I should be unable to edit their address')]
    public function i_should_be_unable_to_edit_their_address(): void
    {
        Assert::false($this->response_checker->is_update_successful($this->client->update()));
    }
    #[When('/^I try to view details of (address belongs to "([^"]+)")$/')]
    public function i_try_to_view_details_of_address_belonging_to(Address_Interface $address): void
    {
        $this->client->show_by_iri($this->iri_converter->get_iri_from_resource($address));
    }
    #[Then('/^I should(?:| still) have a single address in my address book$/')]
    #[Then('/^I should(?:| still) have (\d+) addresses in my address book$/')]
    public function i_should_have_addresses(int $count = 1): void
    {
        Assert::same(count($this->response_checker->get_collection($this->client->index(Resources::ADDRESSES))), $count);
    }
    #[Then('this address should be assigned to :fullName')]
    #[Then('the address assigned to :fullName should be in my book')]
    public function this_address_should_be_assigned_to(string $full_name): void
    {
        Assert::not_null($this->get_address_iri_from_address_book_by_full_name($full_name), sprintf('There is no address assigned to %s', $full_name));
    }
    #[Then('I should not see the address assigned to :fullName')]
    public function i_should_not_see_the_address_assigned_to(string $full_name): void
    {
        /** @var AddressInterface $address */
        $address = $this->shared_storage->get('address_assigned_to_' . $full_name);
        $address_book = $this->response_checker->get_collection($this->client->get_last_response());
        Assert::false($this->address_book_has_address($address_book, $address));
    }
    #[Then('there should be no addresses')]
    public function there_should_be_no_addresses(): void
    {
        Assert::same(count($this->response_checker->get_collection($this->client->index(Resources::ADDRESSES))), 0);
    }
    #[Then('I should be notified that the address has been successfully deleted')]
    public function i_should_be_notified_that_address_has_been_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()));
    }
    #[Then('I should be notified that the address has been successfully added')]
    public function i_should_be_notified_that_the_address_has_been_successfully_added(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()));
    }
    #[Then('/^(address "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+"(?:|, "[^"]+")) should(?:| still) be marked as my default address$/')]
    #[Then('/^(address "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+"(?:|, "[^"]+")) should(?:| still) be set as my default address$/')]
    public function address_should_be_marked_as_my_default_address(Address_Interface $address): void
    {
        $customer_response = $this->client->show(Resources::CUSTOMERS, (string) $this->shared_storage->get('user')->get_customer()->get_id());
        $address_response = $this->client->show_by_iri($this->response_checker->get_value($customer_response, 'defaultAddress'));
        Assert::true($this->response_checker->has_value($address_response, 'city', $address->get_city()));
        Assert::true($this->response_checker->has_value($address_response, 'street', $address->get_street()));
        Assert::true($this->response_checker->has_value($address_response, 'countryCode', $address->get_country_code()));
        Assert::true($this->response_checker->has_value($address_response, 'postcode', $address->get_postcode()));
        Assert::true($this->response_checker->has_value($address_response, 'provinceCode', $address->get_province_code()));
        Assert::true($this->response_checker->has_value($address_response, 'provinceName', $address->get_province_name()));
    }
    #[Then('I should still be on the address addition page')]
    public function i_should_still_be_on_the_address_addition_page(): void
    {
        // Intentionally left empty
    }
    #[Then('I should be notified about :expectedCount errors')]
    public function i_should_be_notified_about_errors(int $expected_count): void
    {
        $response = $this->response_checker->get_response_content($this->client->get_last_response());
        Assert::same(count($response['violations']), $expected_count);
    }
    #[Then('I should be notified that the province needs to be specified')]
    public function i_should_be_notified_that_the_province_needs_to_be_specified(): void
    {
        Assert::true($this->response_checker->has_violation_with_message($this->client->get_last_response(), 'Please select proper province.'));
    }
    #[Then('I should still be on the :fullName address edit page')]
    public function i_should_still_be_on_the_address_edit_page(string $full_name): void
    {
        // Intentionally left empty
    }
    #[Then('I should still have :provinceName as my specified province')]
    #[Then('I should still have :provinceName as my chosen province')]
    public function i_should_still_have_as_my_specified_province(string $province_name): void
    {
        Assert::false($this->response_checker->is_update_successful($this->client->get_last_response()));
    }
    #[Then('I should not have a default address')]
    public function i_should_have_no_default_address(): void
    {
        $user_show_response = $this->client->show(Resources::CUSTOMERS, (string) $this->shared_storage->get('user')->get_customer()->get_id());
        Assert::null($this->response_checker->get_value($user_show_response, 'defaultAddress'), 'Default address should be null');
    }
    #[Then('I should be notified that the address has been set as default')]
    public function i_should_be_notified_that_address_has_been_set_as_default(): void
    {
        Assert::true($this->response_checker->is_update_successful($this->client->get_last_response()));
    }
    #[Then('I should not see any details of address')]
    public function i_should_not_see_any_details_of_address(): void
    {
        Assert::true($this->response_checker->has_access_denied($this->client->get_last_response()));
    }
    #[Then('I should not be able to add it')]
    public function i_should_not_be_able_to_do_it(): void
    {
        Assert::true($this->response_checker->has_access_denied($this->client->get_last_response()));
    }
    #[Then('I should not be able to delete it')]
    public function i_should_not_be_able_to_delete_it(): void
    {
        Assert::true($this->response_checker->has_access_denied($this->client->get_last_response()));
    }
    private function address_book_has_address(array $address_book, Address_Interface $address_to_compare): bool
    {
        foreach ($address_book as $address) {
            if ($address['firstName'] === $address_to_compare->get_first_name() && $address['lastName'] === $address_to_compare->get_last_name() && $address['countryCode'] === $address_to_compare->get_country_code() && $address['street'] === $address_to_compare->get_street() && $address['city'] === $address_to_compare->get_city() && $address['postcode'] === $address_to_compare->get_postcode() && $address['provinceName'] === $address_to_compare->get_province_name() && $address['provinceCode'] === $address_to_compare->get_province_code()) {
                return true;
            }
        }
        return false;
    }
    private function get_address_id_from_address_book_by_full_name(string $full_name): ?string
    {
        Assert::not_null($full_name);
        [$first_name, $last_name] = explode(' ', $full_name);
        $addresses = $this->response_checker->get_collection($this->client->get_last_response());
        /** @var AddressInterface $address */
        foreach ($addresses as $address) {
            if ($first_name === $address['firstName'] && $last_name === $address['lastName']) {
                return (string) $address['id'];
            }
        }
        return null;
    }
    private function contains_value(array $data, string $value): bool
    {
        foreach ($data as $data_value) {
            if ($data_value === $value) {
                return true;
            }
        }
        return false;
    }
    private function get_address_iri_from_address_book_by_full_name(string $full_name): ?string
    {
        Assert::not_null($full_name);
        [$first_name, $last_name] = explode(' ', $full_name);
        $addresses = $this->response_checker->get_collection($this->client->index(Resources::ADDRESSES));
        /** @var AddressInterface $address */
        foreach ($addresses as $address) {
            if ($first_name === $address['firstName'] && $last_name === $address['lastName']) {
                return $address['@id'];
            }
        }
        return null;
    }
}
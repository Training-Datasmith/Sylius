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
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Address_Interface;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final class Managing_Placed_Order_Addresses_Context implements Context
{
    /** @var array<string, string> */
    private array $address_properties = ['firstName' => 'getFirstName', 'lastName' => 'getLastName', 'street' => 'getStreet', 'postcode' => 'getPostcode', 'city' => 'getCity', 'countryCode' => 'getCountryCode'];
    public function __construct(private readonly Api_Client_Interface $client, private readonly Response_Checker_Interface $response_checker, private readonly Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I want to modify a customer\'s billing address of this order')]
    public function i_want_to_modify_customer_billing_address(): void
    {
        $this->client->build_update_request(Resources::ADDRESSES, (string) $this->shared_storage->get('order')->get_billing_address()->get_id());
    }
    #[When('I want to modify a customer\'s shipping address of this order')]
    public function i_want_to_modify_customer_shipping_address(): void
    {
        $this->client->build_update_request(Resources::ADDRESSES, (string) $this->shared_storage->get('order')->get_shipping_address()->get_id());
    }
    #[When('/^I clear the (?:billing|shipping) address information$/')]
    public function i_clear_the_address_information(): void
    {
        $this->client->update_request_data(array_fill_keys(array_keys($this->address_properties), ''));
    }
    #[When('/^I do not specify new information$/')]
    public function i_do_not_specify_new_information(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('/^I specify their (?:|new )(?:billing|shipping) (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    public function i_specify_their_address_as(Address_Interface $address): void
    {
        $this->client->add_request_data('firstName', $address->get_first_name());
        $this->client->add_request_data('lastName', $address->get_last_name());
        $this->client->add_request_data('street', $address->get_street());
        $this->client->add_request_data('postcode', $address->get_postcode());
        $this->client->add_request_data('city', $address->get_city());
    }
    #[Then('/^this order should(?:| still) have ("([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" as its(?:| new) billing address)$/')]
    public function its_billing_address_should_contain(Address_Interface $address): void
    {
        $response = $this->client->show(Resources::ADDRESSES, (string) $this->shared_storage->get('order')->get_billing_address()->get_id());
        $this->assert_address_response_properties($response, $address);
    }
    #[Then('/^this order should(?:| still) (be shipped to "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)")$/')]
    public function it_should_be_shipped_to(Address_Interface $address): void
    {
        $response = $this->client->show(Resources::ADDRESSES, (string) $this->shared_storage->get('order')->get_shipping_address()->get_id());
        $this->assert_address_response_properties($response, $address);
    }
    #[Then('/^I should be notified that all mandatory (?:shipping|billing) address details are incomplete$/')]
    public function i_should_be_notified_that_all_mandatory_address_details_are_incomplete(): void
    {
        /** @var array<string, array<string, string>> $mandatoryAddressProperties */
        $mandatory_address_properties = ['firstName' => ['minLength' => '2 characters'], 'lastName' => ['minLength' => '2 characters'], 'street' => ['minLength' => '2 characters'], 'city' => ['minLength' => '2 characters'], 'postcode' => ['minLength' => '1 character']];
        $violations = $this->response_checker->get_error($this->client->get_last_response());
        foreach ($mandatory_address_properties as $property => $constraints) {
            Assert::contains($violations, sprintf('%s: Please enter %s.', $property, $this->camel_case_to_spaces($property)), 'Not found violation for ' . $property . ' property.');
            $formatted_property = ucfirst($this->camel_case_to_spaces($property));
            Assert::contains($violations, sprintf('%s: %s must be at least %s long.', $property, $formatted_property, $constraints['minLength']), 'Not found violation for ' . $property . ' property.');
        }
        Assert::contains($violations, 'countryCode: Please select country.', 'Not found violation for countryCode property.');
    }
    private function assert_address_response_properties(Response $response, Address_Interface $excepted_address): void
    {
        foreach ($this->address_properties as $property => $getter) {
            Assert::same($this->response_checker->get_value($response, $property), $excepted_address->{$getter}());
        }
    }
    private function camel_case_to_spaces(string $string): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', ' $0', $string));
    }
}
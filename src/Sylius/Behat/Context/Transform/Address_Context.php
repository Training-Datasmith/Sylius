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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Bundle\Core_Bundle\Fixture\Factory\Example_Factory_Interface;
use Sylius\Component\Addressing\Converter\Country_Name_Converter_Interface;
use Sylius\Component\Core\Model\Address_Interface;
use Sylius\Component\Core\Repository\Address_Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
use Webmozart\Assert\Assert;
final readonly class Address_Context implements Context
{
    public function __construct(private Factory_Interface $address_factory, private Country_Name_Converter_Interface $country_name_converter, private Address_Repository_Interface $address_repository, private Example_Factory_Interface $example_address_factory)
    {
    }
    #[Transform('/^to "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" based \w+ address$/')]
    #[Transform('/^"([^"]+)" based address$/')]
    public function create_new_address(string $country_name)
    {
        return $this->example_address_factory->create(['country_code' => $this->country_name_converter->convert_to_code($country_name), 'customer' => null]);
    }
    #[Transform('/^address (?:as |is |to )"([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)"$/')]
    #[Transform('/^"([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" specified as$/')]
    public function create_new_address_with($city, $street, $postcode, string $country_name, $customer_name)
    {
        [$first_name, $last_name] = explode(' ', (string) $customer_name);
        return $this->example_address_factory->create(['country_code' => $this->country_name_converter->convert_to_code($country_name), 'first_name' => $first_name, 'last_name' => $last_name, 'company' => null, 'customer' => null, 'phone_number' => null, 'city' => $city, 'street' => $street, 'postcode' => $postcode]);
    }
    #[Transform('/^clear the (shipping|billing) address$/')]
    #[Transform('/^do not specify any (shipping|billing) address$/')]
    public function create_empty_address()
    {
        return $this->address_factory->create_new();
    }
    #[Transform('/^address for "([^"]+)" from "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" addressed it to "([^"]+)", "([^"]+)" "([^"]+)" in the "([^"]+)", "([^"]+)"$/')]
    #[Transform('/^of "([^"]+)" in the "([^"]+)", "([^"]+)" "([^"]+)", "([^"]+)", "([^"]+)"$/')]
    #[Transform('/^addressed it to "([^"]+)", "([^"]+)", "([^"]+)" "([^"]+)" in the "([^"]+)", "([^"]+)"$/')]
    #[Transform('/^address (?:|is |as )"([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)"$/')]
    public function create_new_address_with_name_and_province($name, $street, $postcode, $city, string $country_name, $province_name)
    {
        [$first_name, $last_name] = explode(' ', (string) $name);
        return $this->example_address_factory->create(['country_code' => $this->country_name_converter->convert_to_code($country_name), 'first_name' => $first_name, 'last_name' => $last_name, 'company' => null, 'customer' => null, 'phone_number' => null, 'city' => $city, 'street' => $street, 'postcode' => $postcode, 'province_name' => $province_name]);
    }
    #[Transform('/^"([^"]+)" addressed it to "([^"]+)", "([^"]+)" "([^"]+)" in the "([^"]+)"$/')]
    #[Transform('/^of "([^"]+)" in the "([^"]+)", "([^"]+)" "([^"]+)", "([^"]+)"$/')]
    #[Transform('/^addressed it to "([^"]+)", "([^"]+)", "([^"]+)" "([^"]+)" in the "([^"]+)"$/')]
    #[Transform('/^address (?:|is |as )"([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)"$/')]
    #[Transform('/^"([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" as its(?:| new) billing address$/')]
    #[Transform('/^be shipped to "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)"$/')]
    public function create_new_address_with_name($name, $street, $postcode, $city, string $country_name)
    {
        [$first_name, $last_name] = explode(' ', (string) $name);
        return $this->example_address_factory->create(['country_code' => $this->country_name_converter->convert_to_code($country_name), 'first_name' => $first_name, 'last_name' => $last_name, 'company' => null, 'customer' => null, 'phone_number' => null, 'city' => $city, 'street' => $street, 'postcode' => $postcode]);
    }
    #[Transform('/^"([^"]+)" street$/')]
    public function get_by_street(string $street)
    {
        $address = $this->address_repository->find_one_by(['street' => $street]);
        Assert::not_null($address, sprintf('Cannot find address by %s street.', $street));
        return $address;
    }
    #[Transform('/^address of "([^"]+)"$/')]
    #[Transform('/^address belongs to "([^"]+)"$/')]
    public function get_by_full_name(string $full_name): Address_Interface
    {
        [$first_name, $last_name] = explode(' ', $full_name);
        /** @var AddressInterface $address */
        $address = $this->address_repository->find_one_by(['firstName' => $first_name, 'lastName' => $last_name]);
        Assert::not_null($address, sprintf('Cannot find address by %s full name.', $full_name));
        return $address;
    }
}
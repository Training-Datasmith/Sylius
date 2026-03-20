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
namespace Sylius\Behat\Context\Setup\Checkout;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Sylius\Behat\Service\Factory\Address_Factory_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Api_Bundle\Command\Checkout\Update_Cart;
use Sylius\Component\Addressing\Converter\Country_Name_Converter_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Address_Interface;
use Symfony\Component\Messenger\Message_Bus_Interface;
final readonly class Address_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Message_Bus_Interface $command_bus, private Address_Factory_Interface $address_factory, private Country_Name_Converter_Interface $country_name_converter)
    {
    }
    #[Given('I addressed the cart')]
    #[Given('the customer addressed the cart')]
    public function i_addressed_the_cart(): void
    {
        $this->address_cart();
    }
    #[Given('I addressed the cart with :provinceName province')]
    public function i_addressed_the_cart_with_province(string $province_name): void
    {
        $this->address_cart(billingAddress: $this->address_factory->create_default_with_province_name($province_name));
    }
    #[Given('/^I addressed the cart with "([^"]+)" as the billing address$/')]
    public function i_addressed_the_cart_with_billing_address(string $billing_full_name): void
    {
        $this->address_cart(billingAddress: $this->address_factory->create_default_with_first_and_last_name(...explode(' ', $billing_full_name)));
    }
    #[Given('/^I addressed the cart with "([^"]+)" as the shipping address$/')]
    public function i_addressed_the_cart_with_shipping_address(string $shipping_full_name): void
    {
        $this->address_cart(shippingAddress: $this->address_factory->create_default_with_first_and_last_name(...explode(' ', $shipping_full_name)));
    }
    #[Given('/^I addressed the cart with "([^"]+)" as the billing address and "([^"]+)" as the shipping address$/')]
    public function i_addressed_the_cart_with_billing_and_shipping_address(string $billing_full_name, string $shipping_full_name): void
    {
        $this->address_cart(billingAddress: $this->address_factory->create_default_with_first_and_last_name(...explode(' ', $billing_full_name)), shippingAddress: $this->address_factory->create_default_with_first_and_last_name(...explode(' ', $shipping_full_name)));
    }
    #[Given('/^I addressed the cart with email "([^"]+)"$/')]
    #[Given('/^the (?:customer|visitor) addressed the cart with email "([^"]+)"$/')]
    public function i_addressed_the_cart_with_email(string $email): void
    {
        $this->address_cart(email: $email);
    }
    #[Given('I addressed the cart to :countryName')]
    public function i_addressed_the_cart_to_country(string $country_name): void
    {
        $this->address_cart(billingAddress: $this->address_factory->create_default_with_country_code($this->country_name_converter->convert_to_code($country_name)));
    }
    #[Given('/^I have specified the billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    public function i_have_specified_default_billing_address_for_name(): void
    {
        $this->address_cart(billingAddress: $this->address_factory->create_default_with_country_code('US'));
    }
    public function address_cart(?string $cart_token = null, ?string $email = null, ?Address_Interface $billing_address = null, ?Address_Interface $shipping_address = null): void
    {
        if ($email === null && $this->shared_storage->has('user')) {
            $email = $this->shared_storage->get('user')->get_email();
        }
        $billing_address ??= $shipping_address ?? $this->address_factory->create_default();
        $shipping_address ??= $billing_address ?? $this->address_factory->create_default();
        $this->command_bus->dispatch(new Update_Cart(orderTokenValue: $cart_token ?? $this->shared_storage->get('cart_token'), email: $email, billingAddress: $billing_address, shippingAddress: $shipping_address));
        $this->store_addresses($billing_address, $shipping_address);
    }
    private function store_addresses(?Address_Interface $billing_address = null, ?Address_Interface $shipping_address = null): void
    {
        if ($billing_address === null && $shipping_address !== null) {
            $billing_address = clone $shipping_address;
        }
        if ($shipping_address === null && $billing_address !== null) {
            $shipping_address = clone $billing_address;
        }
        $billing_key = sprintf('billing_address_%s', String_Inflector::name_to_lowercase_code($billing_address->get_first_name() . ' ' . $billing_address->get_last_name()));
        $this->shared_storage->set($billing_key, $billing_address);
        $shipping_key = sprintf('shipping_address_%s', String_Inflector::name_to_lowercase_code($shipping_address->get_first_name() . ' ' . $shipping_address->get_last_name()));
        $this->shared_storage->set($shipping_key, $shipping_address);
    }
}
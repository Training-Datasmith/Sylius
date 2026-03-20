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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Address_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\Core\Repository\Address_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Address_Context implements Context
{
    public function __construct(private Address_Repository_Interface $address_repository, private Object_Manager $customer_manager, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Given('/^(their) default (address is "[^"]+", "[^"]+", "[^"]+", "[^"]+" for "[^"]+")$/')]
    #[Given('/^(their) default (address is "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+")$/')]
    public function their_default_address_is(Customer_Interface $customer, Address_Interface $address): void
    {
        $this->set_default_address_of_customer($customer, $address);
    }
    #[Given('/^(my) default address is of "([^"]+)"$/')]
    public function my_default_address_is_of(Shop_User_Interface $user, string $full_name): void
    {
        [$first_name, $last_name] = explode(' ', $full_name);
        /** @var AddressInterface $address */
        $address = $this->address_repository->find_one_by(['firstName' => $first_name, 'lastName' => $last_name]);
        Assert::not_null($address, sprintf('The address of "%s" has not been found.', $full_name));
        /** @var CustomerInterface $customer */
        $customer = $user->get_customer();
        $this->set_default_address_of_customer($customer, $address);
    }
    #[Given('/^(I) have an (address "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+"(?:|, "[^"]+")) in my address book$/')]
    public function i_have_an_address_in_address_book(Shop_User_Interface $user, Address_Interface $address): void
    {
        /** @var CustomerInterface $customer */
        $customer = $user->get_customer();
        $this->add_address_to_customer($customer, $address);
        $this->shared_storage->set('address', $address);
    }
    #[Given('this address has province :province')]
    public function this_address_has_province(string $province_name): void
    {
        $address = $this->shared_storage->get('address');
        $address->set_province_name($province_name);
        $this->customer_manager->flush();
    }
    #[Given('/^(this customer) has an (address "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+"(?:|, "[^"]+")) in their address book$/')]
    #[Given('/^(this customer) has an? ("[^"]+" based address) in their address book$/')]
    public function this_customer_has_an_address_in_address_book(Customer_Interface $customer, Address_Interface $address): void
    {
        $this->add_address_to_customer($customer, $address);
    }
    private function add_address_to_customer(Customer_Interface $customer, Address_Interface $address): void
    {
        $customer->add_address($address);
        $this->customer_manager->flush();
        $this->shared_storage->set('address_assigned_to_' . $customer->get_full_name(), $address);
    }
    private function set_default_address_of_customer(Customer_Interface $customer, Address_Interface $address): void
    {
        $customer->set_default_address($address);
        $this->customer_manager->flush();
    }
}
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
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Core\Model\Address_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\Core\Repository\Customer_Repository_Interface;
use Sylius\Component\Customer\Model\Customer_Group_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final class Customer_Context implements Context
{
    use Secure_Password_Trait;
    public function __construct(private Shared_Storage_Interface $shared_storage, private Customer_Repository_Interface $customer_repository, private Object_Manager $customer_manager, private Factory_Interface $customer_factory, private Factory_Interface $user_factory, private Factory_Interface $address_factory)
    {
    }
    #[Given('the store has customer :name with email :email')]
    public function the_store_has_customer_with_name_and_email($name, $email): void
    {
        $parts_of_name = explode(' ', (string) $name);
        $customer = $this->create_customer($email, $parts_of_name[0], $parts_of_name[1]);
        $this->customer_repository->add($customer);
        $this->shared_storage->set('customer', $customer);
    }
    #[Given('the store (also )has customer :email')]
    public function the_store_has_customer($email): void
    {
        $customer = $this->create_customer($email);
        $this->customer_repository->add($customer);
    }
    #[Given('the store has customer :email with first name :firstName')]
    public function the_store_has_customer_with_first_name($email, $first_name): void
    {
        $customer = $this->create_customer($email, $first_name);
        $this->customer_repository->add($customer);
    }
    #[Given('the store has customer :email with name :fullName since :since')]
    #[Given('the store has customer :email with name :fullName and phone number :phoneNumber since :since')]
    public function the_store_has_customer_with_name_and_registration_date($email, $full_name, $since, $phone_number = null): void
    {
        $names = explode(' ', (string) $full_name);
        $customer = $this->create_customer($email, $names[0], $names[1], new \DateTime($since), $phone_number);
        $this->customer_repository->add($customer);
    }
    #[Given('there is disabled customer account :email with password :password')]
    public function there_is_disabled_customer_account_with_password($email, $password): void
    {
        $customer = $this->create_customer_with_user_account($email, $password, false);
        $this->customer_repository->add($customer);
    }
    #[Given('there is a customer account :email')]
    #[Given('there is a customer account :email identified by :password')]
    #[Given('there is enabled customer account :email with password :password')]
    public function the_store_has_enabled_customer_account_with_password($email, $password = 'sylius'): void
    {
        $customer = $this->create_customer_with_user_account($email, $password, true);
        $this->customer_repository->add($customer);
    }
    #[Given('there is a customer :name identified by an email :email and a password :password')]
    #[Given('there is a customer :name with an email :email and a password :password')]
    public function the_store_has_customer_account_with_email_and_password(string $name, string $email, string $password): void
    {
        $this->create_customer_with_full_name_email_and_password($name, $email, $password);
    }
    #[Given('there is a customer :name with an email :email')]
    #[Given('there is also a customer :name with an email :email')]
    public function the_store_has_customer_account_with_email_and_name(string $name, string $email): void
    {
        $this->create_customer_with_full_name_email_and_password($name, $email, 'sylius');
    }
    #[Given('/^(the customer) subscribed to the newsletter$/')]
    public function the_customer_subscribed_to_the_newsletter(Customer_Interface $customer): void
    {
        $customer->set_subscribed_to_newsletter(true);
        $this->customer_manager->flush();
    }
    #[Given('/^(this customer) verified their email$/')]
    public function the_customer_verified_their_email(Customer_Interface $customer): void
    {
        $customer->get_user()->set_verified_at(new \DateTime());
        $this->customer_manager->flush();
    }
    #[Given('/^(the customer) belongs to (group "([^"]+)")$/')]
    #[Given('/^(this customer) belongs to (group "([^"]+)")$/')]
    public function the_customer_belongs_to_group(Customer_Interface $customer, Customer_Group_Interface $customer_group): void
    {
        $customer->set_group($customer_group);
        $this->customer_manager->flush();
    }
    #[Given('there is user :email with :country as shipping country')]
    public function there_is_user_identified_by_with_as_shipping_country($email, Country_Interface $country): void
    {
        $customer = $this->create_customer_with_user_account($email, 'password123', true, 'John', 'Doe');
        /** @var AddressInterface $address */
        $address = $this->address_factory->create_new();
        $address->set_country_code($country->get_code());
        $address->set_city('Berlin');
        $address->set_first_name($customer->get_first_name());
        $address->set_last_name($customer->get_last_name());
        $address->set_street('street');
        $address->set_postcode('123');
        $customer->set_default_address($address);
        $this->customer_repository->add($customer);
    }
    /**
     * @param string $email
     *
     * @return CustomerInterface
     */
    private function create_customer(?string $email, ?string $first_name = null, ?string $last_name = null, ?\DateTimeInterface $created_at = null, ?string $phone_number = null)
    {
        /** @var CustomerInterface $customer */
        $customer = $this->customer_factory->create_new();
        $customer->set_first_name($first_name);
        $customer->set_last_name($last_name);
        $customer->set_email($email);
        $customer->set_phone_number($phone_number);
        if (null !== $created_at) {
            $customer->set_created_at($created_at);
        }
        $this->shared_storage->set('customer', $customer);
        return $customer;
    }
    /**
     * @param string $email
     * @param string|null $role
     *
     * @return CustomerInterface
     */
    private function create_customer_with_user_account(?string $email, string $password, bool $enabled = true, ?string $first_name = null, ?string $last_name = null, $role = null)
    {
        /** @var ShopUserInterface $user */
        $user = $this->user_factory->create_new();
        /** @var CustomerInterface $customer */
        $customer = $this->customer_factory->create_new();
        $customer->set_first_name($first_name);
        $customer->set_last_name($last_name);
        $customer->set_email($email);
        $customer->set_phone_number('123456789');
        $customer->set_gender('m');
        $user->set_username($email);
        $user->set_plain_password($this->replace_with_secure_password($password));
        $user->set_enabled($enabled);
        if (null !== $role) {
            $user->add_role($role);
        }
        $customer->set_user($user);
        $this->shared_storage->set('customer', $customer);
        return $customer;
    }
    private function create_customer_with_full_name_email_and_password(string $name, string $email, string $password): void
    {
        $names = explode(' ', $name);
        $first_name = $names[0];
        $last_name = count($names) > 1 ? $names[1] : null;
        $customer = $this->create_customer_with_user_account($email, $password, true, $first_name, $last_name);
        $this->customer_repository->add($customer);
    }
}
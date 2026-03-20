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
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Customer\Model\Customer_Group_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Customer_Group_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Repository_Interface $customer_group_repository, private Factory_Interface $customer_group_factory)
    {
    }
    #[Given('the store has a customer group :name')]
    #[Given('the store has a customer group :name with :code code')]
    public function the_store_has_a_customer_group($name, $code = null): void
    {
        $this->create_customer_group($name, $code);
    }
    #[Given('the store has customer groups :firstName and :secondName')]
    #[Given('the store has customer groups :firstName, :secondName and :thirdName')]
    public function the_store_has_customer_groups(string ...$names): void
    {
        foreach ($names as $name) {
            $this->the_store_has_a_customer_group($name);
        }
    }
    /**
     * @param string $name
     * @param string $code
     */
    private function create_customer_group($name, $code): void
    {
        /** @var CustomerGroupInterface $customerGroup */
        $customer_group = $this->customer_group_factory->create_new();
        $customer_group->set_code($code ?: $this->generate_code_from_name($name));
        $customer_group->set_name(ucfirst($name));
        $this->shared_storage->set('customer_group', $customer_group);
        $this->customer_group_repository->add($customer_group);
    }
    private function generate_code_from_name(string $name): string
    {
        return String_Inflector::name_to_code($name);
    }
}
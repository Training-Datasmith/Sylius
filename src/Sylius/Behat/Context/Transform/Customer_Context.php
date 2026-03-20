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
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Repository\Customer_Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Customer_Context implements Context
{
    public function __construct(private Customer_Repository_Interface $customer_repository, private Factory_Interface $customer_factory, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Transform(':customer')]
    #[Transform('/^customer "([^"]+)"$/')]
    public function get_or_create_customer_by_email(?string $email)
    {
        /** @var CustomerInterface $customer */
        $customer = $this->customer_repository->find_one_by(['email' => $email]);
        if (null === $customer) {
            /** @var CustomerInterface $customer */
            $customer = $this->customer_factory->create_new();
            $customer->set_email($email);
            $this->customer_repository->add($customer);
        }
        return $customer;
    }
    #[Transform('/^(he|his|she|her|their|the customer of my account)$/')]
    public function get_last_customer()
    {
        return $this->shared_storage->get('customer');
    }
}
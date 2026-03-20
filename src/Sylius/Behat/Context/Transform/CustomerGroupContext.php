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
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Customer_Group_Context implements Context
{
    public function __construct(private Repository_Interface $customer_group_repository)
    {
    }
    #[Transform(':customerGroup')]
    #[Transform('/^group "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" group$/')]
    public function get_customer_group_by_name(string $customer_group_name)
    {
        $customer_group = $this->customer_group_repository->find_one_by(['name' => $customer_group_name]);
        Assert::not_null($customer_group, sprintf('Cannot find customer group with name %s', $customer_group_name));
        return $customer_group;
    }
}
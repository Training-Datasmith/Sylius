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
namespace Sylius\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\Persistence\Object_Manager;
use Sylius\Component\Shipping\Model\Shipping_Method_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Shipping_Methods_Context implements Context
{
    public function __construct(private Repository_Interface $shipping_method_repository, private Object_Manager $shipping_method_manager)
    {
    }
    #[When('/^I archive the ("[^"]+" shipping method)$/')]
    public function i_archive_the_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $shipping_method->set_archived_at(new \DateTime());
        $this->shipping_method_manager->flush();
    }
    #[Then('the shipping method :shippingMethod should still exist in the registry')]
    public function the_shipping_method_should_still_exist_in_the_registry(Shipping_Method_Interface $shipping_method): void
    {
        Assert::not_null($this->shipping_method_repository->find($shipping_method));
    }
}
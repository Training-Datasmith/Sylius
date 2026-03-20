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
use Sylius\Component\Core\Repository\Shipment_Repository_Interface;
use Sylius\Component\Shipping\Model\Shipping_Method_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Shipments_Context implements Context
{
    public function __construct(private Shipment_Repository_Interface $shipment_repository)
    {
    }
    #[Then('/^there should be no shipments with ("[^"]+" shipping method) in the registry$/')]
    public function shipment_should_not_exist_in_the_registry(Shipping_Method_Interface $shipping_method): void
    {
        $shipments = $this->shipment_repository->find_by(['method' => $shipping_method]);
        Assert::same($shipments, []);
    }
}
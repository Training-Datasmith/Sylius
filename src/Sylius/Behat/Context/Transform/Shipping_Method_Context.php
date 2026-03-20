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
use Sylius\Component\Shipping\Repository\Shipping_Method_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Shipping_Method_Context implements Context
{
    public function __construct(private Shipping_Method_Repository_Interface $shipping_method_repository)
    {
    }
    #[Transform('/^"([^"]+)" shipping method$/')]
    #[Transform('/^shipping method "([^"]+)"$/')]
    #[Transform(':shippingMethod')]
    public function get_shipping_method_by_name(string $shipping_method_name)
    {
        $shipping_methods = $this->shipping_method_repository->find_by_name($shipping_method_name, 'en_US');
        Assert::eq(count($shipping_methods), 1, sprintf('%d shipping methods have been found with name "%s".', count($shipping_methods), $shipping_method_name));
        return $shipping_methods[0];
    }
}
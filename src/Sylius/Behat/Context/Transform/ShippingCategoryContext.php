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
class Shipping_Category_Context implements Context
{
    public function __construct(private readonly Repository_Interface $shipping_category_repository)
    {
    }
    #[Transform('/^"([^"]+)" shipping category/')]
    #[Transform('/^shipping category "([^"]+)"/')]
    #[Transform('/^shipping category with name "([^"]+)"$/')]
    #[Transform(':shippingCategory')]
    public function get_shipping_category_by_name($shipping_category_name)
    {
        $shipping_categories = $this->shipping_category_repository->find_by(['name' => $shipping_category_name]);
        Assert::eq(count($shipping_categories), 1, sprintf('%d shipping category has been found with name "%s".', count($shipping_categories), $shipping_category_name));
        return $shipping_categories[0];
    }
}
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
use Sylius\Component\Product\Repository\Product_Association_Type_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Association_Type_Context implements Context
{
    public function __construct(private Product_Association_Type_Repository_Interface $product_association_type_repository)
    {
    }
    #[Transform('/^association "([^"]+)"$/')]
    #[Transform('/^associate as "([^"]+)"$/')]
    #[Transform(':productAssociationType')]
    public function get_product_association_type_by_name(string $product_association_type_name)
    {
        $product_association_types = $this->product_association_type_repository->find_by_name($product_association_type_name, 'en_US');
        Assert::eq(count($product_association_types), 1, sprintf('%d product association types has been found with name "%s".', count($product_association_types), $product_association_type_name));
        return $product_association_types[0];
    }
}
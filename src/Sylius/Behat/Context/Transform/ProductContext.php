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
use Sylius\Component\Core\Repository\Product_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Context implements Context
{
    public function __construct(private Product_Repository_Interface $product_repository, private string $locale = 'en_US')
    {
    }
    #[Transform('/^product(?:|s) "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" product(?:|s)$/')]
    #[Transform('/^(?:a|an) "([^"]+)"$/')]
    #[Transform(':product')]
    #[Transform(':firstProduct')]
    #[Transform(':secondProduct')]
    public function get_product_by_name(string $product_name)
    {
        $products = $this->product_repository->find_by_name($product_name, $this->locale);
        Assert::eq(count($products), 1, sprintf('@Transform issue, cannot retrieve "%s" product', $product_name));
        return $products[0];
    }
    #[Transform('/^products "([^"]+)" and "([^"]+)"$/')]
    #[Transform('/^products "([^"]+)", "([^"]+)" and "([^"]+)"$/')]
    public function get_products_by_names(...$products_names): array
    {
        return array_map($this->get_product_by_name(...), $products_names);
    }
}
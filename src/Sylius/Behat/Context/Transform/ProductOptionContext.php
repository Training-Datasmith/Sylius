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
use Sylius\Component\Product\Repository\Product_Option_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Option_Context implements Context
{
    public function __construct(private Product_Option_Repository_Interface $product_option_repository)
    {
    }
    #[Transform('/^product option "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" option$/')]
    #[Transform(':productOption')]
    public function get_product_option_by_name(string $product_option_name)
    {
        $product_options = $this->product_option_repository->find_by_name($product_option_name, 'en_US');
        Assert::eq(count($product_options), 1, sprintf('%d product options has been found with name "%s".', count($product_options), $product_option_name));
        return $product_options[0];
    }
}
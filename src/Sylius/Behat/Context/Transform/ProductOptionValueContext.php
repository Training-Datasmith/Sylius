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
use Sylius\Component\Product\Model\Product_Option_Value_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Option_Value_Context implements Context
{
    public function __construct(private Repository_Interface $product_option_value_repository)
    {
    }
    #[Transform('/^"([^"]+)" option value$/')]
    #[Transform(':optionValue')]
    #[Transform(':productOptionValue')]
    public function get_product_option_value_by_code(string $code): Product_Option_Value_Interface
    {
        $product_option_values = $this->product_option_value_repository->find_by(['code' => $code]);
        Assert::count($product_option_values, 1, sprintf('%d product option values have been found with name "%s" but should be only one.', count($product_option_values), $code));
        return $product_option_values[0];
    }
}
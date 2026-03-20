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
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Repository\Product_Repository_Interface;
use Sylius\Component\Product\Repository\Product_Variant_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Variant_Context implements Context
{
    public function __construct(private Product_Repository_Interface $product_repository, private Product_Variant_Repository_Interface $product_variant_repository, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Transform('/^"([^"]+)" variant of product "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" variant of "([^"]+)" product$/')]
    public function get_product_variant_by_name_and_product(string $variant_name, string $product_name): Product_Variant_Interface
    {
        $products = $this->product_repository->find_by_name($product_name, 'en_US');
        Assert::eq(count($products), 1, sprintf('%d products has been found with name "%s".', count($products), $product_name));
        $product_variants = $this->product_variant_repository->find_by_name_and_product($variant_name, 'en_US', $products[0]);
        Assert::not_empty($product_variants, sprintf('Product variant with name "%s" of product "%s" does not exist', $variant_name, $product_name));
        return $product_variants[0];
    }
    #[Transform('/^"([^"]+)" variant of this product$/')]
    public function get_product_variant_by_name_and_this_product(string $variant_name): Product_Variant_Interface
    {
        $product = $this->shared_storage->get('product');
        $product_variants = $this->product_variant_repository->find_by_name_and_product($variant_name, 'en_US', $product);
        Assert::not_empty($product_variants, sprintf('Product variant with name "%s" of product "%s" does not exist', $variant_name, $product->get_name()));
        return $product_variants[0];
    }
    #[Transform('/^"([^"]+)" product variant$/')]
    #[Transform('/^"([^"]+)" variant$/')]
    #[Transform('/^variant "([^"]+)"$/')]
    #[Transform(':productVariant')]
    #[Transform(':variant')]
    public function get_product_variant_by_name(string $name)
    {
        $product_variants = $this->product_variant_repository->find_by_name($name, 'en_US');
        Assert::eq(count($product_variants), 1, sprintf('%d product variants has been found with name "%s".', count($product_variants), $name));
        return $product_variants[0];
    }
    #[Transform('/^"([^"]+)", "([^"]+)" and "([^"]+)" variants$/')]
    public function get_variants_by_names(string ...$variant_names): array
    {
        return array_map(fn(string $variant_name) => $this->get_product_variant_by_name($variant_name), $variant_names);
    }
    #[Transform('/^variant with code "([^"]+)"$/')]
    public function get_product_variant_by_code(string $code)
    {
        $product_variant = $this->product_variant_repository->find_one_by(['code' => $code]);
        Assert::not_null($product_variant, sprintf('Cannot find product variant with code %s', $code));
        return $product_variant;
    }
    #[Transform('/^"([^"]*)" (\w+) \/ "([^"]*)" (\w+) variant of product "([^"]+)"$/')]
    public function get_variant_by_option_values_and_product(string $value1, string $option1, string $value2, string $option2, string $product_name)
    {
        $products = $this->product_repository->find_by_name($product_name, 'en_US');
        Assert::eq(count($products), 1, sprintf('%d products has been found with name "%s".', count($products), $product_name));
        $product = $products[0];
        $option1 = String_Inflector::name_to_uppercase_code($option1);
        $option2 = String_Inflector::name_to_uppercase_code($option2);
        foreach ($product->get_variants() as $variant) {
            $options = [];
            foreach ($variant->get_option_values() as $option_value) {
                $options[$option_value->get_option()->get_code()] = $option_value->get_value();
            }
            if (array_key_exists($option1, $options) && $options[$option1] === $value1 && array_key_exists($option2, $options) && $options[$option2] === $value2) {
                return $variant;
            }
        }
        throw new \InvalidArgumentException(sprintf('Cannot find variant "%s" %s / "%s" %s within product "%s"', $value1, $option1, $value2, $option2, $product->get_code()));
    }
}
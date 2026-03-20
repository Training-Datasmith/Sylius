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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Product\Model\Product_Option_Interface;
use Sylius\Component\Product\Model\Product_Option_Value_Interface;
use Sylius\Component\Product\Repository\Product_Option_Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Product_Option_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Product_Option_Repository_Interface $product_option_repository, private Factory_Interface $product_option_factory, private Factory_Interface $product_option_value_factory, private Object_Manager $object_manager)
    {
    }
    #[Given('the store has (also) a product option :name')]
    #[Given('the store has a product option :name with a code :code')]
    public function the_store_has_a_product_option_with_a_code(string $name, ?string $code = null): void
    {
        $product_option = $this->create_product_option($name, $code);
        $this->shared_storage->set('product_option', $product_option);
    }
    #[Given('/^the store has(?:| also) a product option "([^"]+)" at position ([^"]+)$/')]
    public function the_store_has_a_product_option_at_position($name, $position): void
    {
        $this->create_product_option($name, null, $position);
    }
    #[Given('/^(this product option) has(?:| also) the "([^"]+)" option value with code "([^"]+)"$/')]
    public function this_product_option_has_the_option_value_with_code(Product_Option_Interface $product_option, $product_option_value_name, $product_option_value_code): void
    {
        $product_option_value = $this->create_product_option_value($product_option_value_name, $product_option_value_code);
        $product_option->add_value($product_option_value);
        $this->object_manager->flush();
    }
    /**
     * @param string $name
     * @param string|null $position
     * @return ProductOptionInterface
     */
    private function create_product_option($name, ?string $code = null, $position = null)
    {
        /** @var ProductOptionInterface $productOption */
        $product_option = $this->product_option_factory->create_new();
        $product_option->set_name($name);
        $product_option->set_code($code ?: String_Inflector::name_to_code($name));
        $product_option->set_position(null === $position ? null : (int) $position);
        $this->shared_storage->set('product_option', $product_option);
        $this->product_option_repository->add($product_option);
        return $product_option;
    }
    /**
     * @param string $value
     * @param string $code
     *
     * @return ProductOptionValueInterface
     */
    private function create_product_option_value(?string $value, $code)
    {
        /** @var ProductOptionValueInterface $productOptionValue */
        $product_option_value = $this->product_option_value_factory->create_new();
        $product_option_value->set_value($value);
        $product_option_value->set_code($code);
        return $product_option_value;
    }
}
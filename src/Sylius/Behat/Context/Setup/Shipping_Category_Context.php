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
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Shipping\Model\Shipping_Category_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Shipping_Category_Context implements Context
{
    /**
     * @param FactoryInterface<ShippingCategoryInterface> $shippingCategoryFactory
     * @param RepositoryInterface<ShippingCategoryInterface> $shippingCategoryRepository
     */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Factory_Interface $shipping_category_factory, private Repository_Interface $shipping_category_repository)
    {
    }
    #[Given('the store has :firstShippingCategoryName shipping category')]
    #[Given('the store has :firstShippingCategoryName and :secondShippingCategoryName shipping category')]
    public function the_store_has_and_shipping_category(string $first_shipping_category_name, ?string $second_shipping_category_name = null): void
    {
        $this->create_shipping_category($first_shipping_category_name);
        null === $second_shipping_category_name ?: $this->create_shipping_category($second_shipping_category_name);
    }
    #[Given('the store has :shippingCategoryName shipping category identified by :shippingCategoryCode')]
    public function the_store_has_shipping_category_identified_by($shipping_category_name, $shipping_category_code): void
    {
        $this->create_shipping_category($shipping_category_name, $shipping_category_code);
    }
    /**
     * @param string $shippingCategoryName
     * @param string $shippingCategoryCode
     */
    private function create_shipping_category(?string $shipping_category_name, $shipping_category_code = null): void
    {
        /** @var ShippingCategoryInterface $shippingCategory */
        $shipping_category = $this->shipping_category_factory->create_new();
        $shipping_category->set_name($shipping_category_name);
        $shipping_category->set_code($shipping_category_code);
        if (null === $shipping_category_code) {
            $shipping_category->set_code(String_Inflector::name_to_code($shipping_category_name));
        }
        $this->shipping_category_repository->add($shipping_category);
        $this->shared_storage->set('shipping_category', $shipping_category);
    }
}
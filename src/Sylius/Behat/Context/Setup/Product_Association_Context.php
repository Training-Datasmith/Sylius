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
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Product\Model\Product_Association_Interface;
use Sylius\Component\Product\Model\Product_Association_Type_Interface;
use Sylius\Component\Product\Model\Product_Association_Type_Translation_Interface;
use Sylius\Component\Product\Repository\Product_Association_Type_Repository_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Product_Association_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Factory_Interface $product_association_type_factory, private Factory_Interface $product_association_type_translation_factory, private Factory_Interface $product_association_factory, private Product_Association_Type_Repository_Interface $product_association_type_repository, private Repository_Interface $product_association_repository, private Object_Manager $object_manager)
    {
    }
    #[Given('the store has (also) a product association type :name')]
    #[Given('the store has (also) a product association type :name with a code :code')]
    public function the_store_has_a_product_association_type($name, $code = null): void
    {
        $this->create_product_association_type($name, $code);
    }
    #[Given('/^the store has(?:| also) a product association type named "([^"]+)" in ("[^"]+" locale) and "([^"]+)" in ("[^"]+" locale)$/')]
    public function it_has_variant_named_in_and_in($first_name, $first_locale, $second_name, $second_locale): void
    {
        $product_association_type = $this->create_product_association_type($first_name);
        $names = [$first_name => $first_locale, $second_name => $second_locale];
        foreach ($names as $name => $locale) {
            $this->add_product_association_type_translation($product_association_type, $name, $locale);
        }
        $this->object_manager->flush();
    }
    #[Given('the store has :firstName and :secondName product association types')]
    public function the_store_has_product_association_types(...$names): void
    {
        foreach ($names as $name) {
            $this->create_product_association_type($name);
        }
    }
    #[Given('the store has :firstName product association type')]
    public function the_store_has_product_association_type($name): void
    {
        $this->create_product_association_type($name);
    }
    #[Given('/^the (product "[^"]+") has(?:| also) an (association "[^"]+") with (product "[^"]+")$/')]
    public function the_product_has_an_association_with_product(Product_Interface $product, Product_Association_Type_Interface $product_association_type, Product_Interface $associated_product): void
    {
        $this->create_product_association($product, $product_association_type, [$associated_product]);
    }
    #[Given('/^the (product "[^"]+") has(?:| also) an (association "[^"]+") with (products "[^"]+" and "[^"]+")$/')]
    public function the_product_has_an_association_with_products(Product_Interface $product, Product_Association_Type_Interface $product_association_type, array $associated_products): void
    {
        $this->create_product_association($product, $product_association_type, $associated_products);
    }
    /**
     * @param string $name
     * @param string|null $code
     *
     * @return ProductAssociationTypeInterface
     */
    private function create_product_association_type($name, $code = null)
    {
        if (null === $code) {
            $code = $this->generate_code_from_name($name);
        }
        /** @var ProductAssociationTypeInterface $productAssociationType */
        $product_association_type = $this->product_association_type_factory->create_new();
        $product_association_type->set_code($code);
        $product_association_type->set_name($name);
        $this->product_association_type_repository->add($product_association_type);
        $this->shared_storage->set('product_association_type', $product_association_type);
        return $product_association_type;
    }
    private function create_product_association(Product_Interface $product, Product_Association_Type_Interface $product_association_type, array $associated_products): void
    {
        /** @var ProductAssociationInterface $productAssociation */
        $product_association = $this->product_association_factory->create_new();
        $product_association->set_type($product_association_type);
        foreach ($associated_products as $associated_product) {
            $product_association->add_associated_product($associated_product);
        }
        $product->add_association($product_association);
        $this->product_association_repository->add($product_association);
        $this->shared_storage->set('product_association', $product_association);
    }
    private function add_product_association_type_translation(Product_Association_Type_Interface $product_association_type, string $name, string $locale): void
    {
        /** @var ProductAssociationTypeTranslationInterface $translation */
        $translation = $this->product_association_type_translation_factory->create_new();
        $translation->set_locale($locale);
        $translation->set_name($name);
        $product_association_type->add_translation($translation);
    }
    /**
     * @param string $name
     */
    private function generate_code_from_name($name): string
    {
        return str_replace([' ', '-'], '_', strtolower($name));
    }
}
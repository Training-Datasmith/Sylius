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
namespace Sylius\Bundle\Admin_Bundle\Form\Data_Transformer;

use Doctrine\Common\Collections\Array_Collection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Product\Model\Product_Association_Interface;
use Sylius\Component\Product\Model\Product_Association_Type_Interface;
use Sylius\Component\Product\Model\Product_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
use Symfony\Component\Form\Data_Transformer_Interface;
use Webmozart\Assert\Assert;
/**
 * @implements DataTransformerInterface<Collection<array-key, ProductAssociationInterface>, array<string, Collection<array-key, ProductInterface>>>
 */
final class Products_To_Product_Associations_Transformer implements Data_Transformer_Interface
{
    /** @var Collection<array-key, ProductAssociationInterface>|null */
    private ?Collection $product_associations = null;
    /**
     * @param FactoryInterface<ProductAssociationInterface> $productAssociationFactory
     * @param RepositoryInterface<ProductAssociationTypeInterface> $productAssociationTypeRepository
     */
    public function __construct(private readonly Factory_Interface $product_association_factory, private readonly Repository_Interface $product_association_type_repository)
    {
    }
    /**
     * @return array<string, Collection<array-key, ProductInterface>>
     */
    public function transform(mixed $value): array
    {
        $this->set_product_associations($value);
        if ($value->is_empty()) {
            return [];
        }
        $values = [];
        /** @var ProductAssociationInterface $productAssociation */
        foreach ($value as $product_association) {
            $values[$product_association->get_type()->get_code()] = clone $product_association->get_associated_products();
        }
        return $values;
    }
    public function reverse_transform(mixed $value): ?Collection
    {
        if (!is_array($value)) {
            return null;
        }
        /** @var Collection<array-key, ProductAssociationInterface> $productAssociations */
        $product_associations = new Array_Collection();
        foreach ($value as $product_association_type_code => $products) {
            if ($products->is_empty()) {
                continue;
            }
            $product_association = $this->get_product_association_by_type_code((string) $product_association_type_code);
            $this->link_products_to_association($product_association, $products);
            $product_associations->add($product_association);
        }
        $this->set_product_associations(null);
        return $product_associations;
    }
    private function get_product_association_by_type_code(string $product_association_type_code): Product_Association_Interface
    {
        foreach ($this->product_associations as $product_association) {
            if ($product_association_type_code === $product_association->get_type()->get_code()) {
                return $product_association;
            }
        }
        /** @var ProductAssociationTypeInterface $productAssociationType */
        $product_association_type = $this->product_association_type_repository->find_one_by(['code' => $product_association_type_code]);
        /** @var ProductAssociationInterface $productAssociation */
        $product_association = $this->product_association_factory->create_new();
        $product_association->set_type($product_association_type);
        return $product_association;
    }
    /**
     * @param Collection<array-key, ProductInterface> $products
     */
    private function link_products_to_association(Product_Association_Interface $product_association, Collection $products): void
    {
        $product_association->clear_associated_products();
        foreach ($products as $product) {
            Assert::is_instance_of($product, Product_Interface::class);
            $product_association->add_associated_product($product);
        }
    }
    /**
     * @param Collection<array-key, ProductAssociationInterface>|null $productAssociations
     */
    private function set_product_associations(?Collection $product_associations): void
    {
        $this->product_associations = $product_associations instanceof Collection ? $product_associations : new Array_Collection();
    }
}
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
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Taxon_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Product_Taxon_Context implements Context
{
    public function __construct(private Factory_Interface $product_taxon_factory, private Object_Manager $object_manager)
    {
    }
    #[Given('/^I assigned (this product) to ("[^"]+" taxon)$/')]
    #[Given('/^(it|this product) (belongs to "[^"]+")$/')]
    #[Given('/^(this product) is in ("[^"]+" taxon) at (\d)(?:st|nd|rd|th) position$/')]
    #[Given('the product :product belongs to taxon :taxon')]
    public function it_belongs_to(Product_Interface $product, Taxon_Interface $taxon, $position = null): void
    {
        $product_taxon = $this->create_product_taxon($taxon, $product, (int) $position - 1);
        $product->add_product_taxon($product_taxon);
        $this->object_manager->persist($product);
        $this->object_manager->flush();
    }
    #[Given('/^(it|this product) (belongs to "[^"]+" and "[^"]+")$/')]
    public function it_belongs_to_and(Product_Interface $product, iterable $taxons): void
    {
        foreach ($taxons as $taxon) {
            $product_taxon = $this->create_product_taxon($taxon, $product);
            $product->add_product_taxon($product_taxon);
        }
        $this->object_manager->persist($product);
        $this->object_manager->flush();
    }
    #[Given('the product :product has a main taxon :taxon')]
    #[Given('/^(this product) has a main (taxon "[^"]+")$/')]
    public function product_has_main_taxon(Product_Interface $product, Taxon_Interface $taxon): void
    {
        $product->set_main_taxon($taxon);
        $this->object_manager->flush();
    }
    private function create_product_taxon(Taxon_Interface $taxon, Product_Interface $product, ?int $position = null): Product_Taxon_Interface
    {
        /** @var ProductTaxonInterface $productTaxon */
        $product_taxon = $this->product_taxon_factory->create_new();
        $product_taxon->set_product($product);
        $product_taxon->set_taxon($taxon);
        if (null !== $position) {
            $product_taxon->set_position($position);
        }
        return $product_taxon;
    }
}
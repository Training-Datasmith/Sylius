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
namespace Sylius\Behat\Element\Admin\Product;

use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Product\Model\Product_Association_Type_Interface;
interface Associations_Form_Element_Interface
{
    /**
     * @param string[] $productsNames
     */
    public function associate_products(Product_Association_Type_Interface $product_association_type, array $products_names): void;
    public function remove_associated_product(Product_Interface $product, Product_Association_Type_Interface $product_association_type): void;
    public function has_associated_product(Product_Interface $product, Product_Association_Type_Interface $product_association_type): bool;
}
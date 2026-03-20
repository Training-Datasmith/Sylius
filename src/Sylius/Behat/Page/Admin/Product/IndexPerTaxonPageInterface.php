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
namespace Sylius\Behat\Page\Admin\Product;

use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as CrudIndexPageInterface;
interface Index_Per_Taxon_Page_Interface extends Crud_Index_Page_Interface
{
    public function get_product_position(string $product_name): int;
    public function has_products_in_order(array $product_names): bool;
    public function set_position_of_product(string $product_name, string $position): void;
    public function save_positions(): void;
    public function filter_by_name(string $name): void;
}
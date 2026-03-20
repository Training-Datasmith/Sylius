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
namespace Sylius\Behat\Page\Admin\Catalog_Promotion\Product_Variant;

use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as BaseIndexPageInterface;
interface Index_Page_Interface extends Base_Index_Page_Interface
{
    public function show_product_of(int $variant_id): void;
    public function filter_by_code(string $code): void;
    public function filter_by_name(string $name): void;
}
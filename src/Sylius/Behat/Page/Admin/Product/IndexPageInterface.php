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
interface Index_Page_Interface extends Crud_Index_Page_Interface
{
    public function filter_by_taxon(string $taxon_name): void;
    public function filter_by_main_taxon(string $taxon_name): void;
    public function has_product_accessible_image(string $product_code): bool;
    public function show_product_page(string $product_name): void;
    public function choose_channel_filter(string $channel_name): void;
    public function filter(): void;
    public function go_to_page(int $page): void;
    public function check_first_product_has_data_attribute(string $attribute_name): bool;
    public function check_last_product_has_data_attribute(string $attribute_name): bool;
    public function get_page_number(): int;
}
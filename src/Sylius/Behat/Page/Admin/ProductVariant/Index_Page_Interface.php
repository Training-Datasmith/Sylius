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
namespace Sylius\Behat\Page\Admin\Product_Variant;

use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as BaseIndexPageInterface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
interface Index_Page_Interface extends Base_Index_Page_Interface
{
    public function get_on_hand_quantity_for(Product_Variant_Interface $product_variant): int;
    public function get_on_hold_quantity_for(Product_Variant_Interface $product_variant): int;
    public function set_position(string $name, int $position): void;
    public function save_positions(): void;
    public function count_items_with_no_name(): int;
    public function has_generate_variants_button(): bool;
    public function go_to_variant_generation(): void;
}
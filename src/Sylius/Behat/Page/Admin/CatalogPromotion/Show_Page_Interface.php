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
namespace Sylius\Behat\Page\Admin\Catalog_Promotion;

use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
interface Show_Page_Interface extends Page_Interface
{
    public function get_name(): string;
    public function get_start_date(): string;
    public function get_end_date(): string;
    public function get_priority(): int;
    public function has_action_with_percentage_discount(string $amount): bool;
    public function has_action_with_fixed_discount(string $amount, Channel_Interface $channel): bool;
    public function has_scope_with_variant(Product_Variant_Interface $variant): bool;
    public function has_scope_with_product(Product_Interface $product): bool;
    public function is_exclusive(): bool;
}
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

use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface as BaseCreatePageInterface;
interface Generate_Page_Interface extends Base_Create_Page_Interface
{
    public function generate(): void;
    public function specify_price(int $nth, int $price, string $channel_code): void;
    public function specify_code(int $nth, string $code): void;
    public function remove_variant(int $nth): void;
    public function is_generation_possible(): bool;
    public function is_product_variant_removable(int $nth): bool;
}
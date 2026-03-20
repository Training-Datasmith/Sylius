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
namespace Sylius\Behat\Page\Admin\Channel_Pricing_Log_Entry;

use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as BaseIndexPageInterface;
interface Index_Page_Interface extends Base_Index_Page_Interface
{
    public function is_log_entry_with_price_and_original_price(string $price, string $original_price): bool;
    public function is_log_entry_with_price_and_original_price_on_position(string $price, string $original_price, int $position): bool;
}
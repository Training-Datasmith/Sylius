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

use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Webmozart\Assert\Assert;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function is_log_entry_with_price_and_original_price(string $price, string $original_price): bool
    {
        $available_prices = $this->get_column_fields('price');
        $available_original_prices = $this->get_column_fields('originalPrice');
        $dates = $this->get_column_fields('loggedAt');
        foreach ($available_prices as $key => $value) {
            Assert::not_empty($dates[$key]);
            if ($available_prices[$key] === $price && $available_original_prices[$key] === $original_price) {
                return true;
            }
        }
        return false;
    }
    public function is_log_entry_with_price_and_original_price_on_position(string $price, string $original_price, int $position): bool
    {
        $available_prices = $this->get_column_fields('price');
        $available_original_prices = $this->get_column_fields('originalPrice');
        $dates = $this->get_column_fields('loggedAt');
        Assert::not_empty($dates[$position - 1]);
        return $available_prices[$position - 1] === $price && $available_original_prices[$position - 1] === $original_price;
    }
}
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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Page\Admin\Channel_Pricing_Log_Entry\Index_Page_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Webmozart\Assert\Assert;
final readonly class Channel_Pricing_Log_Entry_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page)
    {
    }
    #[When('/^I go to the price history of a (variant with code "[^"]+")$/')]
    public function i_go_to_the_price_history_of_a_variant(Product_Variant_Interface $product_variant): void
    {
        $channel_pricing = $product_variant->get_channel_pricings()->first();
        $product = $product_variant->get_product();
        $this->index_page->open(['productId' => $product->get_id(), 'variantId' => $product_variant->get_id(), 'channelPricingId' => $channel_pricing->get_id()]);
    }
    #[Then('I should see :count log entries in the catalog price history')]
    #[Then('I should see a single log entry in the catalog price history')]
    public function i_should_see_log_entries_in_the_catalog_price_history_for_the_variant(int $count = 1): void
    {
        Assert::same($this->index_page->count_items(), $count);
    }
    #[Then('/^there should be a log entry on the (\d+)(?:|st|nd|rd|th) position with the "([^"]+)" selling price, "([^"]+)" original price and datetime of the price change$/')]
    #[Then('/^there should be a log entry on the (\d+)(?:|st|nd|rd|th) position with the "([^"]+)" selling price, no original price and datetime of the price change$/')]
    public function there_should_be_a_log_entry_on_the_position_with_the_selling_price_original_price_and_datetime_of_the_price_change(int $position, string $price, string $original_price = '-'): void
    {
        Assert::true($this->index_page->is_log_entry_with_price_and_original_price_on_position($price, $original_price, $position));
    }
    #[Then('/^there should be a log entry with the "([^"]+)" selling price, "([^"]+)" original price and datetime of the price change$/')]
    #[Then('/^there should be a log entry with the "([^"]+)" selling price, no original price and datetime of the price change$/')]
    public function there_should_be_a_log_entry_with_the_selling_price_original_price_and_datetime_of_the_price_change(string $price, string $original_price = '-'): void
    {
        Assert::true($this->index_page->is_log_entry_with_price_and_original_price($price, $original_price));
    }
}
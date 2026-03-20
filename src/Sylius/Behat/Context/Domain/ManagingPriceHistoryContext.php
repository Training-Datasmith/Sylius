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
namespace Sylius\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Bundle\Core_Bundle\Price_History\Remover\Channel_Pricing_Log_Entries_Remover_Interface;
use Sylius\Component\Core\Model\Channel_Pricing_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Product\Resolver\Product_Variant_Resolver_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Price_History_Context implements Context
{
    public function __construct(private Repository_Interface $channel_pricing_log_entry_repository, private Product_Variant_Resolver_Interface $variant_resolver, private Channel_Pricing_Log_Entries_Remover_Interface $channel_pricing_log_entries_remover)
    {
    }
    #[When('I delete price history older than :days day(s)')]
    public function i_delete_price_history_older_than_days(int $days): void
    {
        $this->channel_pricing_log_entries_remover->remove($days);
    }
    #[Then('/^there should be (\d+) price history entries for (this product)$/')]
    public function there_should_be_count_price_history_entries_for_this_product(int $count, Product_Interface $product): void
    {
        $channel_pricing_log_entries = $this->channel_pricing_log_entry_repository->find_by(['channelPricing' => $this->get_channel_pricing_from_product($product)]);
        Assert::count($channel_pricing_log_entries, $count);
    }
    #[Then('/^(this product) should have no entry with original price changed to ("[^"]+")$/')]
    public function this_product_should_have_no_entry_with_original_price_changed_to(Product_Interface $product, int $original_price): void
    {
        Assert::null($this->channel_pricing_log_entry_repository->find_one_by(['channelPricing' => $this->get_channel_pricing_from_product($product), 'originalPrice' => $original_price]));
    }
    #[Then('/^(this product)\'s price history should be empty$/')]
    public function this_products_price_history_should_be_empty(Product_Interface $product): void
    {
        $this->there_should_be_count_price_history_entries_for_this_product(0, $product);
    }
    private function get_channel_pricing_from_product(Product_Interface $product): Channel_Pricing_Interface
    {
        $variant = $this->variant_resolver->get_variant($product);
        Assert::not_null($variant);
        $channel_pricing = $variant->get_channel_pricings()->first();
        Assert::is_instance_of($channel_pricing, Channel_Pricing_Interface::class);
        return $channel_pricing;
    }
}
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
namespace Sylius\Behat\Context\Api\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Webmozart\Assert\Assert;
final readonly class Channel_Pricing_Log_Entry_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('/^I go to the price history of a (variant with code "[^"]+")$/')]
    public function i_go_to_the_price_history_of_a_variant(Product_Variant_Interface $product_variant): void
    {
        $channel = $this->shared_storage->get('channel');
        Assert::not_null($channel);
        $this->shared_storage->set('variant', $product_variant);
        $this->client->index(Resources::CHANNEL_PRICING_LOG_ENTRIES);
        $this->client->add_filter('channelPricing.channelCode', $channel->get_code());
        $this->client->add_filter('channelPricing.productVariant.code', $product_variant->get_code());
        $this->client->filter();
    }
    #[Then('I should see :count log entries in the catalog price history')]
    #[Then('I should see a single log entry in the catalog price history')]
    public function i_should_see_log_entries_in_the_catalog_price_history_for_the_variant(int $count = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('/^there should be a log entry on the (\d+)(?:|st|nd|rd|th) position with the ("[^"]+") selling price, (no|"[^"]+") original price and datetime of the price change$/')]
    public function there_should_be_a_log_entry_on_the_position_with_the_selling_price_original_price_and_datetime_of_the_price_change(int $position, int $price, int|string $original_price): void
    {
        if ('no' === $original_price) {
            $original_price = null;
        }
        $log_entry = $this->response_checker->get_collection($this->client->get_last_response())[$position - 1];
        Assert::same($log_entry['price'], $price);
        Assert::same($log_entry['originalPrice'], $original_price);
        Assert::key_exists($log_entry, 'loggedAt');
    }
    #[Then('/^there should be a log entry with the ("[^"]+") selling price, (no|"[^"]+") original price and datetime of the price change$/')]
    public function there_should_be_a_log_entry_with_the_selling_price_original_price_and_datetime_of_the_price_change(int $price, int|string $original_price): void
    {
        $this->there_should_be_a_log_entry_on_the_position_with_the_selling_price_original_price_and_datetime_of_the_price_change(1, $price, $original_price);
    }
}
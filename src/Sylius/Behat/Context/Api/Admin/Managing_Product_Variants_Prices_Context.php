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
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
final readonly class Managing_Product_Variants_Prices_Context implements Context
{
    public function __construct(private Api_Client_Interface $client)
    {
    }
    #[When('/^I change the price of the ("[^"]+" product variant) to ("[^"]+") in ("[^"]+" channel)$/')]
    public function i_change_the_price_of_the_product_variant_in_channel(Product_Variant_Interface $variant, int $price, Channel_Interface $channel): void
    {
        $this->update_channel_pricing_field($variant, $channel, $price, 'price');
    }
    #[When('/^I change the original price of the ("[^"]+" product variant) to ("[^"]+") in ("[^"]+" channel)$/')]
    public function i_change_the_original_price_of_the_product_variant_in_channel(Product_Variant_Interface $variant, int $original_price, Channel_Interface $channel): void
    {
        $this->update_channel_pricing_field($variant, $channel, $original_price, 'originalPrice');
    }
    #[When('/^I remove the original price of the ("[^"]+" product variant) in ("[^"]+" channel)$/')]
    public function i_remove_the_original_price_of_the_product_variant_in_channel(Product_Variant_Interface $variant, Channel_Interface $channel): void
    {
        $this->update_channel_pricing_field($variant, $channel, null, 'originalPrice');
    }
    private function update_channel_pricing_field(Product_Variant_Interface $variant, Channel_Interface $channel, ?int $price, string $field): void
    {
        $this->client->build_update_request(Resources::PRODUCT_VARIANTS, $variant->get_code());
        $content = $this->client->get_content();
        $content['channelPricings'][$channel->get_code()][$field] = $price;
        $this->client->update_request_data($content);
        $this->client->update();
    }
}
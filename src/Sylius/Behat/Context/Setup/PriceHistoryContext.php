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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\Persistence\Object_Manager;
use Sylius\Component\Core\Model\Channel_Pricing_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Product\Resolver\Product_Variant_Resolver_Interface;
use Webmozart\Assert\Assert;
final readonly class Price_History_Context implements Context
{
    public function __construct(private Calendar_Context $calendar_context, private Object_Manager $channel_pricing_manager, private Product_Variant_Resolver_Interface $default_variant_resolver)
    {
    }
    #[Given('/^on "([^"]+)" (its) price changed to ("[^"]+")$/')]
    public function on_day_its_price_changed_to(string $date, Product_Interface $product, int $price): void
    {
        $this->calendar_context->it_is_now($date);
        $channel_pricing = $this->get_channel_pricing_from_product($product);
        $channel_pricing->set_price($price);
        $this->channel_pricing_manager->flush();
    }
    #[Given('/^on "([^"]+)" (its) original price changed to ("[^"]+")$/')]
    public function on_day_its_original_price_changed_to(string $date, Product_Interface $product, int $original_price): void
    {
        $this->calendar_context->it_is_now($date);
        $channel_pricing = $this->get_channel_pricing_from_product($product);
        $channel_pricing->set_original_price($original_price);
        $this->channel_pricing_manager->flush();
    }
    #[Given('/^on "([^"]+)" (its) price changed to ("[^"]+") and original price to ("[^"]+")$/')]
    public function on_day_its_original_price_changed_to_and_original_price_to(string $date, Product_Interface $product, int $price, int $original_price): void
    {
        $this->calendar_context->it_is_now($date);
        $channel_pricing = $this->get_channel_pricing_from_product($product);
        $channel_pricing->set_price($price);
        $channel_pricing->set_original_price($original_price);
        $this->channel_pricing_manager->flush();
    }
    #[Given('/^on "([^"]+)" (its) original price has been removed$/')]
    public function on_day_its_original_price_has_been_removed(string $date, Product_Interface $product): void
    {
        $this->calendar_context->it_is_now($date);
        $channel_pricing = $this->get_channel_pricing_from_product($product);
        $channel_pricing->set_original_price(null);
        $this->channel_pricing_manager->flush();
    }
    private function get_channel_pricing_from_product(Product_Interface $product): Channel_Pricing_Interface
    {
        $variant = $this->default_variant_resolver->get_variant($product);
        Assert::is_instance_of($variant, Product_Variant_Interface::class);
        $channel_pricing = $variant->get_channel_pricings()->first();
        Assert::is_instance_of($channel_pricing, Channel_Pricing_Interface::class);
        return $channel_pricing;
    }
}
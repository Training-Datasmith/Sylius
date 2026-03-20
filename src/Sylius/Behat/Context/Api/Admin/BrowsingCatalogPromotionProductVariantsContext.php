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

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Component\Core\Model\Catalog_Promotion_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Webmozart\Assert\Assert;
final readonly class Browsing_Catalog_Promotion_Product_Variants_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter)
    {
    }
    #[Given('I am browsing variants affected by catalog promotion :catalogPromotion')]
    #[When('I browse variants affected by catalog promotion :catalogPromotion')]
    public function i_browse_variants_affected_by_catalog_promotion(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->client->index(Resources::PRODUCT_VARIANTS);
        $this->client->add_filter('catalogPromotion', $this->iri_converter->get_iri_from_resource($catalog_promotion));
        $this->client->filter();
    }
    #[When('/^I want to view all variants of (this product)$/')]
    #[When('/^I view(?:| all) variants of the (product "[^"]+")$/')]
    public function i_want_to_view_all_variants_of_this_product(Product_Interface $product): void
    {
        $this->client->index(Resources::PRODUCT_VARIANTS);
        $this->client->add_filter('product', $this->iri_converter->get_iri_from_resource($product));
        $this->client->filter();
    }
    #[When('I filter by code containing :phrase')]
    public function i_filter_by_code_containing(string $phrase): void
    {
        $this->client->add_filter('code', $phrase);
        $this->client->filter();
    }
    #[When('I filter by name containing :phrase')]
    public function i_filter_by_name_containing(string $phrase): void
    {
        $this->client->add_filter('translations.name', $phrase);
        $this->client->filter();
    }
    #[Then('/^there should be (\d+) product variants? on the list$/')]
    public function there_should_be_product_variants_on_the_list(int $count): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('it should be the :variantName product variant')]
    #[Then('it should be :firstVariant and :secondVariant product variants')]
    public function the_product_variant_should_be_in_the_registry(string ...$variants_names): void
    {
        foreach ($variants_names as $variant_name) {
            Assert::true($this->response_checker->has_item_with_translation($this->client->get_last_response(), 'en_US', 'name', $variant_name));
        }
    }
    #[Then(':variant variant price should be decreased by catalog promotion :catalogPromotion in :channel channel')]
    public function variant_price_should_be_decreased_by_catalog_promotion(Product_Variant_Interface $variant, Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): void
    {
        Assert::true($this->variant_has_catalog_promotion_in_channel($variant, $catalog_promotion, $channel), sprintf('Catalog promotion "%s" was not found in applied promotions of variant "%s" in channel "%s".', $catalog_promotion->get_code(), $variant->get_code(), $channel->get_code()));
    }
    #[Then(':variant variant price should not be decreased by catalog promotion :catalogPromotion in :channel channel')]
    public function variant_price_should_not_be_decreased_by_catalog_promotion(Product_Variant_Interface $variant, Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): void
    {
        Assert::false($this->variant_has_catalog_promotion_in_channel($variant, $catalog_promotion, $channel), sprintf('Catalog promotion "%s" was found in applied promotions of variant "%s" in channel "%s".', $catalog_promotion->get_code(), $variant->get_code(), $channel->get_code()));
    }
    private function variant_has_catalog_promotion_in_channel(Product_Variant_Interface $variant, Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): bool
    {
        $variant_data = $this->get_data_of_variant_with_code($variant->get_code());
        $promotions = $variant_data['channelPricings'][$channel->get_code()]['appliedPromotions'] ?? [];
        foreach ($promotions as $promotion) {
            if ($promotion['code'] === $catalog_promotion->get_code()) {
                return true;
            }
        }
        return false;
    }
    private function get_data_of_variant_with_code(string $code): array
    {
        $variants_data = $this->response_checker->get_collection($this->client->get_last_response());
        foreach ($variants_data as $variant_data) {
            if ($variant_data['code'] === $code) {
                return $variant_data;
            }
        }
        throw new \InvalidArgumentException(sprintf('Variant with code "%s" was not found.', $code));
    }
}
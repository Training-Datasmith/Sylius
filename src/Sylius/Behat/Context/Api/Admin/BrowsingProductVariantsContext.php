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
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Webmozart\Assert\Assert;
final readonly class Browsing_Product_Variants_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[When('I start sorting variants by position')]
    public function i_sort_products_by_position(): void
    {
        $this->client->index(Resources::PRODUCT_VARIANTS, ['order[position]' => 'desc']);
    }
    #[When('I set the position of :productVariant to :position')]
    public function i_set_the_position_of_to(Product_Variant_Interface $product_variant, int $position): void
    {
        $this->client->build_update_request(Resources::PRODUCT_VARIANTS, $product_variant->get_code());
        $this->client->update_request_data(['position' => $position]);
    }
    #[When('I save my new elements order')]
    public function i_save_my_new_elements_order(): void
    {
        $this->client->update();
    }
    #[Then('the first variant in the list should have name :variantName')]
    public function the_first_variant_in_the_list_should_have_name(string $variant_name): void
    {
        $variants = $this->response_checker->get_collection($this->client->get_last_response());
        $first_variant = reset($variants);
        $this->assert_product_variant_name($first_variant['translations']['en_US']['name'], $variant_name);
    }
    #[Then('the last variant in the list should have name :variantName')]
    public function the_last_variant_in_the_list_should_have_name(string $variant_name): void
    {
        $variants = $this->response_checker->get_collection($this->client->get_last_response());
        $last_variant = end($variants);
        $this->assert_product_variant_name($last_variant['translations']['en_US']['name'], $variant_name);
    }
    private function assert_product_variant_name(string $variant_name, string $expected_variant_name): void
    {
        Assert::same($variant_name, $expected_variant_name, sprintf('Expected product variant to have name "%s", but it is named "%s".', $expected_variant_name, $variant_name));
    }
}
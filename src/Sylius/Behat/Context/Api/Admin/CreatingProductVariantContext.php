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
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
final readonly class Creating_Product_Variant_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Iri_Converter_Interface $iri_converter)
    {
    }
    #[When('/^I create a new "([^"]+)" variant priced at ("[^"]+") for ("[^"]+" product) in the ("[^"]+" channel)$/')]
    public function i_create_a_new_variant_priced_at_for_product_in_the_channel(string $name, int $price, Product_Interface $product, Channel_Interface $channel): void
    {
        $this->client->build_create_request(Resources::PRODUCT_VARIANTS);
        $this->client->add_request_data('product', $this->iri_converter->get_iri_from_resource($product));
        $this->client->add_request_data('code', String_Inflector::name_to_code($name));
        $this->client->add_request_data('channelPricings', [$channel->get_code() => ['price' => $price, 'channelCode' => $channel->get_code()]]);
        $this->client->create();
    }
}
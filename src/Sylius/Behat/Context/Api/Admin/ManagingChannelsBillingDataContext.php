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
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Channels_Billing_Data_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[Then('/^(this channel) company should be "([^"]+)"$/')]
    public function this_channel_company_should_be(Channel_Interface $channel, string $company): void
    {
        $shop_billing_data = $this->get_shop_billing_data_from_channel($channel);
        Assert::same($shop_billing_data['company'], $company);
    }
    #[Then('/^(this channel) tax ID should be "([^"]+)"$/')]
    public function this_channel_tax_id_should_be(Channel_Interface $channel, string $tax_id): void
    {
        $shop_billing_data = $this->get_shop_billing_data_from_channel($channel);
        Assert::same($shop_billing_data['taxId'], $tax_id);
    }
    #[Then('/^(this channel) shop billing address should be "([^"]+)", "([^"]+)" "([^"]+)" and ("([^"]+)" country)$/')]
    #[Then('/^(this channel) shop billing address should still be "([^"]+)", "([^"]+)" "([^"]+)" and ("([^"]+)" country)$/')]
    public function this_channel_shop_billing_address_should_be(Channel_Interface $channel, string $street, string $postcode, string $city, Country_Interface $country): void
    {
        $shop_billing_data = $this->get_shop_billing_data_from_channel($channel);
        Assert::same($shop_billing_data['street'], $street);
        Assert::same($shop_billing_data['postcode'], $postcode);
        Assert::same($shop_billing_data['city'], $city);
        Assert::same($shop_billing_data['countryCode'], $country->get_code());
    }
    /**
     * @return array<string, string>
     */
    private function get_shop_billing_data_from_channel(Channel_Interface $channel): array
    {
        $response = $this->client->show(Resources::CHANNELS, $channel->get_code());
        return $this->response_checker->get_value($response, 'shopBillingData');
    }
}
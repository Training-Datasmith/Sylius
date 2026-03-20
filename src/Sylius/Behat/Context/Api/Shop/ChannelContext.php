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
namespace Sylius\Behat\Context\Api\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Webmozart\Assert\Assert;
final readonly class Channel_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private string $api_url_prefix)
    {
    }
    #[When('/^I (?:am browsing|start browsing|try to browse|browse) (?:|the )("[^"]+" channel)$/')]
    #[When('/^I (?:start browsing|try to browse|browse) (that channel)$/')]
    #[When('/^I (?:am browsing|start browsing|try to browse|browse) (?:|the )(channel "[^"]+")$/')]
    public function i_am_browsing_channel(Channel_Interface $channel): void
    {
        $this->shared_storage->set('hostname', $channel->get_hostname());
        $this->shared_storage->remove('current_locale_code');
        $this->client->show(Resources::CHANNELS, $channel->get_code());
    }
    #[Then('I should (still) shop using the :currencyCode currency')]
    public function i_should_shop_using_the_currency(string $currency_code): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'baseCurrency'), sprintf('%s/shop/currencies/%s', $this->api_url_prefix, $currency_code));
    }
    #[Then('I should be able to shop using the :currencyCode currency')]
    public function i_should_be_able_to_shop_using_the_currency(string $currency_code): void
    {
        $this->client->index(Resources::CURRENCIES);
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'code', $currency_code));
    }
    #[Then('I should not be able to shop using the :currencyCode currency')]
    public function i_should_not_be_able_to_shop_using_the_currency(string $currency_code): void
    {
        $this->client->index(Resources::CURRENCIES);
        Assert::false($this->response_checker->has_item_with_value($this->client->get_last_response(), 'code', $currency_code));
    }
}
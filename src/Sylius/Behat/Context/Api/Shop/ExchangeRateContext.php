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
use Webmozart\Assert\Assert;
final readonly class Exchange_Rate_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[When('I get exchange rates of the store')]
    public function i_get_exchange_rates_of_the_store(): void
    {
        $this->client->index(Resources::EXCHANGE_RATES);
    }
    #[Then('I should see :count exchange rates on the list')]
    public function i_should_see_exchange_rates_on_the_list(int $count): void
    {
        Assert::count($this->response_checker->get_collection($this->client->get_last_response()), $count);
    }
    #[Then('I should see that the exchange rate of :sourceCurrency to :targetCurrency is :ratio')]
    public function i_should_see_that_exchange_rate_of_source_currency_to_target_currency_is(string $source_currency, string $target_currency, float $ratio): void
    {
        $exchange_rate = $this->get_exchange_rate_by_target_currency($source_currency, $target_currency);
        Assert::same($exchange_rate['ratio'], $ratio);
    }
    #[Then('I should not see :sourceCurrency to :targetCurrency exchange rate')]
    public function i_should_not_see_source_currency_to_target_currency_exchange_rate(string $source_currency, string $target_currency): void
    {
        Assert::throws(fn(): array => $this->get_exchange_rate_by_target_currency($source_currency, $target_currency), \RuntimeException::class, sprintf('Cannot find %s/%s exchange rate.', $source_currency, $target_currency));
    }
    private function get_exchange_rate_by_target_currency(string $source_currency_code, string $target_currency_code): array
    {
        $exchange_rates = $this->response_checker->get_collection($this->client->get_last_response());
        foreach ($exchange_rates as $exchange_rate) {
            if (str_ends_with((string) $exchange_rate['sourceCurrency'], $source_currency_code) && str_ends_with((string) $exchange_rate['targetCurrency'], $target_currency_code)) {
                return $exchange_rate;
            }
        }
        throw new \RuntimeException(sprintf('Cannot find %s/%s exchange rate.', $source_currency_code, $target_currency_code));
    }
}
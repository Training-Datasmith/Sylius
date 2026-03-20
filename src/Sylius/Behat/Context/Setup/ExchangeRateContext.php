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
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Currency\Model\Currency_Interface;
use Sylius\Component\Currency\Model\Exchange_Rate_Interface;
use Sylius\Component\Currency\Repository\Exchange_Rate_Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Exchange_Rate_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Factory_Interface $exchange_rate_factory, private Exchange_Rate_Repository_Interface $exchange_rate_repository)
    {
    }
    #[Given('the exchange rate of :sourceCurrency to :targetCurrency is :ratio')]
    public function there_is_an_exchange_rate_with_source_currency_and_target_currency(Currency_Interface $source_currency, Currency_Interface $target_currency, $ratio): void
    {
        $exchange_rate = $this->create_exchange_rate($source_currency, $target_currency, $ratio);
        $this->save_exchange_rate($exchange_rate);
    }
    /**
     * @param float $ratio
     *
     * @return ExchangeRateInterface
     */
    private function create_exchange_rate(Currency_Interface $source_currency, Currency_Interface $target_currency, $ratio = 1.0)
    {
        /** @var ExchangeRateInterface $exchangeRate */
        $exchange_rate = $this->exchange_rate_factory->create_new();
        $exchange_rate->set_source_currency($source_currency);
        $exchange_rate->set_target_currency($target_currency);
        $exchange_rate->set_ratio((float) $ratio);
        return $exchange_rate;
    }
    private function save_exchange_rate(Exchange_Rate_Interface $exchange_rate): void
    {
        $this->exchange_rate_repository->add($exchange_rate);
        $this->shared_storage->set('exchange_rate', $exchange_rate);
    }
}
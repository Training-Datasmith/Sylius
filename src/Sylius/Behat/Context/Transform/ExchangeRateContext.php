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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Component\Currency\Converter\Currency_Name_Converter_Interface;
use Sylius\Component\Currency\Model\Exchange_Rate_Interface;
use Sylius\Component\Currency\Repository\Exchange_Rate_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Exchange_Rate_Context implements Context
{
    public function __construct(private Currency_Name_Converter_Interface $currency_name_converter, private Exchange_Rate_Repository_Interface $exchange_rate_repository)
    {
    }
    #[Transform('/^exchange rate between "([^"]+)" and "([^"]+)"$/')]
    public function get_exchange_rate_by_currencies(string $source_currency_name, string $target_currency_name): Exchange_Rate_Interface
    {
        $source_currency_code = $this->currency_name_converter->convert_to_code($source_currency_name);
        $target_currency_code = $this->currency_name_converter->convert_to_code($target_currency_name);
        /** @var ExchangeRateInterface|null $exchangeRate */
        $exchange_rate = $this->exchange_rate_repository->find_one_with_currency_pair($source_currency_code, $target_currency_code);
        Assert::not_null($exchange_rate, sprintf('ExchangeRate for %s and %s currencies does not exist.', $source_currency_name, $target_currency_name));
        return $exchange_rate;
    }
}
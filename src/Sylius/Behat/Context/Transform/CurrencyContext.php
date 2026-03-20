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
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Currency_Context implements Context
{
    public function __construct(private Currency_Name_Converter_Interface $currency_name_converter, private Repository_Interface $currency_repository)
    {
    }
    #[Transform(':currency')]
    #[Transform(':sourceCurrency')]
    #[Transform(':targetCurrency')]
    #[Transform('/^currency "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" currency$/')]
    public function get_currency_by_name(string $currency_name)
    {
        $currency = $this->currency_repository->find_one_by(['code' => $this->get_currency_code_by_name($currency_name)]);
        Assert::not_null($currency, sprintf('Currency with name %s does not exist.', $currency_name));
        return $currency;
    }
    #[Transform(':currencyCode')]
    #[Transform(':secondCurrencyCode')]
    #[Transform(':thirdCurrencyCode')]
    public function get_currency_code_by_name($currency_name): string
    {
        // If it's already a currency code - just return it.
        if (strlen((string) $currency_name) === 3 && strtoupper((string) $currency_name) === $currency_name) {
            return $currency_name;
        }
        return $this->currency_name_converter->convert_to_code($currency_name);
    }
}
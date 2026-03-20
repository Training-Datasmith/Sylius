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
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Currency\Model\Currency_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Currency_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Repository_Interface $currency_repository, private Factory_Interface $currency_factory, private Object_Manager $channel_manager)
    {
    }
    #[Given('the store has currency :currencyCode')]
    public function the_store_has_currency($currency_code): void
    {
        $currency = $this->create_currency($currency_code);
        $this->save_currency($currency);
    }
    #[Given('the store has currency :currencyCode, :secondCurrencyCode')]
    #[Given('the store has currency :currencyCode and :secondCurrencyCode')]
    #[Given('the store has currency :currencyCode, :secondCurrencyCode and :thirdCurrencyCode')]
    public function the_store_has_currency_and($currency_code, $second_currency_code, $third_currency_code = null): void
    {
        $this->save_currency($this->create_currency($currency_code));
        $this->save_currency($this->create_currency($second_currency_code));
        if (null !== $third_currency_code) {
            $this->save_currency($this->create_currency($third_currency_code));
        }
    }
    #[Given('the currency :currencyCode has been disabled')]
    public function the_store_has_disabled_currency($currency_code): void
    {
        $currency = $this->provide_currency($currency_code);
        $this->save_currency($currency);
    }
    #[Given('/^(that channel|"[^"]+" channel)(?: also|) allows to shop using the "([^"]+)" currency$/')]
    #[Given('/^(that channel|"[^"]+" channel)(?: also|) allows to shop using "([^"]+)" and "([^"]+)" currencies$/')]
    #[Given('/^(that channel)(?: also|) allows to shop using "([^"]+)", "([^"]+)" and "([^"]+)" currencies$/')]
    public function that_channel_allows_to_shop_using_and_currencies(Channel_Interface $channel, ...$currencies_codes): void
    {
        foreach ($currencies_codes as $currency_code) {
            $channel->add_currency($this->provide_currency($currency_code));
        }
        $this->channel_manager->flush();
    }
    private function save_currency(Currency_Interface $currency): void
    {
        $this->shared_storage->set('currency', $currency);
        $this->currency_repository->add($currency);
    }
    /**
     * @param string $currencyCode
     *
     * @return CurrencyInterface
     */
    private function create_currency($currency_code)
    {
        /** @var CurrencyInterface $currency */
        $currency = $this->currency_factory->create_new();
        $currency->set_code($currency_code);
        return $currency;
    }
    /**
     * @param string $currencyCode
     *
     * @return CurrencyInterface
     */
    private function provide_currency($currency_code)
    {
        $currency = $this->currency_repository->find_one_by(['code' => $currency_code]);
        if (null === $currency) {
            /** @var CurrencyInterface $currency */
            $currency = $this->create_currency($currency_code);
            $this->currency_repository->add($currency);
        }
        return $currency;
    }
}
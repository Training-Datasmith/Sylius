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
namespace Sylius\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Page\Shop\Home_Page_Interface;
use Webmozart\Assert\Assert;
final readonly class Currency_Context implements Context
{
    public function __construct(private Home_Page_Interface $home_page)
    {
    }
    #[When('I browse currencies')]
    public function i_browse_currencies(): void
    {
        $this->home_page->open();
    }
    #[Given('I changed my currency to :currencyCode')]
    #[When('I switch to the :currencyCode currency')]
    public function i_switch_the_currency_to_the_currency(string $currency_code): void
    {
        $this->home_page->open();
        $this->home_page->switch_currency($currency_code);
    }
    #[Then('I should (still) shop using the :currencyCode currency')]
    public function i_should_shop_using_the_currency(string $currency_code): void
    {
        $this->home_page->open();
        Assert::same($this->home_page->get_active_currency(), $currency_code);
    }
    #[Then('I should be able to shop using the :currencyCode currency')]
    public function i_should_be_able_to_shop_using_the_currency(string $currency_code): void
    {
        $this->home_page->open();
        Assert::one_of($currency_code, $this->home_page->get_available_currencies());
    }
    #[Then('I should not be able to shop using the :currencyCode currency')]
    public function i_should_not_be_able_to_shop_using_the_currency(string $currency_code): void
    {
        $this->home_page->open();
        if (in_array($currency_code, $this->home_page->get_available_currencies(), true)) {
            throw new \InvalidArgumentException(sprintf('Expected "%s" not to be in "%s"', $currency_code, implode('", "', $this->home_page->get_available_currencies())));
        }
    }
    #[Then('I should see :firstCurrency and :secondCurrency in the list')]
    public function i_should_see_currencies_in_the_list(string ...$currencies_codes): void
    {
        $this->home_page->open();
        if (in_array($currencies_codes, $this->home_page->get_available_currencies(), true)) {
            throw new \InvalidArgumentException(sprintf('Expected "%s" not to be in "%s"', $currencies_codes, implode('", "', $this->home_page->get_available_currencies())));
        }
    }
}
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
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Locale\Context\Locale_Not_Found_Exception;
use Sylius\Component\Locale\Model\Locale_Interface;
use Webmozart\Assert\Assert;
final readonly class Locale_Context implements Context
{
    public function __construct(private Home_Page_Interface $home_page, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Given('I switched the shop\'s locale to :locale')]
    #[Given('I have switched to the :locale locale')]
    #[When('I switch to the :locale locale')]
    #[When('I change my locale to :locale')]
    public function i_switch_the_locale_to_the_locale(Locale_Interface $locale): void
    {
        $this->home_page->open();
        $this->home_page->switch_locale($locale->get_code());
        $this->shared_storage->set('current_locale_code', $locale->get_code());
    }
    #[When('I use the locale :localeCode')]
    public function i_use_the_locale(string $locale_code): void
    {
        $this->home_page->try_to_open(['_locale' => $locale_code]);
    }
    #[Then('I should shop using the :localeNameInItsLocale locale')]
    #[Then('I should still shop using the :localeNameInItsLocale locale')]
    public function i_should_shop_using_the_locale(string $locale_name_in_its_locale): void
    {
        Assert::same($this->home_page->get_active_locale(), $locale_name_in_its_locale);
    }
    #[Then('I should be able to shop using the :localeNameInCurrentLocale locale')]
    #[Then('the store should be available in the :localeNameInCurrentLocale locale')]
    public function i_should_be_able_to_shop_using_the_locale(string $locale_name_in_current_locale): void
    {
        Assert::one_of($locale_name_in_current_locale, $this->home_page->get_available_locales());
    }
    #[Then('I should not be able to shop using the :locale locale')]
    #[Then('the store should not be available in the :locale locale')]
    public function i_should_not_be_able_to_shop_using_the_locale(string $locale): void
    {
        if (in_array($locale, $this->home_page->get_available_locales(), true)) {
            throw new \InvalidArgumentException(sprintf('Expected "%s" not to be in "%s"', $locale, implode('", "', $this->home_page->get_available_locales())));
        }
    }
    #[Then('I should not be able to shop without default locale')]
    public function i_should_not_be_able_to_shop(): void
    {
        try {
            $this->home_page->try_to_open();
            throw new \Exception('The page should not be able to open.');
        } catch (Locale_Not_Found_Exception) {
        }
    }
}
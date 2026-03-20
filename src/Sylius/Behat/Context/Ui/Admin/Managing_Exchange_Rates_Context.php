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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Admin\Exchange_Rate\Form_Element_Interface;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Behat\Page\Admin\Exchange_Rate\Index_Page_Interface;
use Sylius\Component\Currency\Model\Currency_Interface;
use Sylius\Component\Currency\Model\Exchange_Rate_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Exchange_Rates_Context implements Context
{
    public function __construct(private Create_Page_Interface $create_page, private Index_Page_Interface $index_page, private Update_Page_Interface $update_page, private Form_Element_Interface $form_element)
    {
    }
    #[When('I want to add a new exchange rate')]
    public function i_want_to_add_new_exchange_rate(): void
    {
        $this->create_page->open();
    }
    #[When('/^I want to edit (this exchange rate)$/')]
    #[When('/^I am editing (this exchange rate)$/')]
    public function i_want_to_edit_this_exchange_rate(Exchange_Rate_Interface $exchange_rate): void
    {
        $this->update_page->open(['id' => $exchange_rate->get_id()]);
    }
    #[Given('I am browsing exchange rates of the store')]
    #[When('I browse exchange rates')]
    #[When('I browse exchange rates of the store')]
    public function i_want_to_browse_exchange_rates_of_the_store(): void
    {
        $this->index_page->open();
    }
    #[When('/^I specify its ratio as (-?[0-9\.]+)$/')]
    #[When('I don\'t specify its ratio')]
    public function i_specify_its_ratio_as(?string $ratio = null): void
    {
        $this->form_element->specify_ratio($ratio ?? '');
    }
    #[When('I choose :currencyCode as the source currency')]
    public function i_choose_as_source_currency(string $currency_code): void
    {
        $this->form_element->specify_source_currency($currency_code);
    }
    #[When('I choose :currencyCode as the target currency')]
    public function i_choose_as_target_currency(string $currency_code): void
    {
        $this->form_element->specify_target_currency($currency_code);
    }
    #[When('I( try to) add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I change ratio to :ratio')]
    public function i_change_ratio_to(string $ratio): void
    {
        $this->form_element->specify_ratio($ratio);
    }
    #[When('I delete the exchange rate between :sourceCurrencyName and :targetCurrencyName')]
    public function i_delete_the_exchange_rate_between_and(string $source_currency_name, string $target_currency_name): void
    {
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['sourceCurrency' => $source_currency_name, 'targetCurrency' => $target_currency_name]);
    }
    #[When('I choose :currencyName as a currency filter')]
    public function i_choose_currency_as_a_currency_filter(string $currency_name): void
    {
        $this->index_page->choose_currency_filter($currency_name);
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->index_page->filter();
    }
    #[When('I check (also) the exchange rate between :sourceCurrencyName and :targetCurrencyName')]
    public function i_check_the_exchange_rate_between_and(string $source_currency_name, string $target_currency_name): void
    {
        $this->index_page->check_resource_on_page(['sourceCurrency' => $source_currency_name, 'targetCurrency' => $target_currency_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[Then('I should see :count exchange rates on the list')]
    public function i_should_see_exchange_rates_on_the_list(int $count): void
    {
        $this->assert_count_of_exchange_rates_on_the_list($count);
    }
    #[Then('I should see a single exchange rate in the list')]
    #[Then('I should( still) see one exchange rate on the list')]
    public function i_should_see_one_exchange_rate_on_the_list(): void
    {
        $this->index_page->open();
        $this->assert_count_of_exchange_rates_on_the_list(1);
    }
    #[Then('the exchange rate with ratio :ratio between :sourceCurrency and :targetCurrency should appear in the store')]
    public function the_exchange_rate_between_and_should_appear_in_the_store(string $ratio, Currency_Interface $source_currency, Currency_Interface $target_currency): void
    {
        $this->index_page->open();
        $this->assert_exchange_rate_with_ratio_is_on_the_list($ratio, $source_currency->get_name(), $target_currency->get_name());
    }
    #[Then('I should see the exchange rate between :sourceCurrencyName and :targetCurrencyName in the list')]
    #[Then('I should (also) see an exchange rate between :sourceCurrencyName and :targetCurrencyName on the list')]
    public function i_should_see_an_exchange_rate_between_and_on_the_list(string $source_currency_name, string $target_currency_name): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['sourceCurrency' => $source_currency_name, 'targetCurrency' => $target_currency_name]));
    }
    #[Then('it should have a ratio of :ratio')]
    public function this_exchange_rate_should_have_ratio_of(string $ratio): void
    {
        Assert::eq($this->form_element->get_ratio(), $ratio);
    }
    #[Then('/^(this exchange rate) should no longer be on the list$/')]
    public function this_exchange_rate_should_no_longer_be_on_the_list(Exchange_Rate_Interface $exchange_rate): void
    {
        $this->assert_exchange_rate_is_not_on_the_list($exchange_rate->get_source_currency()->get_name(), $exchange_rate->get_target_currency()->get_name());
    }
    #[Then('the exchange rate between :sourceCurrencyName and :targetCurrencyName should not be added')]
    public function the_exchange_rate_between_and_should_not_be_added(string $source_currency_name, string $target_currency_name): void
    {
        $this->index_page->open();
        $this->assert_exchange_rate_is_not_on_the_list($source_currency_name, $target_currency_name);
    }
    #[Then('/^(this exchange rate) should have a ratio of ([0-9\.]+)$/')]
    public function this_exchange_rate_should_have_a_ratio_of(Exchange_Rate_Interface $exchange_rate, string $ratio): void
    {
        $source_currency_name = $exchange_rate->get_source_currency()->get_name();
        $target_currency_name = $exchange_rate->get_target_currency()->get_name();
        $this->assert_exchange_rate_with_ratio_is_on_the_list($ratio, $source_currency_name, $target_currency_name);
    }
    #[Then('I should not be able to edit its source currency')]
    public function i_should_not_be_able_to_edit_its_source_currency(): void
    {
        Assert::true($this->form_element->is_field_disabled('source_currency'));
    }
    #[Then('I should not be able to edit its target currency')]
    public function i_should_not_be_able_to_edit_its_target_currency(): void
    {
        Assert::true($this->form_element->is_field_disabled('target_currency'));
    }
    #[Then('/^I should be notified that ([^"]+) is required$/')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        Assert::same($this->form_element->get_validation_message($element), sprintf('Please enter exchange rate %s.', $element));
    }
    #[Then('I should be notified that the ratio must be greater than zero')]
    public function i_should_be_notified_that_ratio_must_be_greater_than_zero(): void
    {
        Assert::same($this->form_element->get_validation_message('ratio'), 'The ratio must be greater than 0.');
    }
    #[Then('I should be notified that the ratio must be less than :value')]
    public function i_should_be_notified_that_ratio_must_be_less_than(string $value): void
    {
        Assert::same($this->form_element->get_validation_message('ratio'), sprintf('The ratio must be less than %s.', $value));
    }
    #[Then('I should be notified that source and target currencies must differ')]
    public function i_should_be_notified_that_source_and_target_currencies_must_differ(): void
    {
        $this->assert_form_has_validation_message('The source and target currencies must differ.');
    }
    #[Then('I should be notified that the currency pair must be unique')]
    public function i_should_be_notified_that_the_currency_pair_must_be_unique(): void
    {
        $this->assert_form_has_validation_message('The currency pair must be unique.');
    }
    /**
     * @throws \InvalidArgumentException
     */
    private function assert_exchange_rate_with_ratio_is_on_the_list(string $ratio, string $source_currency_name, string $target_currency_name): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['ratio' => $ratio, 'sourceCurrency' => $source_currency_name, 'targetCurrency' => $target_currency_name]), sprintf('An exchange rate between %s and %s with a ratio of %s has not been found on the list.', $source_currency_name, $target_currency_name, $ratio));
    }
    /**
     * @throws \InvalidArgumentException
     */
    private function assert_exchange_rate_is_not_on_the_list(string $source_currency_name, string $target_currency_name): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['sourceCurrency' => $source_currency_name, 'targetCurrency' => $target_currency_name]), sprintf('An exchange rate with source currency %s and target currency %s has been found on the list.', $source_currency_name, $target_currency_name));
    }
    /**
     * @throws \InvalidArgumentException
     */
    private function assert_count_of_exchange_rates_on_the_list(int $count): void
    {
        Assert::same($this->index_page->count_items(), $count, 'Expected %2$d exchange rates to be on the list, but found %d instead.');
    }
    /**
     * @throws \InvalidArgumentException
     */
    private function assert_form_has_validation_message(string $expected_message): void
    {
        Assert::true($this->form_element->has_form_validation_error($expected_message), sprintf('The validation message "%s" was not found on the page.', $expected_message));
    }
}
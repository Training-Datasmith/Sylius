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
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Currency\Model\Currency_Interface;
use Sylius\Component\Currency\Model\Exchange_Rate_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Exchange_Rates_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private string $api_url_prefix)
    {
    }
    #[Given('/^I am editing (this exchange rate)$/')]
    #[When('/^I want to edit (this exchange rate)$/')]
    public function i_want_to_edit_this_exchange_rate(Exchange_Rate_Interface $exchange_rate): void
    {
        $this->client->build_update_request(Resources::EXCHANGE_RATES, (string) $exchange_rate->get_id());
        $this->shared_storage->set('exchange_rate_id', (string) $exchange_rate->get_id());
    }
    #[Given('I am browsing exchange rates of the store')]
    #[When('I browse exchange rates')]
    #[When('I browse exchange rates of the store')]
    public function i_browse_exchange_rates_of_the_store(): void
    {
        $this->client->index(Resources::EXCHANGE_RATES);
    }
    #[When('I want to add a new exchange rate')]
    public function i_want_to_add_new_exchange_rate(): void
    {
        $this->client->build_create_request(Resources::EXCHANGE_RATES);
    }
    #[When('I specify its ratio as :ratio')]
    #[When('I don\'t specify its ratio')]
    public function i_specify_its_ratio_as(?string $ratio = null): void
    {
        if ($ratio !== null) {
            $this->client->add_request_data('ratio', $ratio);
        }
    }
    #[When('I choose :currencyCode as the source currency')]
    public function i_choose_as_the_source_currency(string $currency_code): void
    {
        $this->client->add_request_data('sourceCurrency', sprintf('%s/admin/currencies/%s', $this->api_url_prefix, $currency_code));
    }
    #[When('I choose :currencyCode as the target currency')]
    public function i_choose_as_the_target_currency(string $currency_code): void
    {
        $this->client->add_request_data('targetCurrency', sprintf('%s/admin/currencies/%s', $this->api_url_prefix, $currency_code));
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I change ratio to :ratio')]
    public function i_change_ratio_to(string $ratio): void
    {
        $this->client->update_request_data(['ratio' => $ratio]);
    }
    #[When('/^I delete the (exchange rate between "[^"]+" and "[^"]+")$/')]
    public function i_delete_the_exchange_rate_between_and(Exchange_Rate_Interface $exchange_rate): void
    {
        $this->client->delete(Resources::EXCHANGE_RATES, (string) $exchange_rate->get_id());
    }
    #[When('I choose :currency as a currency filter')]
    public function i_choose_currency_as_a_currency_filter(Currency_Interface $currency): void
    {
        $this->client->add_filter('currencyCode', $currency->get_code());
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->client->filter();
    }
    #[Then('I should see :count exchange rates on the list')]
    public function i_should_see_exchange_rates_on_the_list(int $count): void
    {
        Assert::count($this->response_checker->get_collection($this->client->get_last_response()), $count);
    }
    #[Then('I should see a single exchange rate in the list')]
    #[Then('I should( still) see one exchange rate on the list')]
    public function i_should_see_a_single_exchange_rate_in_the_list(): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->index(Resources::EXCHANGE_RATES)), 1);
    }
    #[Then('the exchange rate with ratio :ratio between :sourceCurrency and :targetCurrency should appear in the store')]
    public function the_exchange_rate_with_ratio_between_and_should_appear_in_the_store(float $ratio, Currency_Interface $source_currency, Currency_Interface $target_currency): void
    {
        Assert::true($this->response_has_exchange_rate($ratio, $source_currency, $target_currency), sprintf('Exchange rate with ratio %s between %s and %s does not exist', $ratio, $source_currency->get_name(), $target_currency->get_name()));
    }
    #[Then('I should see the exchange rate between :sourceCurrency and :targetCurrency in the list')]
    #[Then('I should (also) see an exchange rate between :sourceCurrency and :targetCurrency on the list')]
    public function i_should_see_the_exchange_rate_between_and_in_the_list(Currency_Interface $source_currency, Currency_Interface $target_currency): void
    {
        Assert::not_null($this->get_exchange_rate_from_response($source_currency, $target_currency), sprintf('Exchange rate for %s and %s currencies does not exist', $source_currency, $target_currency));
    }
    #[Then('it should have a ratio of :ratio')]
    public function it_should_have_a_ratio_of(float $ratio): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::EXCHANGE_RATES), 'ratio', $ratio), sprintf('ExchangeRate with ratio %s does not exist', $ratio));
    }
    #[Then('/^(this exchange rate) should no longer be on the list$/')]
    public function this_exchange_rate_should_no_longer_be_on_the_list(Exchange_Rate_Interface $exchange_rate): void
    {
        Assert::false($this->response_has_exchange_rate($exchange_rate->get_ratio(), $exchange_rate->get_source_currency(), $exchange_rate->get_target_currency()), sprintf('Exchange rate with ratio %s between %s and %s still exists, but it should not.', $exchange_rate->get_ratio(), $exchange_rate->get_source_currency()->get_name(), $exchange_rate->get_target_currency()->get_name()));
    }
    #[Then('the exchange rate between :sourceCurrency and :targetCurrency should not be added')]
    public function the_exchange_rate_between_and_should_not_be_added(Currency_Interface $source_currency, Currency_Interface $target_currency): void
    {
        $this->client->index(Resources::EXCHANGE_RATES);
        Assert::null($this->get_exchange_rate_from_response($source_currency, $target_currency));
    }
    #[Then('/^(this exchange rate) should have a ratio of ([0-9\.]+)$/')]
    public function this_exchange_rate_should_have_a_ratio_of(Exchange_Rate_Interface $exchange_rate, float $ratio): void
    {
        $exchange_rate = $this->get_exchange_rate_from_response($exchange_rate->get_source_currency(), $exchange_rate->get_target_currency());
        Assert::same($exchange_rate['ratio'], $ratio);
    }
    #[Then('I should not be able to edit its source currency')]
    public function i_should_not_be_able_to_edit_its_source_currency(): void
    {
        $this->assert_if_not_be_able_to_edit_it_currency('sourceCurrency');
    }
    #[Then('I should not be able to edit its target currency')]
    public function i_should_not_be_able_to_edit_its_target_currency(): void
    {
        $this->assert_if_not_be_able_to_edit_it_currency('targetCurrency');
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s: Please enter exchange rate %s.', $element, $element));
    }
    #[Then('I should be notified that the ratio must be greater than zero')]
    public function i_should_be_notified_that_ratio_must_be_greater_than_zero(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The ratio must be greater than 0.');
    }
    #[Then('I should be notified that the ratio must be less than :value')]
    public function i_should_be_notified_that_ratio_must_be_less_than(string $value): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('The ratio must be less than %s.', $value));
    }
    #[Then('I should be notified that source and target currencies must differ')]
    public function i_should_be_notified_that_source_and_target_currencies_must_differ(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The source and target currencies must differ.');
    }
    #[Then('I should be notified that the currency pair must be unique')]
    public function i_should_be_notified_that_the_currency_pair_must_be_unique(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The currency pair must be unique.');
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Exchange rate could not be created');
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Exchange rate could not be deleted');
    }
    private function assert_if_not_be_able_to_edit_it_currency(string $currency_type): void
    {
        $this->client->build_update_request(Resources::EXCHANGE_RATES, $this->shared_storage->get('exchange_rate_id'));
        $this->client->add_request_data($currency_type, sprintf('%s/admin/currencies/EUR', $this->api_url_prefix));
        $this->client->update();
        Assert::false($this->response_checker->has_item_on_position_with_value($this->client->index(Resources::EXCHANGE_RATES), 0, $currency_type, sprintf('%s/admin/currencies/EUR', $this->api_url_prefix)), sprintf('It was possible to change %s', $currency_type));
    }
    private function get_exchange_rate_from_response(Currency_Interface $source_currency, Currency_Interface $target_currency): ?array
    {
        /** @var array $item */
        foreach ($this->response_checker->get_collection($this->client->index(Resources::EXCHANGE_RATES)) as $item) {
            if ($item['sourceCurrency'] === sprintf('%s/admin/currencies/%s', $this->api_url_prefix, $source_currency->get_code()) && $item['targetCurrency'] === sprintf('%s/admin/currencies/%s', $this->api_url_prefix, $target_currency->get_code())) {
                return $item;
            }
        }
        return null;
    }
    private function response_has_exchange_rate(float $ratio, Currency_Interface $source_currency, Currency_Interface $target_currency): bool
    {
        $exchange_rate_response = $this->get_exchange_rate_from_response($source_currency, $target_currency);
        if (null === $exchange_rate_response) {
            return false;
        }
        return $exchange_rate_response['ratio'] === $ratio;
    }
}
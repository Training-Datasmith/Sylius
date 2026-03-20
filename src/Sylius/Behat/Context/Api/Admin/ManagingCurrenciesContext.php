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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Webmozart\Assert\Assert;
final readonly class Managing_Currencies_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[When('I want to browse currencies of the store')]
    public function i_want_to_see_all_currencies_in_store(): void
    {
        $this->client->index(Resources::CURRENCIES);
    }
    #[When('I want to add a new currency')]
    public function i_want_to_add_new_currency(): void
    {
        $this->client->build_create_request(Resources::CURRENCIES);
    }
    #[When('I choose :currencyCode')]
    #[When('I set code to :code')]
    #[When('I do not choose a code')]
    public function i_choose(string $currency_code = ''): void
    {
        $this->client->add_request_data('code', $currency_code);
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[Then('I should see :count currencies on the list')]
    public function i_should_see_currencies_in_the_list(int $count): void
    {
        $items_count = $this->response_checker->count_collection_items($this->client->get_last_response());
        Assert::eq($count, $items_count, sprintf('Expected %d currencies, but got %d', $count, $items_count));
    }
    #[Then('I should see the currency :currencyName on the list')]
    #[Then('the currency :currencyName should appear in the store')]
    public function currency_should_appear_in_the_store(string $currency_name): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::CURRENCIES), 'name', $currency_name), sprintf('There is no currency with name "%s"', $currency_name));
    }
    #[Then('there should still be only one currency with code :code')]
    public function there_should_still_be_only_one_currency_with_code(string $code): void
    {
        $response = $this->client->index(Resources::CURRENCIES);
        Assert::same($this->response_checker->count_collection_items($response), 1);
        Assert::true($this->response_checker->has_item_with_value($response, 'code', $code), sprintf('There is no currency with code "%s"', $code));
    }
    #[Then('I should be notified that currency code must be unique')]
    public function i_should_be_notified_that_currency_code_must_be_unique(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Currency has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: Currency code must be unique.');
    }
    #[Then('I should be notified that a code is required')]
    public function i_should_be_notified_that_a_code_is_required(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Currency has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: Please choose currency code.');
    }
    #[Then('I should be notified that the code is invalid')]
    public function i_should_be_notified_that_the_code_is_invalid(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Currency has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: This value is not a valid currency code.');
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Currency could not be created');
    }
}
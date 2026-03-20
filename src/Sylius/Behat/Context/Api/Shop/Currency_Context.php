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
final readonly class Currency_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[When('/^I (?:start browsing|try to browse|browse) currencies$/')]
    public function i_browse_currencies(): void
    {
        $this->client->index(Resources::CURRENCIES);
    }
    #[Then('I should see :firstCurrency in the list')]
    #[Then('I should see :firstCurrency and :secondCurrency in the list')]
    #[Then('I should see :firstCurrency, :secondCurrency and :thirdCurrency in the list')]
    public function i_should_see_currencies_in_the_list(string ...$currencies_codes): void
    {
        $response = $this->client->get_last_response();
        foreach ($currencies_codes as $currency_code) {
            $this->response_checker->get_collection_items_with_value($response, 'code', $currency_code);
        }
    }
}
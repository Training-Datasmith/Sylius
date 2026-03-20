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
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Webmozart\Assert\Assert;
final readonly class Locale_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I get available locales')]
    public function i_get_available_locales(): void
    {
        $this->client->index(Resources::LOCALES);
    }
    #[When('I get :locale locale')]
    public function i_get_locale(Locale_Interface $locale): void
    {
        $this->client->show(Resources::LOCALES, $locale->get_code());
    }
    #[When('I switch to the :localeCode locale')]
    #[When('I use the locale :localeCode')]
    public function i_switch_to_the_locale(string $locale_code): void
    {
        $this->shared_storage->set('current_locale_code', $locale_code);
    }
    #[Then('I should have :count locales')]
    public function i_should_have_locales(int $count): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('the :name locale with code :code should be available')]
    public function the_locale_with_code_should_be_available(string $name, string $code): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->get_last_response(), ['name' => $name, 'code' => $code]));
    }
    #[Then('the :name locale with code :code should not be available')]
    public function the_locale_with_code_should_not_be_available(string $name, string $code): void
    {
        Assert::false($this->response_checker->has_item_with_values($this->client->get_last_response(), ['name' => $name, 'code' => $code]));
    }
    #[Then('I should have :name with code :code')]
    public function i_should_have_with_code(string $name, string $code): void
    {
        $response = $this->client->get_last_response();
        Assert::true($this->response_checker->has_value($response, 'name', $name));
        Assert::true($this->response_checker->has_value($response, 'code', $code));
    }
    #[Then('I should( still) shop using the :localeCode locale')]
    public function i_should_shop_using_the_locale(string $locale_code): void
    {
        $this->client->build_create_request(Resources::ORDERS);
        Assert::same($this->response_checker->get_value($this->client->create(), 'localeCode'), $locale_code);
    }
    #[Then('I should be able to shop using the :localeCode locale')]
    public function i_should_be_able_to_shop_using_the_locale(string $locale_code): void
    {
        $this->i_get_available_locales();
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'code', $locale_code));
    }
    #[Then('I should not be able to shop using the :localeCode locale')]
    public function i_should_not_be_able_to_shop_using_the_locale(string $locale_code): void
    {
        $this->i_get_available_locales();
        Assert::false($this->response_checker->has_item_with_value($this->client->get_last_response(), 'code', $locale_code));
    }
}
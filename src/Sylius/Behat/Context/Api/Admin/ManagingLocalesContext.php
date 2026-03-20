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
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final readonly class Managing_Locales_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[Given('I am browsing locales')]
    public function i_am_browsing_locales(): void
    {
        $this->client->index(Resources::LOCALES);
    }
    #[Given('/^I want to (?:create|add) a new locale$/')]
    public function i_want_to_add_new_locale(): void
    {
        $this->client->build_create_request(Resources::LOCALES);
    }
    #[When('I choose :localeCode')]
    #[When('I set code to :code')]
    #[When('I do not choose a code')]
    public function i_choose(string $locale_code = ''): void
    {
        $this->client->add_request_data('code', $locale_code);
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I filter by code containing :phrase')]
    public function i_filter_by_code_containing(string $phrase): void
    {
        $this->client->add_filter('code', $phrase);
        $this->client->filter();
    }
    #[When('I remove :localeCode locale')]
    public function i_remove_locale(string $locale_code): void
    {
        $this->client->delete(Resources::LOCALES, $locale_code);
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Locale could not be created');
    }
    #[Then('the store should be available in the :localeCode language')]
    public function the_store_should_be_available_in_the_language(string $locale_code): void
    {
        $response = $this->client->index(Resources::LOCALES);
        Assert::true($this->response_checker->has_item_with_value($response, 'code', $locale_code), sprintf('There is no locale with code "%s"', $locale_code));
    }
    #[Then('I should not be able to choose :localeCode')]
    public function i_should_not_be_able_to_choose(string $locale_code): void
    {
        $this->client->add_request_data('code', $locale_code);
        $response = $this->client->create();
        Assert::false($this->response_checker->is_creation_successful($response), 'Locale has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: Locale code must be unique.');
    }
    #[Then('I should be notified that a code is required')]
    public function i_should_be_notified_that_a_code_is_required(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Locale has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: Please choose locale code.');
    }
    #[Then('I should be notified that the code is invalid')]
    public function i_should_be_notified_that_the_code_is_invalid(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Locale has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: This value is not a valid locale code.');
    }
    #[Then('I should be informed that locale :localeCode has been deleted')]
    public function i_should_be_informed_that_locale_has_been_deleted(string $locale_code): void
    {
        Assert::same($this->client->get_last_response()->get_status_code(), Response::HTTP_NO_CONTENT);
    }
    #[Then('only the :localeCode locale should be present in the system')]
    public function only_the_locale_should_be_present_in_the_system(string $locale_code): void
    {
        $response = $this->client->index(Resources::LOCALES);
        Assert::true($this->response_checker->count_collection_items($response) === 1);
        Assert::true($this->response_checker->has_item_with_value($response, 'code', $locale_code), sprintf('There is no locale with code "%s"', $locale_code));
    }
    #[Then('I should be informed that locale :localeCode is in use and cannot be deleted')]
    public function i_should_be_informed_that_locale_is_in_use_and_cannot_be_deleted(string $locale_code): void
    {
        Assert::same($this->client->get_last_response()->get_status_code(), Response::HTTP_UNPROCESSABLE_ENTITY);
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), sprintf('Locale "%s" is used.', $locale_code));
    }
    #[Then('the :localeCode locale should be still present in the system')]
    public function the_locale_should_be_still_present_in_the_system(string $locale_code): void
    {
        $response = $this->client->index(Resources::LOCALES);
        Assert::true($this->response_checker->has_item_with_value($response, 'code', $locale_code), sprintf('There is no locale with code "%s"', $locale_code));
    }
    #[Then('I should see a single locale in the list')]
    public function i_should_see_locale_in_the_list(int $amount = 1): void
    {
        Assert::same(count($this->response_checker->get_collection($this->client->get_last_response())), $amount);
    }
    #[Then('I should see the locale :localeName')]
    public function i_should_see_the_locale(string $locale_name): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'name', $locale_name), sprintf('Locale with name %s does not exist', $locale_name));
    }
}
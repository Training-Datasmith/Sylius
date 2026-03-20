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

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Model\Province_Interface;
use Symfony\Component\Intl\Countries;
use Webmozart\Assert\Assert;
final class Managing_Countries_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private Iri_Converter_Interface $iri_converter)
    {
    }
    #[When('I want to add a new country')]
    public function i_want_to_add_a_new_country(): void
    {
        $this->client->build_create_request(Resources::COUNTRIES);
    }
    #[When('I choose :countryName')]
    public function i_choose(string $country_name): void
    {
        $this->i_specify_the_country_code_as($this->get_country_code_by_name($country_name));
    }
    #[When('I specify the country code as :code')]
    public function i_specify_the_country_code_as(string $code): void
    {
        $this->client->add_request_data('code', $code);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('/^I want to edit (this country)$/')]
    #[When('/^I am editing (this country)$/')]
    #[When('/^I want to create a new province in (country "([^"]+)")$/')]
    public function i_want_to_edit_this_country(Country_Interface $country): void
    {
        $this->client->build_update_request(Resources::COUNTRIES, $country->get_code());
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->client->add_request_data('enabled', true);
    }
    #[When('I disable it')]
    public function i_disable_it(): void
    {
        $this->client->add_request_data('enabled', false);
    }
    #[When('I name the province :provinceName')]
    public function i_name_the_province(string $province_name): void
    {
        $this->client->add_sub_resource_data('provinces', ['name' => $province_name]);
    }
    #[When('I specify the province code as :provinceCode')]
    public function i_specify_the_province_code_as(string $province_code): void
    {
        $this->client->add_sub_resource_data('provinces', ['code' => $province_code]);
    }
    #[When('I provide a too long province code')]
    public function i_provide_a_too_long_province_code(): void
    {
        $this->i_specify_the_province_code_as(sprintf('XX-%s', str_repeat('A', $this->get_max_code_length())));
    }
    #[When('I add the :provinceName province with :provinceCode code')]
    public function i_add_the_province_with_code(string $province_name, string $province_code): void
    {
        $this->client->add_sub_resource_data('provinces', ['code' => $province_code, 'name' => $province_name]);
    }
    #[When('I add the :name province with :code code and :abbreviation abbreviation')]
    public function i_add_the_province_with_code_and_abbreviation(string $name, string $code, string $abbreviation): void
    {
        $this->client->add_sub_resource_data('provinces', ['code' => $code, 'name' => $name, 'abbreviation' => $abbreviation]);
    }
    #[When('/^I(?:| also) delete the ("[^"]+" province) of (this country)$/')]
    public function i_delete_the_province_of_this_country(Province_Interface $province, Country_Interface $country): void
    {
        $iri = $this->iri_converter->get_iri_from_resource($province);
        $provinces = $this->response_checker->get_value($this->client->show(Resources::COUNTRIES, $country->get_code()), 'provinces');
        foreach ($provinces as $province_iri) {
            if ($iri === $province_iri) {
                $this->client->remove_sub_resource_iri('provinces', $province_iri);
            }
        }
    }
    #[When('I do not specify the country code')]
    #[When('I do not specify the province code')]
    #[When('I do not name the province')]
    public function i_do_not_specify_the_field(): void
    {
        // Intentionally left blank
    }
    #[When('I remove :province province name')]
    public function i_remove_province_name(Province_Interface $province): void
    {
        $this->client->build_update_request(sprintf('countries/%s/provinces/%s', $province->get_country()->get_code(), $province->get_code()));
        $this->client->add_request_data('name', '');
        $this->client->update();
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Country could not be created');
    }
    #[Then('the country :country should appear in the store')]
    public function the_country_should_appear_in_the_store(Country_Interface $country): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::COUNTRIES), 'code', $country->get_code()), sprintf('There is no country with name "%s"', $country->get_name()));
    }
    #[Then('the country :country should have the :province province')]
    #[Then('/^(this country) should(?:| still) have the ("[^"]*" province)$/')]
    public function the_country_should_have_the_province(Country_Interface $country, Province_Interface $province): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->sub_resource_index(Resources::COUNTRIES, 'provinces', $country->get_code()), 'code', $province->get_code()));
    }
    #[Then('/^(this country) should(?:| still) have the ("[^"]*" and "[^"]*" provinces)$/')]
    public function the_country_should_have_the_province_and_province(Country_Interface $country, array $provinces): void
    {
        foreach ($provinces as $province) {
            $this->the_country_should_have_the_province($country, $province);
        }
    }
    #[Then('the province should still be named :province in this country')]
    public function the_province_should_still_be_named_in_this_country(Province_Interface $province): void
    {
        /** @var CountryInterface $country */
        $country = $this->shared_storage->get('country');
        Assert::true($this->response_checker->has_item_with_value($this->client->sub_resource_index(Resources::COUNTRIES, 'provinces', $country->get_code()), 'code', $province->get_code()));
    }
    #[Then('I should not be able to choose :countryName')]
    public function i_should_not_be_able_to_choose(string $country_name): void
    {
        $this->client->add_request_data('code', $this->get_country_code_by_name($country_name));
        $response = $this->client->create();
        Assert::false($this->response_checker->is_creation_successful($response), 'Country has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: Country ISO code must be unique.');
    }
    #[Then('/^(this country) should be (enabled|disabled)$/')]
    public function this_country_should_be(Country_Interface $country, string $state): void
    {
        $is_enabled = 'enabled' === $state;
        Assert::true($this->response_checker->has_value($this->client->show(Resources::COUNTRIES, $country->get_code()), 'enabled', $is_enabled), sprintf('Country is not %s', $is_enabled ? 'enabled' : 'disabled'));
    }
    #[Then('I should not be able to edit its code')]
    public function the_code_field_should_be_disabled(): void
    {
        $this->client->update_request_data(['code' => 'NEW_CODE']);
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'));
    }
    #[Then('/^province with code ("[^"]*") should not be added in (this country)$/')]
    public function province_with_code_should_not_be_added_in_this_country(string $province_code, Country_Interface $country): void
    {
        /** @var ProvinceInterface $province */
        foreach ($this->get_provinces_of_country($country) as $province) {
            Assert::false($province->get_code() === $province_code, sprintf('The country "%s" should not have the "%s" province', $country->get_name(), $province->get_name()));
        }
    }
    #[Then('this country should not have the :provinceName province')]
    #[Then('province with name :provinceName should not be added in this country')]
    public function this_country_should_not_have_the_province(string $province_name): void
    {
        /** @var CountryInterface $country */
        $country = $this->shared_storage->get('country');
        /** @var ProvinceInterface $province */
        foreach ($this->get_provinces_of_country($country) as $province) {
            Assert::false($province->get_name() === $province_name, sprintf('The country "%s" should not have the "%s" province', $country->get_name(), $province->get_name()));
        }
    }
    #[Then('/^I should be notified that province (code|name) must be unique$/')]
    public function i_should_be_notified_that_province_code_must_be_unique(string $field): void
    {
        Assert::regex($this->response_checker->get_error($this->client->get_last_response()), sprintf('/provinces\[[\d+]\]\.%1$s: Province %1$s must be unique\./', $field));
    }
    #[Then('I should be notified that all province codes and names within this country need to be unique')]
    public function i_should_be_notified_that_all_province_codes_and_names_within_this_country_need_to_be_unique(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'provinces: All provinces within this country need to have unique codes and names.');
    }
    #[Then('I should be notified that :field is required')]
    public function i_should_be_notified_that_field_is_required(string $field): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), \sprintf('Please enter province %s.', $field));
    }
    #[Then('/^I should be notified that the country code is (required|invalid)$/')]
    public function i_should_be_notified_that_the_country_code_is_required(string $constraint): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), $constraint === 'required' ? 'Please enter country ISO code.' : 'Country ISO code is invalid.');
    }
    #[Then('I should be notified that name of the province is required')]
    public function i_should_be_notified_that_name_of_the_province_is_required(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Please enter province name.');
    }
    #[Then('I should be informed that the provided province code is too long')]
    public function i_should_be_informed_that_the_province_code_is_too_long(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The code must not be longer than');
    }
    #[Then('I should be notified that provinces that are in use cannot be deleted')]
    public function i_should_be_notified_that_provinces_that_are_in_use_cannot_be_deleted(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot delete, the province is in use.');
    }
    private function get_country_code_by_name(string $country_name): string
    {
        $country_list = array_flip(Countries::get_names());
        Assert::key_exists($country_list, $country_name, sprintf('The country with name "%s" not found', $country_name));
        return $country_list[$country_name];
    }
    /** @return iterable<ProvinceInterface> */
    private function get_provinces_of_country(Country_Interface $country): iterable
    {
        $response = $this->client->show(Resources::COUNTRIES, $country->get_code());
        $country_from_response = $this->response_checker->get_response_content($response);
        foreach ($country_from_response['provinces'] as $province_from_response) {
            yield $this->iri_converter->get_resource_from_iri($province_from_response);
        }
    }
}
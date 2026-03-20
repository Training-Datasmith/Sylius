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
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Currency\Model\Currency_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Webmozart\Assert\Assert;
final class Managing_Channels_Context implements Context
{
    use Validation_Trait;
    private array $shop_billing_data = [];
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter)
    {
    }
    #[When('I want to create a new channel')]
    public function i_want_to_create_a_new_channel(): void
    {
        $this->client->build_create_request(Resources::CHANNELS);
    }
    #[When('I delete channel :channel')]
    public function i_delete_channel(Channel_Interface $channel): void
    {
        $this->client->delete(Resources::CHANNELS, $channel->get_code());
    }
    #[When('I want to modify a channel :channel')]
    public function i_want_to_modify_channel(Channel_Interface $channel): void
    {
        $this->client->build_update_request(Resources::CHANNELS, $channel->get_code());
    }
    #[When('I rename it to :name')]
    #[When('I do not name it')]
    #[When('I remove its name')]
    public function i_rename_it(string $name = ''): void
    {
        $this->client->add_request_data('name', $name);
    }
    #[When('/^I (enable|disable) it$/')]
    public function i_disable_it(string $toggle_action): void
    {
        $this->client->add_request_data('enabled', $toggle_action === 'enable');
    }
    #[When('I change its menu taxon to :taxon')]
    public function i_change_its_menu_taxon_to(Taxon_Interface $taxon): void
    {
        $this->client->add_request_data('menuTaxon', $this->iri_converter->get_iri_from_resource_in_section($taxon, 'admin'));
    }
    #[When('I specify its :field as :value')]
    #[When('I :field it :value')]
    #[When('I set its :field as :value')]
    #[When('I define its :field as :value')]
    #[When('I do not specify its :field')]
    public function i_specify_its_as(string $field, string $value = ''): void
    {
        $this->client->add_request_data($field, $value);
    }
    #[When('I choose :currency as the base currency')]
    #[When('I do not choose base currency')]
    public function i_choose_as_the_base_currency(?Currency_Interface $currency = null): void
    {
        $this->client->add_request_data('baseCurrency', null === $currency ? $currency : $this->iri_converter->get_iri_from_resource_in_section($currency, 'admin'));
    }
    #[When('I allow for paying in :currency')]
    public function i_allow_to_paying_for_this_channel(Currency_Interface $currency): void
    {
        $this->client->add_request_data('currencies', [$this->iri_converter->get_iri_from_resource($currency)]);
    }
    #[When('I select the :zone as default tax zone')]
    public function i_select_default_tax_zone(Zone_Interface $zone): void
    {
        $this->client->add_request_data('defaultTaxZone', $this->iri_converter->get_iri_from_resource($zone));
    }
    #[When('I remove its default tax zone')]
    public function i_remove_its_default_tax_zone(): void
    {
        $this->client->add_request_data('defaultTaxZone', null);
    }
    #[When('I make it available in :locale')]
    public function i_make_it_available_in_locale(Locale_Interface $locale): void
    {
        $this->client->add_request_data('locales', [$this->iri_converter->get_iri_from_resource_in_section($locale, 'admin')]);
    }
    #[When('I make it available only in :locale')]
    public function i_make_it_available_only_in_locale(Locale_Interface $locale): void
    {
        $this->client->replace_request_data('locales', [$this->iri_converter->get_iri_from_resource_in_section($locale, 'admin')]);
    }
    #[When('I choose :locale as a default locale')]
    #[When('I do not choose default locale')]
    public function i_choose_as_a_default_locale(?Locale_Interface $locale = null): void
    {
        $this->client->add_request_data('defaultLocale', null === $locale ? $locale : $this->iri_converter->get_iri_from_resource_in_section($locale, 'admin'));
    }
    #[When('I describe it as :description')]
    public function i_describe_it_as(string $description): void
    {
        $this->client->add_request_data('description', $description);
    }
    #[When('I set its contact email as :contactEmail')]
    public function i_set_its_contact_email_as(string $contact_email): void
    {
        $this->client->add_request_data('contactEmail', $contact_email);
    }
    #[When('I set its contact phone number as :contactPhoneNumber')]
    public function i_set_its_contact_phone_number_as(string $contact_phone_number): void
    {
        $this->client->add_request_data('contactPhoneNumber', $contact_phone_number);
    }
    #[When('I choose :country and :otherCountry as operating countries')]
    public function i_choose_and_as_operating_countries(Country_Interface $country, Country_Interface $other_country): void
    {
        $this->client->add_request_data('countries', [$this->iri_converter->get_iri_from_resource_in_section($country, 'admin'), $this->iri_converter->get_iri_from_resource_in_section($other_country, 'admin')]);
    }
    #[When('I allow to skip shipping step if only one shipping method is available')]
    public function i_allow_to_skip_shipping_step_if_only_one_shipping_method_is_available(): void
    {
        $this->client->add_request_data('skippingShippingStepAllowed', true);
    }
    #[When('I allow to skip payment step if only one payment method is available')]
    public function i_allow_to_skip_payment_step_if_only_one_payment_method_is_available(): void
    {
        $this->client->add_request_data('skippingPaymentStepAllowed', true);
    }
    #[When('I specify menu taxon as :taxon')]
    public function i_specify_menu_taxon_as(Taxon_Interface $taxon): void
    {
        $this->client->add_request_data('menuTaxon', $this->iri_converter->get_iri_from_resource_in_section($taxon, 'admin'));
    }
    #[When('I specify company as :company')]
    public function i_specify_company_as(string $company): void
    {
        $this->shop_billing_data['company'] = $company;
    }
    #[When('I specify tax ID as :taxId')]
    public function i_specify_tax_id_as(string $tax_id): void
    {
        $this->shop_billing_data['taxId'] = $tax_id;
    }
    #[When('/^I specify shop billing data for (this channel) as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" tax ID and ("([^"]+)" country)$/')]
    public function i_specify_shop_billing_data_as(Channel_Interface $channel, string $company, string $street, string $postcode, string $city, string $tax_id, Country_Interface $country): void
    {
        $shop_billing_data_id = $this->iri_converter->get_iri_from_resource($channel->get_shop_billing_data());
        $this->client->add_request_data('shopBillingData', ['@id' => $shop_billing_data_id, 'company' => $company, 'street' => $street, 'postcode' => $postcode, 'city' => $city, 'countryCode' => $country->get_code(), 'taxId' => $tax_id]);
    }
    #[When('/^I specify new country code for (this channel) as "([^"]+)"$/')]
    public function i_specify_new_country_code_for_this_channel_as(Channel_Interface $channel, string $code): void
    {
        $shop_billing_data_id = $this->iri_converter->get_iri_from_resource($channel->get_shop_billing_data());
        $this->client->add_request_data('shopBillingData', ['@id' => $shop_billing_data_id, 'countryCode' => $code]);
    }
    #[When('I specify shop billing address as :street, :postcode :city, :country')]
    public function specify_shop_billing_address_as(string $street, string $postcode, string $city, Country_Interface $country): void
    {
        $this->shop_billing_data['street'] = $street;
        $this->shop_billing_data['city'] = $city;
        $this->shop_billing_data['postcode'] = $postcode;
        $this->shop_billing_data['countryCode'] = $country->get_code();
    }
    #[Then('I save it')]
    public function i_save_it(): void
    {
        $this->i_add_it();
        $this->client->update();
    }
    #[When('I select the :taxCalculationStrategy as tax calculation strategy')]
    public function i_select_tax_calculation_strategy(string $tax_calculation_strategy): void
    {
        $this->client->add_request_data('taxCalculationStrategy', String_Inflector::name_to_lowercase_code($tax_calculation_strategy));
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->client->set_sub_resource_data('shopBillingData', $this->shop_billing_data);
        $this->client->create();
    }
    #[When('I want to browse channels')]
    public function i_want_to_browse_channels(): void
    {
        $this->client->index(Resources::CHANNELS);
    }
    #[When('/^I choose (billing|shipping) address as a required address in the checkout$/')]
    public function i_choose_address_as_a_required_address_in_the_checkout(string $type): void
    {
        $this->client->add_request_data('shippingAddressInCheckoutRequired', $type === 'shipping');
    }
    #[When('/^I want to modify (this channel)$/')]
    public function i_want_to_modify_this_channel(Channel_Interface $channel): void
    {
        $this->client->build_update_request(Resources::CHANNELS, $channel->get_code());
    }
    #[When('/^I specify its ([^"]+) as a too long string$/')]
    public function i_specify_its_field_as_a_too_long_string(string $field): void
    {
        $this->client->add_request_data(String_Inflector::name_to_camel_case($field), str_repeat('a@', 128));
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($response = $this->client->get_last_response()), 'Channel could not be created: ' . $response->get_content());
    }
    #[Then('the channel :name should appear in the registry')]
    #[Then('the channel :name should be in the registry')]
    public function the_channel_should_appear_in_the_registry(string $name): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::CHANNELS), 'name', $name), sprintf('Channel with name %s does not exist', $name));
    }
    #[Then('the channel :channel should have :taxon as a menu taxon')]
    public function the_channel_should_have_as_a_menu_taxon(Channel_Interface $channel, Taxon_Interface $taxon): void
    {
        Assert::same($this->response_checker->get_value($this->client->show(Resources::CHANNELS, $channel->get_code()), 'menuTaxon'), $this->iri_converter->get_iri_from_resource_in_section($taxon, 'admin'), sprintf('Channel %s does not have %s menu taxon', $channel->get_name(), $taxon->get_name()));
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->update_request_data(['code' => 'NEW_CODE']);
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'));
    }
    #[Then('the base currency field should be disabled')]
    #[Then('I should not be able to edit its base currency')]
    public function the_base_currency_field_should_be_disabled(): void
    {
        $this->client->update_request_data(['baseCurrency' => 'PLN']);
        Assert::false($this->response_checker->has_value($this->client->update(), 'baseCurrency', 'PLN'));
    }
    #[Then('/^(this channel) name should be "([^"]*)"$/')]
    public function this_channel_name_should_be(Channel_Interface $channel, string $name): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::CHANNELS, $channel->get_code()), 'name', $name), sprintf('Its Channel does not have name %s.', $name));
    }
    #[Then('the :channel channel should no longer exist in the registry')]
    public function the_channel_should_no_longer_exist_in_the_registry(string $name): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::CHANNELS), 'name', $name), sprintf('Channel with name %s exists', $name));
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_channel_has_been_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()));
    }
    #[Then('/^(this channel) menu (taxon should be "([^"]+)")$/')]
    public function this_channel_menu_taxon_should_be(Channel_Interface $channel, Taxon_Interface $taxon): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::CHANNELS, $channel->get_code()), 'menuTaxon', $this->iri_converter->get_iri_from_resource_in_section($taxon, 'admin')));
    }
    #[Then('I should see :count channels in the list')]
    public function i_should_see_channels_in_the_list(int $count): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('/^the required address in the checkout for this channel should be (billing|shipping)$/')]
    public function the_required_address_in_the_checkout_for_the_channel_should_be(string $type): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'shippingAddressInCheckoutRequired', $type === 'shipping'));
    }
    #[Then('I should be notified that it cannot be deleted')]
    public function i_should_be_notified_that_it_cannot_be_deleted(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The channel cannot be deleted. At least one enabled channel is required.');
    }
    #[Then('I should be notified that at least one channel has to be defined')]
    public function i_should_be_notified_that_at_least_one_channel_has_to_be_defined(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Must have at least one enabled entity');
    }
    #[Then('channel with name :channel should still be enabled')]
    #[Then('/^(this channel) should be enabled$/')]
    public function channel_with_name_should_still_be_enabled(Channel_Interface $channel): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::CHANNELS, $channel->get_code()), 'enabled', true), sprintf('Channel with name %s does not exists', $channel->get_name()));
    }
    #[Then('this channel should still be named :channel')]
    public function this_channel_should_still_be_named(Channel_Interface $channel): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::CHANNELS, $channel->get_code()), 'name', $channel->get_name()), sprintf('Channel with name %s does not exists', $channel->get_name()));
    }
    #[Then('paying in :currency should be possible for the :channel channel')]
    public function paying_in_currency_should_be_possible_for_the_channel(Currency_Interface $currency, Channel_Interface $channel): void
    {
        $currencies = $this->response_checker->get_value($this->client->show(Resources::CHANNELS, $channel->get_code()), 'currencies');
        Assert::true(in_array($this->iri_converter->get_iri_from_resource_in_section($currency, 'admin'), $currencies));
    }
    #[Then('channel :channel should not have default tax zone')]
    public function channel_should_not_have_default_tax_zone(Channel_Interface $channel): void
    {
        Assert::same($this->response_checker->get_value($this->client->show(Resources::CHANNELS, $channel->get_code()), 'defaultTaxZone'), null, sprintf('Channel %s has default tax zone', $channel->get_name()));
    }
    #[Then('the default tax zone for the :channel channel should be :zone')]
    public function the_default_tax_zone_for_the_channel_should_be(Channel_Interface $channel, Zone_Interface $zone): void
    {
        Assert::same($this->response_checker->get_value($this->client->show(Resources::CHANNELS, $channel->get_code()), 'defaultTaxZone'), $this->iri_converter->get_iri_from_resource_in_section($zone, 'admin'), sprintf('Channel %s does not have %s default tax zone', $channel->get_name(), $zone));
    }
    #[Then('the channel :channel should be available in :locale')]
    public function the_channel_should_be_available_in(Channel_Interface $channel, Locale_Interface $locale): void
    {
        $locales = $this->response_checker->get_value($this->client->show(Resources::CHANNELS, $channel->get_code()), 'locales');
        Assert::true(in_array($this->iri_converter->get_iri_from_resource_in_section($locale, 'admin'), $locales));
    }
    #[Then('I should be notified that the default locale has to be enabled')]
    public function i_should_be_notified_that_the_default_locale_has_to_be_enabled(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'defaultLocale: Default locale has to be enabled.');
    }
    #[Then('/^(this channel) should still be in the registry$/')]
    public function this_channel_should_still_be_in_the_registry(Channel_Interface $channel): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::CHANNELS), 'code', $channel->get_code()), sprintf('Channel with code %s does not exists', $channel->get_code()));
    }
    #[Then('the tax calculation strategy for the :channel channel should be :taxCalculationStrategy')]
    public function the_tax_calculation_strategy_for_the_channel_should_be(Channel_Interface $channel, string $tax_calculation_strategy): void
    {
        Assert::same($this->response_checker->get_value($this->client->show(Resources::CHANNELS, $channel->get_code()), 'taxCalculationStrategy'), String_Inflector::name_to_lowercase_code($tax_calculation_strategy), sprintf('Channel %s does not have %s tax calculation strategy', $channel->get_name(), $tax_calculation_strategy));
    }
    #[Then('/^(this channel) should be disabled$/')]
    public function this_channel_should_be_disabled(Channel_Interface $channel): void
    {
        Assert::same($this->response_checker->get_value($this->client->show(Resources::CHANNELS, $channel->get_code()), 'enabled'), false, sprintf('Channel %s is enabled', $channel->get_name()));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s: Please enter channel %s.', String_Inflector::name_to_camel_case($element), $element));
    }
    #[Then('I should be notified that base currency is required')]
    public function i_should_be_notified_that_base_currency_is_required(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The type of the "baseCurrency" attribute must be "array" (nested document) or "string" (IRI), "NULL" given.');
    }
    #[Then('I should be notified that default locale is required')]
    public function i_should_be_notified_that_default_locale_is_required(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The type of the "defaultLocale" attribute must be "array" (nested document) or "string" (IRI), "NULL" given.');
    }
    #[Then('channel with :element :value should not be added')]
    public function channel_with_should_not_be_added(string $element, string $value): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::CHANNELS), $element, $value), sprintf('Channel with %s: %s exists', $element, $value));
    }
    #[Then('I should be notified that channel with this code already exists')]
    public function i_should_be_notified_that_channel_with_this_code_already_exists(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'code: Channel code has to be unique.');
    }
    #[Then('there should still be only one channel with :element :value')]
    public function there_should_still_be_only_one_channel_with_code(string $element, string $value): void
    {
        Assert::same(count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::CHANNELS), $element, $value)), 1, sprintf('There is more than one channel with %s: %s', $element, $value));
    }
    #[Then('I should be notified that it is not a valid country')]
    public function i_should_be_notified_that_it_is_not_a_valid_country_code(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'countryCode: This value is not a valid country.');
    }
}
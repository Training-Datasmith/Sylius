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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\When;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Service\Setter\Channel_Context_Setter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Channel\Repository\Channel_Repository_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Shop_Billing_Data_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Core\Test\Services\Default_Channel_Factory_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Channel_Context implements Context
{
    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     * @param FactoryInterface<ShopBillingDataInterface> $shopBillingDataFactory
     */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Channel_Context_Setter_Interface $channel_context_setter, private Default_Channel_Factory_Interface $united_states_channel_factory, private Default_Channel_Factory_Interface $default_channel_factory, private Channel_Repository_Interface $channel_repository, private Object_Manager $channel_manager, private Factory_Interface $shop_billing_data_factory)
    {
    }
    #[Given(':channel channel has account verification disabled')]
    public function channel_has_account_verification_disabled(Channel_Interface $channel): void
    {
        $channel->set_account_verification_required(false);
        $this->channel_manager->flush();
    }
    #[Given('/^the (channel "[^"]+") has ("([^"]+)" and "([^"]+)" taxons) excluded from showing the lowest price of discounted products$/')]
    public function the_taxon_and_taxon_are_excluded_from_showing_the_lowest_price_of_discounted_products_on_this_channel(Channel_Interface $channel, iterable $taxons): void
    {
        /** @var TaxonInterface $taxon */
        foreach ($taxons as $taxon) {
            $channel->get_channel_price_history_config()->add_taxon_excluded_from_showing_lowest_price($taxon);
        }
        $this->channel_manager->flush();
    }
    #[Given('the store operates on a single channel in "United States"')]
    public function store_operates_on_a_single_channel_in_united_states(): void
    {
        $default_data = $this->united_states_channel_factory->create();
        $this->shared_storage->set_clipboard($default_data);
        $this->shared_storage->set('channel', $default_data['channel']);
    }
    #[Given('the store operates on a single channel in the "United States" named :channelName')]
    public function store_operates_on_a_single_channel_in_the_united_states_named(string $channel_name): void
    {
        $channel_code = String_Inflector::name_to_lowercase_code($channel_name);
        $default_data = $this->united_states_channel_factory->create($channel_code, $channel_name);
        $this->shared_storage->set_clipboard($default_data);
        $this->shared_storage->set('channel', $default_data['channel']);
    }
    #[Given('the store operates on a single channel')]
    #[Given('the store operates on a single channel in :currencyCode currency')]
    public function store_operates_on_a_single_channel(?string $currency_code = null): void
    {
        $default_data = $this->default_channel_factory->create(null, null, $currency_code);
        $this->shared_storage->set_clipboard($default_data);
        $this->shared_storage->set('channel', $default_data['channel']);
    }
    #[Given('the store operates on a single channel in :localeCode locale')]
    public function store_operates_on_a_single_channel_in_locale(string $locale_code): void
    {
        $default_data = $this->default_channel_factory->create(localeCode: $locale_code);
        $this->shared_storage->set_clipboard($default_data);
        $this->shared_storage->set('channel', $default_data['channel']);
    }
    #[Given('/^the store(?:| also) operates on (?:a|another) channel named "([^"]+)"$/')]
    #[Given('/^the store(?:| also) operates on (?:a|another) channel named "([^"]+)" in "([^"]+)" currency$/')]
    #[Given('/^the store(?:| also) operates on (?:a|another) channel named "([^"]+)" in "([^"]+)" currency and with hostname "([^"]+)"$/')]
    #[Given('the store (also) operates on a(nother) channel named :channelName with hostname :hostname')]
    #[Given('the store operates on a channel identified by :channelCode code')]
    public function the_store_operates_on_a_channel_named(?string $channel_name = null, ?string $currency_code = null, ?string $hostname = null, ?string $channel_code = null): void
    {
        $channel_code ??= String_Inflector::name_to_lowercase_code($channel_name);
        $channel_name ??= $channel_code;
        $default_data = $this->default_channel_factory->create($channel_code, $channel_name, $currency_code);
        $default_data['channel']->set_hostname($hostname);
        $this->shared_storage->set_clipboard($default_data);
        $this->shared_storage->set('channel', $default_data['channel']);
    }
    #[Given('the channel :channel is enabled')]
    public function the_channel_is_enabled(Channel_Interface $channel): void
    {
        $this->change_channel_state($channel, true);
    }
    #[Given('the channel :channel is disabled')]
    #[Given('the channel :channel has been disabled')]
    public function the_channel_is_disabled(Channel_Interface $channel): void
    {
        $this->change_channel_state($channel, false);
    }
    #[Given('/^the (channel "[^"]+") has showing the lowest price of discounted products (enabled|disabled)$/')]
    public function the_channel_has_showing_the_lowest_price_of_discounted_products(Channel_Interface $channel, string $visible): void
    {
        $channel->get_channel_price_history_config()->set_lowest_price_for_discounted_products_visible($visible === 'enabled');
        $this->channel_manager->flush();
    }
    #[Given('channel :channel has been deleted')]
    public function i_channel_has_been_deleted(Channel_Interface $channel): void
    {
        $this->channel_repository->remove($channel);
    }
    #[Given('/^(its) default tax zone is (zone "([^"]+)")$/')]
    public function its_default_tax_rate_is(Channel_Interface $channel, Zone_Interface $default_tax_zone): void
    {
        $channel->set_default_tax_zone($default_tax_zone);
        $this->channel_manager->flush();
    }
    #[Given('/^(this channel) has contact email set as "([^"]+)"$/')]
    #[Given('/^(this channel) has no contact email set$/')]
    public function this_channel_has_contact_email_set_as(Channel_Interface $channel, ?string $contact_email = null): void
    {
        $channel->set_contact_email($contact_email);
        $this->channel_manager->flush();
    }
    #[Given('/^on (this channel) shipping step is skipped if only a single shipping method is available$/')]
    public function on_this_channel_shipping_step_is_skipped_if_only_a_single_shipping_method_is_available(Channel_Interface $channel): void
    {
        $channel->set_skipping_shipping_step_allowed(true);
        $this->channel_manager->flush();
    }
    #[Given('/^on (this channel) payment step is skipped if only a single payment method is available$/')]
    public function on_this_channel_payment_step_is_skipped_if_only_a_single_payment_method_is_available(Channel_Interface $channel): void
    {
        $channel->set_skipping_payment_step_allowed(true);
        $this->channel_manager->flush();
    }
    #[Given('/^on (this channel) account verification is not required$/')]
    public function on_this_channel_account_verification_is_not_required(Channel_Interface $channel): void
    {
        $channel->set_account_verification_required(false);
        $this->channel_manager->flush();
    }
    #[Given('/^on (this channel) account verification is required$/')]
    public function on_this_channel_account_verification_is_required(Channel_Interface $channel): void
    {
        $channel->set_account_verification_required(true);
        $this->channel_manager->flush();
    }
    #[Given('channel :channel billing data is :company, :street, :postcode :city, :country with :taxId tax ID')]
    public function channel_billing_data_is(Channel_Interface $channel, string $company, string $street, string $postcode, string $city, Country_Interface $country, string $tax_id): void
    {
        $shop_billing_data = $this->shop_billing_data_factory->create_new();
        $shop_billing_data->set_company($company);
        $shop_billing_data->set_street($street);
        $shop_billing_data->set_postcode($postcode);
        $shop_billing_data->set_city($city);
        $shop_billing_data->set_country_code($country->get_code());
        $shop_billing_data->set_tax_id($tax_id);
        $channel->set_shop_billing_data($shop_billing_data);
        $this->channel_manager->flush();
    }
    #[Given('channel :channel has menu taxon :taxon')]
    #[Given('/^(this channel) has menu (taxon "[^"]+")$/')]
    public function channel_has_menu_taxon(Channel_Interface $channel, Taxon_Interface $taxon): void
    {
        $channel->set_menu_taxon($taxon);
        $this->channel_manager->flush();
    }
    #[Given('/^(this channel) operates in the ("[^"]+" country)$/')]
    public function channel_operates_in_country(Channel_Interface $channel, Country_Interface $country): void
    {
        $channel->add_country($country);
        $this->channel_manager->flush();
    }
    #[Given('/^(this channel) does not define operating countries$/')]
    public function channel_does_not_define_operating_countries(Channel_Interface $channel): void
    {
        foreach ($channel->get_countries() as $country) {
            $channel->remove_country($country);
        }
        $this->channel_manager->flush();
    }
    #[Given('/^I changed my current (channel to "([^"]+)")$/')]
    #[Given('I am in the :channel channel')]
    #[When('/^I change (?:|back )my current (channel to "([^"]+)")$/')]
    #[When('customer view shop on :channel channel')]
    public function i_changed_my_current_channel_to(Channel_Interface $channel): void
    {
        $this->shared_storage->set('channel', $channel);
        $this->shared_storage->set('hostname', $channel->get_hostname());
        $this->channel_context_setter->set_channel($channel);
    }
    #[Given('/^its required address in the checkout is (billing|shipping)$/')]
    public function its_required_address_in_the_checkout_is(string $type): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->shared_storage->get('channel');
        $channel->set_shipping_address_in_checkout_required($type === 'shipping');
        $this->channel_manager->flush();
    }
    #[Given('/^(this channel) has (\d+) day(?:|s) set as the lowest price for discounted products checking period$/')]
    public function this_channel_has_days_set_as_the_lowest_price_for_discounted_products_checking_period(Channel_Interface $channel, int $days): void
    {
        $channel->get_channel_price_history_config()->set_lowest_price_for_discounted_products_checking_period($days);
        $this->channel_manager->flush();
    }
    #[Given('the :taxon taxon is excluded from showing the lowest price of discounted products in the :channel channel')]
    public function the_taxon_is_excluded_from_showing_the_lowest_price_of_discounted_products_in_the_channel(Taxon_Interface $taxon, Channel_Interface $channel): void
    {
        $channel->get_channel_price_history_config()->add_taxon_excluded_from_showing_lowest_price($taxon);
        $this->channel_manager->flush();
    }
    #[Given('/^the lowest price of discounted products prior to the current discount is disabled on (this channel)$/')]
    public function the_lowest_price_of_discounted_products_prior_to_the_current_discount_is_disabled_on_this_channel(Channel_Interface $channel): void
    {
        $channel->get_channel_price_history_config()->set_lowest_price_for_discounted_products_visible(false);
    }
    #[Given('the store also operates in :locale locale')]
    public function the_store_also_operates_in_locale(Locale_Interface $locale): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->shared_storage->get('channel');
        $channel->add_locale($locale);
        $this->channel_manager->flush();
    }
    #[Given('the store uses the :taxCalculationStrategy tax calculation strategy')]
    public function the_store_uses_the_tax_calculation_strategy(string $tax_calculation_strategy): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->shared_storage->get('channel');
        $channel->set_tax_calculation_strategy(String_Inflector::name_to_lowercase_code($tax_calculation_strategy));
        $this->channel_manager->flush();
    }
    private function change_channel_state(Channel_Interface $channel, bool $state): void
    {
        $channel->set_enabled($state);
        $this->channel_manager->flush();
        $this->shared_storage->set('channel', $channel);
    }
}
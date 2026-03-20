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
use Sylius\Behat\Context\Ui\Admin\Helper\Validation_Trait;
use Sylius\Behat\Element\Admin\Channel\Discounted_Products_Checking_Period_Input_Element_Interface;
use Sylius\Behat\Element\Admin\Channel\Exclude_Taxons_From_Showing_Lowest_Price_Input_Element_Interface;
use Sylius\Behat\Element\Admin\Channel\Lowest_Price_Flag_Element_Interface;
use Sylius\Behat\Element\Admin\Channel\Shipping_Address_In_Checkout_Required_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Channel\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Channel\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Channel\Update_Page_Interface;
use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Currency\Model\Currency_Interface;
use Webmozart\Assert\Assert;
final class Managing_Channels_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Index_Page_Interface $index_page, private Create_Page_Interface $create_page, private Update_Page_Interface $update_page, private Shipping_Address_In_Checkout_Required_Element_Interface $shipping_address_in_checkout_required_element, private Current_Page_Resolver_Interface $current_page_resolver, private Notification_Checker_Interface $notification_checker, private Discounted_Products_Checking_Period_Input_Element_Interface $discounted_products_checking_period_input_element, private Lowest_Price_Flag_Element_Interface $lowest_price_flag_element, private Exclude_Taxons_From_Showing_Lowest_Price_Input_Element_Interface $exclude_taxons_from_showing_lowest_price_input_element)
    {
    }
    #[When('I want to create a new channel')]
    public function i_want_to_create_a_new_channel(): void
    {
        $this->create_page->open();
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->create_page->specify_code($code ?? '');
    }
    #[When('I name it :name')]
    #[When('I rename it to :name')]
    #[When('I do not name it')]
    #[When('I remove its name')]
    public function i_name_it(?string $name = null): void
    {
        $this->create_page->name_it($name ?? '');
    }
    #[When('I specify its name as a too long string')]
    public function i_specify_its_name_as_a_too_long_string(): void
    {
        $this->create_page->name_it($this->get_too_long_string());
    }
    #[When('I choose :currency as the base currency')]
    #[When('I do not choose base currency')]
    public function i_choose_as_a_base_currency(?Currency_Interface $currency = null): void
    {
        if (null !== $currency) {
            $this->create_page->choose_base_currency($currency->get_name());
        }
    }
    #[When('I choose :defaultLocaleName as a default locale')]
    #[When('I do not choose default locale')]
    public function i_choose_as_a_default_locale(?string $default_locale_name = null): void
    {
        if (null !== $default_locale_name) {
            $this->create_page->choose_default_locale($default_locale_name);
        }
    }
    #[When('I choose :firstCountry and :secondCountry as operating countries')]
    public function i_choose_operating_countries(string ...$countries): void
    {
        $this->create_page->choose_operating_countries($countries);
    }
    #[When('I specify menu taxon as :menuTaxon')]
    #[When('I change its menu taxon to :menuTaxon')]
    public function i_specify_menu_taxon_as(string $menu_taxon): void
    {
        $this->resolve_current_page()->specify_menu_taxon($menu_taxon);
    }
    #[When('I allow to skip shipping step if only one shipping method is available')]
    public function i_allow_to_skip_shipping_step_if_only_one_shipping_method_is_available(): void
    {
        $this->create_page->allow_to_skip_shipping_step();
    }
    #[When('I allow to skip payment step if only one payment method is available')]
    public function i_allow_to_skip_payment_step_if_only_one_payment_method_is_available(): void
    {
        $this->create_page->allow_to_skip_payment_step();
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('/^I choose (billing|shipping) address as a required address in the checkout$/')]
    public function i_choose_address_as_a_required_address_in_the_checkout(string $type): void
    {
        $this->shipping_address_in_checkout_required_element->require_address_type_in_checkout($type);
    }
    #[Then('I should see the channel :channelName in the list')]
    #[Then('the channel :channelName should appear in the registry')]
    #[Then('the channel :channelName should be in the registry')]
    public function the_channel_should_appear_in_the_registry(string $channel_name): void
    {
        $this->i_want_to_browse_channels();
        Assert::true($this->index_page->is_single_resource_on_page(['nameAndDescription' => $channel_name]));
    }
    #[Then('/^(this channel) should still be in the registry$/')]
    public function this_channel_should_appear_in_the_registry(Channel_Interface $channel): void
    {
        $this->the_channel_should_appear_in_the_registry($channel->get_name());
    }
    #[When('I describe it as :description')]
    public function i_describe_it_as(string $description): void
    {
        $this->create_page->describe_it_as($description);
    }
    #[When('I set its hostname as :hostname')]
    public function i_set_its_hostname_as(string $hostname): void
    {
        $this->create_page->set_hostname($hostname);
    }
    #[When('I specify its hostname as a too long string')]
    public function i_specify_its_hostname_as_a_too_long_string(): void
    {
        $this->create_page->set_hostname($this->get_too_long_string());
    }
    #[When('I set its contact email as :contactEmail')]
    public function i_set_its_contact_email_as(string $contact_email): void
    {
        $this->create_page->set_contact_email($contact_email);
    }
    #[When('I specify its contact email as a too long string')]
    public function i_specify_its_contact_email_as_a_too_long_string(): void
    {
        $this->create_page->set_contact_email($this->get_too_long_string());
    }
    #[When('I set its contact phone number as :contactPhoneNumber')]
    public function i_set_its_contact_phone_number_as(string $contact_phone_number): void
    {
        $this->create_page->set_contact_phone_number($contact_phone_number);
    }
    #[When('I specify its contact phone number as a too long string')]
    public function i_specify_its_contact_phone_number_as_a_too_long_string(): void
    {
        $this->create_page->set_contact_phone_number($this->get_too_long_string());
    }
    #[When('I define its color as :color')]
    public function i_define_its_color_as(string $color): void
    {
        $this->create_page->define_color($color);
    }
    #[When('I specify its color as a too long string')]
    public function i_specify_its_color_as_a_too_long_string(): void
    {
        $this->create_page->define_color($this->get_too_long_string());
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->update_page->enable();
    }
    #[When('I disable it')]
    public function i_disable_it(): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        $current_page->disable();
    }
    #[When('I exclude the :taxon taxon from showing the lowest price of discounted products')]
    public function i_exclude_the_taxon_from_showing_the_lowest_price_of_discounted_products(Taxon_Interface $taxon): void
    {
        $this->exclude_taxons_from_showing_lowest_price_input_element->exclude_taxon($taxon);
    }
    #[When('/^I exclude the ("([^"]+)" and "([^"]+)" taxons) from showing the lowest price of discounted products$/')]
    public function i_exclude_the_taxons_from_showing_the_lowest_price_of_discounted_products(iterable $taxons): void
    {
        foreach ($taxons as $taxon) {
            $this->exclude_taxons_from_showing_lowest_price_input_element->exclude_taxon($taxon);
        }
    }
    #[When('I remove the :taxon taxon from excluded taxons from showing the lowest price of discounted products')]
    public function i_remove_the_taxon_from_excluded_taxons_from_showing_the_lowest_price_of_discounted_products(Taxon_Interface $taxon): void
    {
        $this->exclude_taxons_from_showing_lowest_price_input_element->remove_excluded_taxon($taxon);
    }
    #[Then('I should be notified that at least one channel has to be defined')]
    public function i_should_be_notified_that_at_least_one_channel_has_to_be_defined_is_required(): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        Assert::same($current_page->get_validation_message('enabled'), 'Must have at least one enabled entity');
    }
    #[Then('channel with :element :value should not be added')]
    public function channel_with_should_not_be_added(string $element, string $value): void
    {
        $this->i_want_to_browse_channels();
        Assert::false($this->index_page->is_single_resource_on_page([$element => $value]));
    }
    #[Then('/^I should be notified that ([^"]+) is required$/')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        Assert::same($current_page->get_validation_message(String_Inflector::name_to_code($element)), sprintf('Please enter channel %s.', $element));
    }
    #[Given('I am modifying a channel :channel')]
    #[When('I want to modify a channel :channel')]
    #[When('/^I want to modify (this channel)$/')]
    #[When('I want to modify a billing data of channel :channel')]
    public function i_want_to_modify_channel(Channel_Interface $channel): void
    {
        $this->update_page->open(['id' => $channel->get_id()]);
    }
    #[Then('/^(this channel) name should be "([^"]+)"$/')]
    #[Then('/^(this channel) should still be named "([^"]+)"$/')]
    public function this_channel_name_should_be(Channel_Interface $channel, string $channel_name): void
    {
        $this->i_want_to_browse_channels();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $channel->get_code(), 'nameAndDescription' => $channel_name]));
    }
    #[Then('I should be notified that channel with this code already exists')]
    public function i_should_be_notified_that_channel_with_this_code_already_exists(): void
    {
        Assert::same($this->create_page->get_validation_message('code'), 'Channel code has to be unique.');
    }
    #[Then('there should still be only one channel with :element :value')]
    public function there_should_still_be_only_one_channel_with_code(string $element, string $value): void
    {
        $this->i_want_to_browse_channels();
        Assert::true($this->index_page->is_single_resource_on_page([$element => $value]));
    }
    #[When('I browse channels')]
    #[When('I want to browse channels')]
    public function i_want_to_browse_channels(): void
    {
        $this->index_page->open();
    }
    #[When('I check (also) the :channelName channel')]
    public function i_check_the_channel(string $channel_name): void
    {
        $this->index_page->check_resource_on_page(['nameAndDescription' => $channel_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[Then('I should see a single channel in the list')]
    #[Then('I should see :numberOfChannels channels in the list')]
    public function i_should_see_channels_in_the_list(int $number_of_channels = 1): void
    {
        Assert::same($this->index_page->count_items(), $number_of_channels);
    }
    #[Then('the code field should be disabled')]
    #[Then('I should not be able to edit its code')]
    public function the_code_field_should_be_disabled(): void
    {
        Assert::true($this->update_page->is_code_disabled());
    }
    #[Then('/^(this channel) should be disabled$/')]
    public function this_channel_should_be_disabled(Channel_Interface $channel): void
    {
        $this->assert_channel_state($channel, false);
    }
    #[Then('/^(this channel) should be enabled$/')]
    #[Then('channel with name :channel should still be enabled')]
    public function this_channel_should_be_enabled(Channel_Interface $channel): void
    {
        $this->assert_channel_state($channel, true);
    }
    #[When('I delete channel :channel')]
    public function i_delete_channel(Channel_Interface $channel): void
    {
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['nameAndDescription' => $channel->get_name()]);
    }
    #[Then('the :channelName channel should no longer exist in the registry')]
    public function this_channel_should_no_longer_exist_in_the_registry(string $channel_name): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['nameAndDescription' => $channel_name]));
    }
    #[Then('I should be notified that it cannot be deleted')]
    public function i_should_be_notified_that_it_cannot_be_deleted(): void
    {
        $this->notification_checker->check_notification('The channel cannot be deleted. At least one enabled channel is required.', Notification_Type::failure());
    }
    #[When('I make it available (only) in :nameOfLocale')]
    public function i_make_it_available_in(string $name_of_locale): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        $current_page->choose_locale($name_of_locale);
    }
    #[Then('the channel :channel should be available in :nameOfLocale')]
    public function the_channel_should_be_available_in(Channel_Interface $channel, string $name_of_locale): void
    {
        $this->update_page->open(['id' => $channel->get_id()]);
        Assert::in_array($name_of_locale, $this->update_page->get_locales());
    }
    #[When('I allow for paying in :currencyCode')]
    public function i_allow_to_paying_for_this_channel(string $currency_code): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        $current_page->choose_currency($currency_code);
    }
    #[Then('paying in :currency should be possible for the :channel channel')]
    public function paying_in_currency_should_be_possible_for_the_channel(Currency_Interface $currency, Channel_Interface $channel): void
    {
        $this->update_page->open(['id' => $channel->get_id()]);
        Assert::in_array($currency->get_name(), $this->update_page->get_currencies());
    }
    #[When('I select the :taxZone as default tax zone')]
    public function i_select_default_tax_zone(string $tax_zone): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        $current_page->choose_default_tax_zone($tax_zone);
    }
    #[Given('I remove its default tax zone')]
    public function i_remove_its_default_tax_zone(): void
    {
        $this->update_page->choose_default_tax_zone('');
    }
    #[When('I select the :taxCalculationStrategy as tax calculation strategy')]
    public function i_select_tax_calculation_strategy(string $tax_calculation_strategy): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        $current_page->choose_tax_calculation_strategy($tax_calculation_strategy);
    }
    #[When('/^I specify (-?\d+) days as the lowest price for discounted products checking period$/')]
    public function i_specify_days_as_the_lowest_price_for_discounted_products_checking_period(int $days): void
    {
        $this->discounted_products_checking_period_input_element->specify_period($days);
    }
    #[Then('/^the "[^"]+" channel should have the lowest price for discounted products checking period set to (\d+) days$/')]
    #[Then('its lowest price for discounted products checking period should be set to :days days')]
    public function the_channel_should_have_the_lowest_price_for_discounted_products_checking_period_set_to_days(int $days): void
    {
        $lowest_price_for_discounted_products_checking_period = $this->discounted_products_checking_period_input_element->get_period();
        Assert::same($days, $lowest_price_for_discounted_products_checking_period);
    }
    #[Then('I should be notified that the lowest price for discounted products checking period must be lower')]
    public function i_should_be_notified_that_the_lowest_price_for_discounted_products_checking_period_must_be_lower(): void
    {
        Assert::same('Value must be less than 2147483647', $this->update_page->get_validation_message('discounted_products_checking_period'));
    }
    #[When('/^I (enable|disable) showing the lowest price of discounted products$/')]
    public function i_enable_showing_the_lowest_price_of_discounted_products(string $visible): void
    {
        $this->lowest_price_flag_element->{$visible}();
    }
    #[Then('I should be notified that the lowest price for discounted products checking period must be greater than 0')]
    public function i_should_be_notified_that_the_lowest_price_for_discounted_products_checking_period_must_be_greater_than_zero(): void
    {
        Assert::same('Value must be greater than 0', $this->update_page->get_validation_message('discounted_products_checking_period'));
    }
    #[Then('/^the ("[^"]+" channel) should have the lowest price of discounted products prior to the current discount (enabled|disabled)$/')]
    public function the_channel_should_have_the_lowest_price_of_discounted_products_prior_to_the_current_discount_enabled_or_disabled(Channel_Interface $channel, string $visible): void
    {
        Assert::same('enabled' === $visible, $this->lowest_price_flag_element->is_enabled());
    }
    #[Then('/^this channel should have ("([^"]+)" and "([^"]+)" taxons) excluded from displaying the lowest price of discounted products$/')]
    public function this_channel_should_have_taxons_excluded_from_displaying_the_lowest_price_of_discounted_products(iterable $taxons): void
    {
        foreach ($taxons as $taxon) {
            Assert::true($this->exclude_taxons_from_showing_lowest_price_input_element->has_taxon_excluded($taxon), sprintf('The taxon with code %s should be excluded from displaying the lowest price of discounted products', $taxon->get_code()));
        }
    }
    #[Then('the default tax zone for the :channel channel should be :taxZone')]
    public function the_default_tax_zone_for_the_channel_should_be(Channel_Interface $channel, string $tax_zone): void
    {
        $this->update_page->open(['id' => $channel->get_id()]);
        Assert::same($this->update_page->get_default_tax_zone(), $tax_zone);
    }
    #[Then('channel :channel should not have default tax zone')]
    public function channel_should_not_have_default_tax_zone(Channel_Interface $channel): void
    {
        $this->update_page->open(['id' => $channel->get_id()]);
        Assert::is_empty($this->update_page->get_default_tax_zone());
    }
    #[Then('the tax calculation strategy for the :channel channel should be :taxCalculationStrategy')]
    public function the_tax_calculation_strategy_for_the_channel_should_be(Channel_Interface $channel, string $tax_calculation_strategy): void
    {
        $this->update_page->open(['id' => $channel->get_id()]);
        Assert::same($this->update_page->get_tax_calculation_strategy(), $tax_calculation_strategy);
    }
    #[Then('the base currency field should be disabled')]
    #[Then('I should not be able to edit its base currency')]
    public function the_base_currency_field_should_be_disabled(): void
    {
        Assert::true($this->update_page->is_base_currency_disabled());
    }
    #[Then('I should be notified that the default locale has to be enabled')]
    public function i_should_be_notified_that_the_default_locale_has_to_be_enabled(): void
    {
        Assert::same($this->update_page->get_validation_message('default_locale'), 'Default locale has to be enabled.');
    }
    #[Given('/^(this channel) menu taxon should be "([^"]+)"$/')]
    #[Given('the channel :channel should have :menuTaxon as a menu taxon')]
    public function this_channel_menu_taxon_should_be(Channel_Interface $channel, string $menu_taxon): void
    {
        if (!$this->update_page->is_open(['id' => $channel->get_id()])) {
            $this->update_page->open(['id' => $channel->get_id()]);
        }
        Assert::same($this->update_page->get_menu_taxon(), $menu_taxon);
    }
    #[Then('this channel should have :taxon taxon excluded from displaying the lowest price of discounted products')]
    public function this_channel_should_have_taxon_excluded_from_displaying_the_lowest_price_of_discounted_products(Taxon_Interface $taxon): void
    {
        Assert::true($this->exclude_taxons_from_showing_lowest_price_input_element->has_taxon_excluded($taxon), sprintf('The taxon with code %s should be excluded from displaying the lowest price of discounted products', $taxon->get_code()));
    }
    #[Then('this channel should not have :taxon taxon excluded from displaying the lowest price of discounted products')]
    public function this_channel_should_not_have_taxon_excluded_from_displaying_the_lowest_price_of_discounted_products(Taxon_Interface $taxon): void
    {
        Assert::false($this->exclude_taxons_from_showing_lowest_price_input_element->has_taxon_excluded($taxon), sprintf('The taxon with code %s should be not be excluded from displaying the lowest price of discounted products', $taxon->get_code()));
    }
    #[Then('/^the required address in the checkout for this channel should be (billing|shipping)$/')]
    public function the_required_address_in_the_checkout_for_this_channel_should_be(string $type): void
    {
        Assert::same($this->shipping_address_in_checkout_required_element->get_required_address_type_in_checkout(), $type);
    }
    private function assert_channel_state(Channel_Interface $channel, bool $state): void
    {
        $this->i_want_to_browse_channels();
        Assert::true($this->index_page->is_single_resource_with_specific_element_on_page(['nameAndDescription' => $channel->get_name()], $state ? '[data-test-status-enabled]' : '[data-test-status-disabled]'));
    }
    private function get_too_long_string(): string
    {
        return str_repeat('a@', 128);
    }
    protected function resolve_current_page(): Sylius_Page_Interface
    {
        return $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
    }
}
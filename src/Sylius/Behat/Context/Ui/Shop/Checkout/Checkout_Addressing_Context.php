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
namespace Sylius\Behat\Context\Ui\Shop\Checkout;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Friends_Of_Behat\Page_Object_Extension\Page\Unexpected_Page_Exception;
use Sylius\Behat\Page\Shop\Checkout\Address_Page_Interface;
use Sylius\Behat\Page\Shop\Checkout\Select_Shipping_Page_Interface;
use Sylius\Behat\Service\Factory\Address_Factory_Interface;
use Sylius\Behat\Service\Helper\Java_Script_Test_Helper_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Comparator\Address_Comparator_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Core\Model\Address_Interface;
use Webmozart\Assert\Assert;
final readonly class Checkout_Addressing_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Address_Page_Interface $address_page, private Address_Factory_Interface $address_factory, private Address_Comparator_Interface $address_comparator, private Select_Shipping_Page_Interface $select_shipping_page, private Java_Script_Test_Helper_Interface $test_helper)
    {
    }
    #[Given('the visitor has completed the addressing step')]
    #[Given('the customer has completed the addressing step')]
    #[When('the customer completes the addressing step')]
    #[When('the visitor completes the addressing step')]
    public function the_visitor_has_completed_the_addressing_step(): void
    {
        $this->address_page->next_step();
    }
    #[Given('my billing address is fulfilled automatically through default address')]
    public function my_billing_address_is_fulfilled_automatically_through_default_address(): void
    {
        //intentionally blank line for api tests
    }
    #[Given('I am at the checkout addressing step')]
    #[When('I go to the checkout addressing step')]
    #[When('I go back to addressing step of the checkout')]
    public function i_am_at_the_checkout_addressing_step(): void
    {
        $this->address_page->open();
    }
    #[Given('/^I have completed addressing step with email "([^"]+)" and ("[^"]+" based billing address)$/')]
    #[Given('/^they have completed addressing step with email "([^"]+)" and ("[^"]+" based billing address)$/')]
    #[When('/^I complete addressing step with email "([^"]+)" and ("[^"]+" based billing address)$/')]
    #[When('/^they complete addressing step with email "([^"]+)" and ("[^"]+" based billing address)$/')]
    public function i_complete_addressing_step_with_email(string $email, Address_Interface $address): void
    {
        $this->address_page->open();
        $this->i_specify_the_email($email);
        $this->i_specify_the_billing_address_as($address);
        $this->address_page->next_step();
    }
    #[When('/^I complete addressing step with ("[^"]+" based billing address)$/')]
    public function i_complete_addressing_step_with_based_billing_address(Address_Interface $address): void
    {
        $this->address_page->open();
        $this->i_specify_the_billing_address_as($address);
        $this->address_page->next_step();
    }
    #[When('I specify the province name manually as :provinceName for shipping address')]
    public function i_specify_the_province_name_manually_as_for_shipping_address(string $province_name): void
    {
        $this->address_page->specify_shipping_address_province($province_name);
    }
    #[When('I specify the province name manually as :provinceName for billing address')]
    public function i_specify_the_province_name_manually_as_for_billing_address(string $province_name): void
    {
        $this->address_page->specify_billing_address_province($province_name);
    }
    #[When('I try to open checkout addressing page')]
    public function i_try_to_open_checkout_addressing_page(): void
    {
        $this->address_page->try_to_open();
    }
    #[When('/^I choose ("[^"]+" street) for shipping address$/')]
    public function i_choose_for_shipping_address(Address_Interface $address): void
    {
        $this->address_page->choose_different_shipping_address();
        $this->address_page->select_shipping_address_from_address_book($address);
    }
    #[When('/^I choose ("[^"]+" street) for billing address$/')]
    public function i_choose_for_billing_address(Address_Interface $address): void
    {
        $this->address_page->select_billing_address_from_address_book($address);
    }
    #[When('/^I specify the shipping (address as "[^"]+", "[^"]+", "[^"]+", "[^"]+" for "[^"]+")$/')]
    #[When('/^I specify the shipping (address for "[^"]+" from "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+")$/')]
    #[When('/^I change the shipping (address to "[^"]+", "[^"]+", "[^"]+", "[^"]+" for "[^"]+")$/')]
    public function i_specify_the_shipping_address_as(Address_Interface $address): void
    {
        $this->address_page->choose_different_shipping_address();
        $key = sprintf('shipping_address_%s_%s', strtolower((string) $address->get_first_name()), strtolower((string) $address->get_last_name()));
        $this->shared_storage->set($key, $address);
        $this->address_page->specify_shipping_address($address);
    }
    #[When('/^I (do not specify any shipping address) information$/')]
    public function i_do_not_specify_any_shipping_address_information(): void
    {
        $this->address_page->choose_different_shipping_address();
    }
    #[When('/^I specify the required shipping (address as "[^"]+", "[^"]+", "[^"]+", "[^"]+" for "[^"]+")$/')]
    public function i_specify_the_required_shipping_address_as(Address_Interface $address): void
    {
        $key = sprintf('shipping_address_%s_%s', strtolower((string) $address->get_first_name()), strtolower((string) $address->get_last_name()));
        $this->shared_storage->set($key, $address);
        $this->shared_storage->set(str_replace('shipping', 'billing', $key), $address);
        $this->address_page->specify_shipping_address($address);
    }
    #[When('I specify shipping country province as :provinceName')]
    public function i_specify_shipping_country_province_as(string $province_name): void
    {
        $this->address_page->select_shipping_address_province($province_name);
    }
    #[When('I specify billing country province as :provinceName')]
    public function i_specify_billing_country_province_as(string $province_name): void
    {
        $this->address_page->select_billing_address_province($province_name);
    }
    #[Given('/^the customer specify the billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[Given('the customer specify the billing address')]
    #[Given('the visitor specify the billing address')]
    #[Given('/^the visitor specify the billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[Given('/^the visitor has specified (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[Given('/^the customer has specified (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[When('/^I specify the billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[When('/^I specify the billing (address for "([^"]+)" from "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)")$/')]
    #[When('/^I (do not specify any billing address) information$/')]
    public function i_specify_the_billing_address_as(?Address_Interface $address = null): void
    {
        if (!$this->address_page->is_open()) {
            $this->address_page->open();
        }
        if ($address === null) {
            $address = $this->address_factory->create_default();
        }
        $billing_key = sprintf('billing_address_%s_%s', strtolower((string) $address->get_first_name()), strtolower((string) $address->get_last_name()));
        $shipping_key = sprintf('shipping_address_%s_%s', strtolower((string) $address->get_first_name()), strtolower((string) $address->get_last_name()));
        $this->shared_storage->set($billing_key, $address);
        $this->shared_storage->set($shipping_key, $address);
        $this->address_page->specify_billing_address($address);
    }
    #[When('/^I specify different billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    public function i_specify_different_billing_address_as(Address_Interface $address): void
    {
        $this->address_page->choose_different_billing_address();
        $this->i_specify_the_billing_address_as($address);
    }
    #[When('I specified the billing address')]
    #[When('/^I specified the billing (address as "[^"]+", "[^"]+", "[^"]+", "[^"]+" for "[^"]+")$/')]
    #[When('/^I define the billing (address as "[^"]+", "[^"]+", "[^"]+", "[^"]+" for "[^"]+")$/')]
    public function i_specified_the_billing_address(?Address_Interface $address = null): void
    {
        if (null === $address) {
            $address = $this->address_factory->create_default();
        }
        if (!$this->address_page->is_open()) {
            $this->address_page->open();
        }
        $this->address_page->specify_billing_address($address);
        $billing_key = sprintf('billing_address_%s_%s', strtolower((string) $address->get_first_name()), strtolower((string) $address->get_last_name()));
        $shipping_key = sprintf('shipping_address_%s_%s', strtolower((string) $address->get_first_name()), strtolower((string) $address->get_last_name()));
        $this->shared_storage->set($billing_key, $address);
        $this->shared_storage->set($shipping_key, $address);
        $this->address_page->next_step();
    }
    #[When('I specify the email as :email')]
    #[When('I do not specify the email')]
    public function i_specify_the_email(?string $email = null): void
    {
        $this->address_page->specify_email($email);
    }
    #[Given('the visitor has specified the email as :email')]
    #[Given('the customer has specified the email as :email')]
    #[When('the visitor specify the email as :email')]
    public function the_visitor_specify_the_email(?string $email = null): void
    {
        $this->address_page->open();
        $this->address_page->specify_email($email);
    }
    #[When('I specify the first and last name as :fullName for billing address')]
    public function i_specify_the_first_and_last_name_as_for_billing_address(string $full_name): void
    {
        $this->address_page->specify_billing_address_full_name($full_name);
    }
    #[When('I complete the addressing step')]
    #[When('I try to complete the addressing step')]
    public function i_complete_the_addressing_step(): void
    {
        if (!$this->address_page->is_open()) {
            throw new Unexpected_Page_Exception('Addressing page should be open, but it is not.');
        }
        $this->address_page->next_step();
    }
    #[When('I go back to store')]
    public function i_go_back_to_store(): void
    {
        $this->address_page->back_to_store();
    }
    #[When('/^I proceed selecting ("[^"]+" as billing country)$/')]
    #[When('/^I proceed with selecting ("[^"]+" as billing country)$/')]
    #[When('/^I proceed with selecting billing country$/')]
    public function i_proceed_selecting_billing_country(?Country_Interface $shipping_country = null, string $locale_code = 'en_US', ?string $email = null): void
    {
        $this->address_page->open(['_locale' => $locale_code]);
        $shipping_address = $this->address_factory->create_default();
        if (null !== $shipping_country) {
            $shipping_address->set_country_code($shipping_country->get_code());
        }
        if (null !== $email) {
            $this->address_page->specify_email($email);
        }
        $this->address_page->specify_billing_address($shipping_address);
        $this->address_page->next_step();
    }
    #[When('/^I proceed as guest "([^"]*)" with ("[^"]+" as billing country)$/')]
    public function i_proceed_logging_as_guest_with_as_billing_country(string $email, ?Country_Interface $shipping_country = null): void
    {
        $this->address_page->open();
        $this->address_page->specify_email($email);
        $shipping_address = $this->address_factory->create_default();
        if (null !== $shipping_country) {
            $shipping_address->set_country_code($shipping_country->get_code());
        }
        $this->address_page->specify_billing_address($shipping_address);
        $this->address_page->next_step();
    }
    #[When('I specify the password as :password')]
    public function i_specify_the_password_as(string $password): void
    {
        $this->address_page->specify_password($password);
    }
    #[Then('I should be making an order as :purchaserIdentifier')]
    public function i_should_see_in_checkout_header(string $purchaser_identifier): void
    {
        Assert::contains($this->select_shipping_page->get_purchaser_identifier(), $purchaser_identifier);
    }
    #[When('I sign in')]
    public function i_sign_in(): void
    {
        $this->address_page->sign_in();
    }
    #[Then('I should have :countryName selected as country')]
    public function i_should_have_selected_as_country(string $country_name): void
    {
        Assert::same($this->address_page->get_billing_address_country(), $country_name);
    }
    #[Then('I should have no country selected')]
    public function i_should_have_no_country_selected(): void
    {
        Assert::same($this->address_page->get_billing_address_country(), 'Select');
    }
    #[Then('I should be able to log in')]
    public function i_should_be_able_to_log_in(): void
    {
        Assert::true($this->address_page->can_sign_in());
    }
    #[Then('the login form should no longer be accessible')]
    public function the_login_form_should_no_longer_be_accessible(): void
    {
        Assert::false($this->address_page->can_sign_in());
    }
    #[Then('I should be notified about bad credentials')]
    public function i_should_be_notified_about_bad_credentials(): void
    {
        Assert::true($this->address_page->check_invalid_credentials_validation());
    }
    #[Then('I should be notified to resubmit the addressing form')]
    public function i_should_be_notified_to_resubmit_the_addressing_form(): void
    {
        Assert::true($this->address_page->check_form_validation_message('Please resubmit complete form.'), 'Unable to find "Please resubmit complete form." validation message');
    }
    #[Then('I should not be notified that the form contains extra fields')]
    public function i_should_not_be_notified_the_form_contains_extra_fields(): void
    {
        Assert::false($this->address_page->check_form_validation_message('This form should not contain extra fields.'), 'Found "This form should not contains extra fields." validation message');
    }
    #[Then('I should be redirected to the addressing step')]
    #[Then('I should be on the checkout addressing step')]
    public function i_should_be_redirected_to_the_addressing_step(): void
    {
        $this->address_page->verify();
    }
    #[Then('I should be able to go to the shipping step again')]
    public function i_should_be_able_to_go_to_the_shipping_step_again(): void
    {
        $this->address_page->next_step();
        $this->select_shipping_page->verify();
    }
    #[Then('I should not be able to specify province name manually for shipping address')]
    public function i_should_not_be_able_to_specify_province_name_manually_for_shipping_address(): void
    {
        Assert::false($this->address_page->has_shipping_address_input());
    }
    #[Then('I should not be able to specify province name manually for billing address')]
    public function i_should_not_be_able_to_specify_province_name_manually_for_billing_address(): void
    {
        Assert::false($this->address_page->has_billing_address_input());
    }
    #[Then('/^(address "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+") should be filled as shipping address$/')]
    public function address_should_be_filled_as_shipping_address(Address_Interface $address): void
    {
        $this->test_helper->wait_until_assertion_passes(function () use ($address): void {
            Assert::true($this->address_comparator->equal($address, $this->address_page->get_pre_filled_shipping_address()));
        });
    }
    #[Then('/^(address "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+") should be filled as billing address$/')]
    public function address_should_be_filled_as_billing_address(Address_Interface $address): void
    {
        $this->test_helper->wait_until_assertion_passes(function () use ($address): void {
            Assert::true($this->address_comparator->equal($address, $this->address_page->get_pre_filled_billing_address()));
        });
    }
    #[Then('different shipping address should be checked')]
    public function different_shipping_address_should_be_checked(): void
    {
        Assert::true($this->address_page->is_different_shipping_address_checked());
    }
    #[Then('different shipping address should not be checked')]
    public function different_shipping_address_should_not_be_checked(): void
    {
        Assert::false($this->address_page->is_different_shipping_address_checked());
    }
    #[Then('shipping address should be visible')]
    public function shipping_address_should_be_visible(): void
    {
        Assert::true($this->address_page->is_shipping_address_visible());
    }
    #[Then('shipping address should not be visible')]
    public function shipping_address_should_not_be_visible(): void
    {
        Assert::false($this->address_page->is_shipping_address_visible());
    }
    #[Then('/^I should(?:| also) be notified that the "([^"]+)" and the "([^"]+)" in (shipping|billing) details are required$/')]
    public function i_should_be_notified_that_the_and_the_in_shipping_details_are_required($first_element, $second_element, $type): void
    {
        $this->assert_element_validation_message($type, $first_element, sprintf('Please enter %s.', $first_element));
        $this->assert_element_validation_message($type, $second_element, sprintf('Please enter %s.', $second_element));
    }
    #[Then('/^I should(?:| also) be notified that the "([^"]+)" in (shipping|billing) details is required$/')]
    public function i_should_be_notified_that_the_in_shipping_details_is_required(string $element, string $type): void
    {
        $this->assert_element_validation_message($type, $element, sprintf('Please enter %s.', $element));
    }
    #[Then('I should have only :firstCountry country available to choose from')]
    #[Then('I should have both :firstCountry and :secondCountry countries available to choose from')]
    public function should_have_countries_to_choose_from(string ...$countries): void
    {
        $available_billing_countries = $this->address_page->get_available_billing_countries();
        sort($countries);
        sort($available_billing_countries);
        Assert::same($available_billing_countries, $countries);
    }
    #[Then('I should be able to update the address without unexpected alert')]
    public function i_should_be_able_to_update_the_address_without_unexpected_alert(): void
    {
        $this->address_page->wait_for_form_to_stop_loading();
    }
    #[Then('the customer should have checkout address step completed')]
    #[Then('the visitor should have checkout address step completed')]
    public function the_customer_should_have_checkout_address_step_completed(): void
    {
        Assert::false($this->address_page->is_open(), 'Customer should have checkout address step completed, but it is not.');
    }
    /**
     * @param string $element
     *
     * @throws \InvalidArgumentException
     */
    private function assert_element_validation_message(string $type, $element, string $expected_message): void
    {
        $element = sprintf('%s_%s', $type, str_replace(' ', '_', $element));
        Assert::true($this->address_page->check_validation_message_for($element, $expected_message));
    }
}
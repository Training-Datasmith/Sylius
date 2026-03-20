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
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Core\Model\Tax_Rate_Interface;
use Sylius\Component\Taxation\Model\Tax_Category_Interface;
use Webmozart\Assert\Assert;
class Managing_Tax_Rates_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I want to create a new tax rate')]
    public function i_want_to_create_a_new_tax_rate(): void
    {
        $this->client->build_create_request(Resources::TAX_RATES);
    }
    #[When('I specify its code as :code')]
    public function i_specify_its_code_as(string $code): void
    {
        $this->client->add_request_data('code', $code);
    }
    #[When('I name it :name')]
    #[When('I rename it to :name')]
    public function i_name_it(string $name): void
    {
        $this->client->add_request_data('name', $name);
    }
    #[When('I define it for the :zone zone')]
    #[When('I change its zone to :zone')]
    public function i_define_it_for_the_zone(Zone_Interface $zone): void
    {
        $this->client->add_request_data('zone', $this->iri_converter->get_iri_from_resource($zone));
    }
    #[When('I make it applicable for the :taxCategory tax category')]
    #[When('I change it to be applicable for the :taxCategory tax category')]
    public function i_make_it_applicable_for_the_tax_category(Tax_Category_Interface $tax_category): void
    {
        $this->client->add_request_data('category', $this->iri_converter->get_iri_from_resource($tax_category));
    }
    #[When('I specify its amount as :amount%')]
    public function i_specify_its_amount_as(string $amount): void
    {
        $this->client->add_request_data('amount', $amount);
    }
    #[When('I do not specify related tax category')]
    #[When('I do not specify its zone')]
    #[When('I do not name it')]
    #[When('I do not specify its code')]
    public function i_do_not_specify_its_field(): void
    {
        // Intentionally left blank
    }
    #[When('I choose the default tax calculator')]
    public function i_choose_the_default_tax_calculator(): void
    {
        $this->client->add_request_data('calculator', 'default');
    }
    #[When('I make it start at :startDate and end at :endDate')]
    public function i_make_it_start_at_and_end_at(string $start_date, string $end_date): void
    {
        $this->client->add_request_data('startDate', $start_date);
        $this->client->add_request_data('endDate', $end_date);
    }
    #[When('I set the start date to :startDate')]
    public function i_set_the_start_date_to(string $start_date): void
    {
        $this->client->add_request_data('startDate', $start_date);
    }
    #[When('I set the end date to :endDate')]
    public function i_set_the_end_date_to(string $end_date): void
    {
        $this->client->add_request_data('endDate', $end_date);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I choose "Included in price" option')]
    public function i_choose_option(): void
    {
        $this->client->add_request_data('includedInPrice', true);
    }
    #[When('/^I want to modify (this tax rate)$/')]
    #[When('I want to modify a tax rate :taxRate')]
    public function i_want_to_modify_this_tax_rate(Tax_Rate_Interface $tax_rate): void
    {
        $this->client->build_update_request(Resources::TAX_RATES, (string) $tax_rate->get_code());
        $this->client->add_request_data('amount', (string) $tax_rate->get_amount());
    }
    #[When('I browse tax rates')]
    public function i_browse_tax_rates(): void
    {
        $this->client->index(Resources::TAX_RATES);
    }
    #[When('I remove its name')]
    public function i_remove_its_name(): void
    {
        $this->client->add_request_data('name', '');
    }
    #[When('I filter tax rates by start date from :startDate')]
    public function i_filter_tax_rates_by_start_date_from(string $start_date): void
    {
        $this->client->add_filter('startDate[after]', $start_date);
        $this->client->filter();
    }
    #[When('I filter tax rates by start date up to :startDate')]
    public function i_filter_tax_rates_by_start_date_up_to(string $start_date): void
    {
        $this->client->add_filter('startDate[before]', $start_date);
        $this->client->filter();
    }
    #[When('I filter tax rates by start date from :startDate up to :endDate')]
    public function i_filter_tax_rates_by_start_date_from_up_to(string $start_date, string $end_date): void
    {
        $this->client->add_filter('startDate[after]', $start_date);
        $this->client->add_filter('startDate[before]', $end_date);
        $this->client->filter();
    }
    #[When('I filter tax rates by end date from :endDate')]
    public function i_filter_tax_rates_by_end_date_from(string $end_date): void
    {
        $this->client->add_filter('endDate[after]', $end_date);
        $this->client->filter();
    }
    #[When('I filter tax rates by end date up to :endDate')]
    public function i_filter_tax_rates_by_end_date_up_to(string $end_date): void
    {
        $this->client->add_filter('endDate[before]', $end_date);
        $this->client->filter();
    }
    #[When('I filter tax rates by end date from :startDate up to :endDate')]
    public function i_filter_tax_rates_by_end_date_from_up_to(string $start_date, string $end_date): void
    {
        $this->client->add_filter('endDate[after]', $start_date);
        $this->client->add_filter('endDate[before]', $end_date);
        $this->client->filter();
    }
    #[When('I delete tax rate :taxRate')]
    public function i_delete_tax_rate(Tax_Rate_Interface $tax_rate): void
    {
        $this->client->delete(Resources::TAX_RATES, (string) $tax_rate->get_code());
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Tax tax rate could not be created');
    }
    #[Then('the tax rate :taxRate should appear in the registry')]
    #[Then('I should see the tax rate :taxRate in the list')]
    public function the_tax_rate_should_appear_in_the_registry(Tax_Rate_Interface $tax_rate): void
    {
        $this->shared_storage->set('tax_rate', $tax_rate);
        $name = $tax_rate->get_name();
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::TAX_RATES), 'name', $name), sprintf('Tax rate with name %s does not exist', $name));
    }
    #[Then('the tax rate :taxRate should be included in price')]
    public function the_tax_rate_should_include_price(Tax_Rate_Interface $tax_rate): void
    {
        Assert::true($tax_rate->is_included_in_price(), sprintf('Tax rate is not included in price'));
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Tax rate could not be deleted');
    }
    #[Then('/^(this tax rate) should no longer exist in the registry$/')]
    public function this_tax_rate_should_no_longer_exist_in_the_registry(Tax_Rate_Interface $tax_rate): void
    {
        $name = $tax_rate->get_name();
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::TAX_RATES), 'name', $name), sprintf('Tax rate with name %s exists', $name));
    }
    #[Then('I should see a single tax rate in the list')]
    public function i_should_see_a_single_tax_rate_in_the_list(): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->index(Resources::TAX_RATES)), 1);
    }
    #[Then('I should be notified that tax rate with this code already exists')]
    public function i_should_be_notified_that_tax_rate_with_this_code_already_exists(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Tax rate has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: The tax rate with given code already exists.');
    }
    #[Then('there should still be only one tax rate with code :code')]
    public function there_should_still_be_only_one_tax_rate_with_code(string $code): void
    {
        Assert::count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::TAX_RATES), 'code', $code), 1, sprintf('There is more than one tax rate with code %s', $code));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s: Please enter tax rate %s.', $element, $element));
    }
    #[Then('tax rate with :element :code should not be added')]
    public function tax_rate_with_code_should_not_be_added(string $element, string $code): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::TAX_RATES), $element, $code), sprintf('Tax rate with %s %s exist', $element, $code));
    }
    #[Then('I should be notified that zone has to be selected')]
    public function i_should_be_notified_that_zone_has_to_be_selected(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'zone: Please select tax zone.');
    }
    #[Then('I should be notified that category has to be selected')]
    public function i_should_be_notified_that_category_has_to_be_selected(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'category: Please select tax category.');
    }
    #[Then('I should not see a tax rate with name :name')]
    public function i_should_not_see_a_tax_rate_with_name(string $name): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->get_last_response(), 'name', $name), sprintf('Tax rate with name %s exists', $name));
    }
    #[Then('/^(this tax rate) should still be named "([^"]+)"$/')]
    #[Then('/^(this tax rate) name should be "([^"]*)"$/')]
    public function this_tax_rate_should_still_be_named(Tax_Rate_Interface $tax_rate, string $tax_rate_name): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::TAX_RATES, (string) $tax_rate->get_code()), 'name', $tax_rate_name), sprintf('Tax rate name is not %s', $tax_rate_name));
    }
    #[Then('the code field should be disabled')]
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->update_request_data(['code' => 'NEW_CODE']);
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'));
    }
    #[Then('/^(this tax rate) amount should be ([^"]+)%$/')]
    public function this_tax_rate_amount_should_be(Tax_Rate_Interface $tax_rate, int $tax_rate_amount): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::TAX_RATES, (string) $tax_rate->get_code()), 'amount', $tax_rate_amount), sprintf('Tax rate amount is not %s', $tax_rate_amount));
    }
    #[Then('/^(this tax rate) should be applicable for the ("[^"]+" tax category)$/')]
    public function this_tax_rate_should_be_applicable_for_the_tax_category(Tax_Rate_Interface $tax_rate, Tax_Category_Interface $tax_category): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::TAX_RATES, (string) $tax_rate->get_code()), 'category', $this->iri_converter->get_iri_from_resource($tax_category)), sprintf('Tax rate is not applicable for %s tax category', $tax_category));
    }
    #[Then('/^(this tax rate) should be applicable in ("[^"]+" zone)$/')]
    public function this_tax_rate_should_be_applicable_in_zone(Tax_Rate_Interface $tax_rate, Zone_Interface $zone): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::TAX_RATES, (string) $tax_rate->get_code()), 'zone', $this->iri_converter->get_iri_from_resource($zone)), sprintf('Tax rate is not applicable for %s zone', $zone));
    }
    #[Then('I should be notified that amount is invalid')]
    public function i_should_be_notified_that_amount_is_invalid(): void
    {
        Assert::true($this->response_checker->has_violation_with_message($this->client->get_last_response(), 'The tax rate amount is invalid.', 'amount'));
    }
    #[Then('I should be notified that tax rate should not end before it starts')]
    public function i_should_be_notified_that_tax_rate_should_not_end_before_it_starts(): void
    {
        Assert::true($this->response_checker->has_violation_with_message($this->client->get_last_response(), 'The tax rate should not end before it starts', 'endDate'));
    }
}
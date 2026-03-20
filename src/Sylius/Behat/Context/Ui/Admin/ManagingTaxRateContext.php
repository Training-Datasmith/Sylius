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
use Sylius\Behat\Element\Admin\Tax_Rate\Filter_Element_Interface;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Tax_Rate\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Tax_Rate\Update_Page_Interface;
use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver_Interface;
use Sylius\Component\Core\Model\Tax_Rate_Interface;
use Webmozart\Assert\Assert;
final class Managing_Tax_Rate_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Index_Page_Interface $index_page, private Create_Page_Interface $create_page, private Update_Page_Interface $update_page, private Current_Page_Resolver_Interface $current_page_resolver, private Filter_Element_Interface $filter_element)
    {
    }
    #[When('I want to create a new tax rate')]
    public function i_want_to_create_new_tax_rate(): void
    {
        $this->create_page->open();
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->create_page->specify_code($code ?? '');
    }
    #[When('/^I specify its amount as ([^"]+)%$/')]
    #[When('I do not specify its amount')]
    #[When('I remove its amount')]
    public function i_specify_its_amount_as($amount = null): void
    {
        $this->create_page->specify_amount($amount ?? '');
    }
    #[When('I make it start at :startDate and end at :endDate')]
    public function i_make_it_start_at_and_end_at(string $start_date, string $end_date): void
    {
        $this->create_page->specify_start_date(new \DateTime($start_date));
        $this->create_page->specify_end_date(new \DateTime($end_date));
    }
    #[When('I set the start date to :startDate')]
    public function i_set_the_start_date_to(string $start_date): void
    {
        $this->create_page->specify_start_date(new \DateTime($start_date));
    }
    #[When('I set the end date to :endDate')]
    public function i_set_the_end_date_to(string $end_date): void
    {
        $this->create_page->specify_start_date(new \DateTime($end_date));
    }
    #[When('I define it for the :zoneName zone')]
    #[When('I change its zone to :zoneName')]
    public function i_define_it_for_the_zone(string $zone_name): void
    {
        $this->create_page->choose_zone($zone_name);
    }
    #[When('I make it applicable for the :taxCategoryName tax category')]
    #[When('I change it to be applicable for the :taxCategoryName tax category')]
    public function i_make_it_applicable_for_the_tax_category(string $tax_category_name): void
    {
        $this->create_page->choose_category($tax_category_name);
    }
    #[When('I choose the default tax calculator')]
    public function i_want_to_use_the_default_tax_calculator(): void
    {
        $this->create_page->choose_calculator('default');
    }
    #[When('I name it :name')]
    #[When('I rename it to :name')]
    #[When('I do not name it')]
    #[When('I remove its name')]
    public function i_name_it($name = null): void
    {
        $this->create_page->name_it($name ?? '');
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[Then('I should see the tax rate :taxRateName in the list')]
    #[Then('the tax rate :taxRateName should appear in the registry')]
    public function the_tax_rate_should_appear_in_the_registry(string $tax_rate_name): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $tax_rate_name]));
    }
    #[Then('the tax rate :taxRate should be included in price')]
    public function the_tax_rate_should_include_price(Tax_Rate_Interface $tax_rate): void
    {
        $this->update_page->open(['id' => $tax_rate->get_id()]);
        Assert::true($tax_rate->is_included_in_price(), sprintf('Tax rate is not included in price'));
    }
    #[Then('I should not see a tax rate with name :name')]
    public function i_should_not_see_a_tax_rate_with_name(string $tax_rate_name): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['name' => $tax_rate_name]), sprintf('Tax rate with name "%s" has been found, but should not.', $tax_rate_name));
    }
    #[When('I delete tax rate :taxRate')]
    public function i_deleted_tax_rate(Tax_Rate_Interface $tax_rate): void
    {
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['name' => $tax_rate->get_name()]);
    }
    #[Then('/^(this tax rate) should no longer exist in the registry$/')]
    public function this_tax_rate_should_no_longer_exist_in_the_registry(Tax_Rate_Interface $tax_rate): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['code' => $tax_rate->get_code()]));
    }
    #[When('I want to modify a tax rate :taxRate')]
    #[When('/^I want to modify (this tax rate)$/')]
    public function i_want_to_modify_tax_rate(Tax_Rate_Interface $tax_rate): void
    {
        $this->update_page->open(['id' => $tax_rate->get_id()]);
    }
    #[Then('the code field should be disabled')]
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->update_page->is_code_disabled());
    }
    #[Then('/^(this tax rate) name should be "([^"]+)"$/')]
    #[Then('/^(this tax rate) should still be named "([^"]+)"$/')]
    public function this_tax_rate_name_should_be(Tax_Rate_Interface $tax_rate, string $tax_rate_name): void
    {
        $this->assert_field_value($tax_rate, 'name', $tax_rate_name);
    }
    #[Then('/^(this tax rate) amount should be ([^"]+)%$/')]
    #[Then('/^(this tax rate) amount should still be ([^"]+)%$/')]
    public function this_tax_rate_amount_should_be(Tax_Rate_Interface $tax_rate, string $tax_rate_amount): void
    {
        $this->assert_field_value($tax_rate, 'amount', $tax_rate_amount);
    }
    #[Then('I should be notified that tax rate with this code already exists')]
    public function i_should_be_notified_that_tax_rate_with_this_code_already_exists(): void
    {
        $this->assert_field_validation_message('code', 'The tax rate with given code already exists.');
    }
    #[Then('there should still be only one tax rate with :element :code')]
    public function there_should_still_be_only_one_tax_rate_with($element, $code): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page([$element => $code]));
    }
    #[Then('/^(this tax rate) should be applicable for the "([^"]+)" tax category$/')]
    public function this_tax_rate_should_be_applicable_for_tax_category(Tax_Rate_Interface $tax_rate, string $tax_category): void
    {
        $this->assert_field_value($tax_rate, 'category', $tax_category);
    }
    #[Then('/^(this tax rate) should be applicable in "([^"]+)" zone$/')]
    public function this_tax_rate_should_be_applicable_in_zone(Tax_Rate_Interface $tax_rate, string $zone): void
    {
        $this->assert_field_value($tax_rate, 'zone', $zone);
    }
    #[Then('I should be notified that :element has to be selected')]
    public function i_should_be_notified_that_element_has_to_be_selected(string $element): void
    {
        $this->assert_field_validation_message($element, sprintf('Please select tax %s.', $element));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        $this->assert_field_validation_message($element, sprintf('Please enter tax rate %s.', $element));
    }
    #[Then('I should be notified that :element is invalid')]
    public function i_should_be_notified_that_is_invalid(string $element): void
    {
        $this->assert_field_validation_message($element, sprintf('The tax rate %s is invalid.', $element));
    }
    #[Then('tax rate with :element :name should not be added')]
    public function tax_rate_with_element_value_should_not_be_added($element, $name): void
    {
        $this->index_page->open();
        Assert::false($this->index_page->is_single_resource_on_page([$element => $name]));
    }
    #[When('I do not specify its zone')]
    public function i_do_not_specify_its_zone(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('I remove its zone')]
    public function i_remove_its_zone(): void
    {
        $this->update_page->remove_zone();
    }
    #[When('I do not specify related tax category')]
    public function i_do_not_specify_related_tax_category(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('I check (also) the :taxRateName tax rate')]
    public function i_check_the_tax_rate(string $tax_rate_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $tax_rate_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[When('I browse tax rates')]
    public function i_want_to_browse_tax_rates(): void
    {
        $this->index_page->open();
    }
    #[Then('I should see a single tax rate in the list')]
    public function i_should_see_a_single_tax_rate_in_the_list(): void
    {
        Assert::same($this->index_page->count_items(), 1);
    }
    #[Given('I choose "Included in price" option')]
    public function i_choose_option(): void
    {
        $this->create_page->choose_included_in_price();
    }
    #[Then('I should be notified that tax rate should not end before it starts')]
    public function i_should_be_notified_that_tax_rate_should_not_end_before_it_starts(): void
    {
        $this->assert_field_validation_message('end_date', 'The tax rate should not end before it starts');
    }
    #[When('/^I filter tax rates by (end|start) date from "(\d{4}-\d{2}-\d{2})"$/')]
    public function i_filter_tax_rates_by_date_from(string $date_type, string $date): void
    {
        $this->filter_element->specify_date_from($date_type, $date);
        $this->filter_element->filter();
    }
    #[When('/^I filter tax rates by (end|start) date up to "(\d{4}-\d{2}-\d{2})"$/')]
    public function i_filter_tax_rates_by_date_up_to(string $date_type, string $date): void
    {
        $this->filter_element->specify_date_to($date_type, $date);
        $this->filter_element->filter();
    }
    #[When('/^I filter tax rates by (end|start) date from "(\d{4}-\d{2}-\d{2})" up to "(\d{4}-\d{2}-\d{2})"$/')]
    public function i_filter_tax_rates_by_date_from_date_to_date(string $date_type, string $from_date, string $to_date): void
    {
        $this->filter_element->specify_date_from($date_type, $from_date);
        $this->filter_element->specify_date_to($date_type, $to_date);
        $this->filter_element->filter();
    }
    private function assert_field_value(Tax_Rate_Interface $tax_rate, string $element, string $tax_rate_element): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $tax_rate->get_code(), $element => $tax_rate_element]), sprintf('Tax rate %s %s has not been assigned properly.', $element, $tax_rate_element));
    }
    protected function resolve_current_page(): Sylius_Page_Interface
    {
        return $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
    }
    private function assert_field_validation_message(string $element, string $expected_message): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        Assert::same($current_page->get_validation_message('field_' . $element), $expected_message);
    }
}
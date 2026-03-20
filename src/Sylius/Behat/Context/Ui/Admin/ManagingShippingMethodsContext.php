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
use Sylius\Behat\Element\Admin\Shipping_Method\Form_Element_Interface;
use Sylius\Behat\Page\Admin\Shipping_Method\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Shipping_Method\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Shipping_Method\Update_Page_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Channel\Model\Channel_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Sylius\Component\Core\Shipping\Checker\Rule\Order_Total_Greater_Than_Or_Equal_Rule_Checker;
use Sylius\Component\Core\Shipping\Checker\Rule\Order_Total_Less_Than_Or_Equal_Rule_Checker;
use Sylius\Component\Shipping\Checker\Rule\Total_Weight_Greater_Than_Or_Equal_Rule_Checker;
use Sylius\Component\Shipping\Checker\Rule\Total_Weight_Less_Than_Or_Equal_Rule_Checker;
use Webmozart\Assert\Assert;
final readonly class Managing_Shipping_Methods_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page, private Create_Page_Interface $create_page, private Update_Page_Interface $update_page, private Form_Element_Interface $shipping_method_form_element, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I want to create a new shipping method')]
    public function i_want_to_create_a_new_shipping_method(): void
    {
        $this->create_page->open();
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->shipping_method_form_element->set_code($code ?? '');
    }
    #[When('I specify its position as :position')]
    public function i_specify_its_position_as(int $position): void
    {
        $this->shipping_method_form_element->set_position($position);
    }
    #[When('I name it :name in :language')]
    #[When('I rename it to :name in :language')]
    public function i_name_it_in(string $name, string $language): void
    {
        $this->shipping_method_form_element->set_name($name, $language);
    }
    #[When('I describe it as :description in :language')]
    public function i_describe_it_as_in(string $description, string $language): void
    {
        $this->shipping_method_form_element->set_description($description, $language);
    }
    #[When('I define it for the zone named :zone')]
    public function i_define_it_for_the_zone(Zone_Interface $zone): void
    {
        $this->shipping_method_form_element->set_zone_code($zone->get_code());
    }
    #[When('I make it available in channel :channel')]
    public function i_make_it_available_in_channel(Channel_Interface $channel): void
    {
        $this->shipping_method_form_element->check_channel($channel->get_code());
    }
    #[When('I specify its amount as :amount for :channel channel')]
    public function i_specify_its_amount_for_channel(int $amount, Channel_Interface $channel): void
    {
        $this->shipping_method_form_element->set_calculator_configuration_amount_for_channel($channel->get_code(), $amount);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I choose :calculatorName calculator')]
    #[When('I do not specify amount for :calculatorName calculator')]
    public function i_choose_calculator(string $calculator_name): void
    {
        $this->shipping_method_form_element->choose_calculator($calculator_name);
    }
    #[When('I fill in :label with :value')]
    public function i_fill_in_with(string $label, string $value): void
    {
        $this->shipping_method_form_element->set_field($label, $value);
    }
    #[When('I check (also) the :shippingMethodName shipping method')]
    public function i_check_the_shipping_method(string $shipping_method_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $shipping_method_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[Then('I should see the shipping method :shipmentMethodName in the list')]
    #[Then('the shipping method :shipmentMethodName should appear in the registry')]
    #[Then('the shipping method :shipmentMethodName should be in the registry')]
    public function the_shipment_method_should_appear_in_the_registry(string $shipment_method_name): void
    {
        $this->i_want_to_browse_shipping_methods();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $shipment_method_name]));
    }
    #[Then('the shipping method :shipmentMethodName should not appear in the registry')]
    public function the_shipment_method_should_not_appear_in_the_registry(string $shipment_method_name): void
    {
        $this->i_want_to_browse_shipping_methods();
        Assert::false($this->index_page->is_single_resource_on_page(['name' => $shipment_method_name]));
    }
    #[Given('/^(this shipping method) should still be in the registry$/')]
    public function this_shipping_method_should_still_be_in_the_registry(Shipping_Method_Interface $shipping_method): void
    {
        $this->the_shipment_method_should_appear_in_the_registry($shipping_method->get_name());
    }
    #[Then('the shipping method :shippingMethod should be available in channel :channel')]
    public function the_shipping_method_should_be_available_in_channel(Shipping_Method_Interface $shipping_method, Channel_Interface $channel): void
    {
        $this->i_want_to_modify_a_shipping_method($shipping_method);
        Assert::true($this->shipping_method_form_element->has_checked_channel($channel->get_code()), sprintf('Shipping method %s should be available in channel %s, but it is not.', $shipping_method->get_name(), $channel->get_code()));
    }
    #[Then('I should be notified that shipping method with this code already exists')]
    public function i_should_be_notified_that_shipping_method_with_this_code_already_exists(): void
    {
        Assert::same($this->shipping_method_form_element->get_validation_message('code'), 'The shipping method with given code already exists.');
    }
    #[Then('there should still be only one shipping method with :element :code')]
    public function there_should_still_be_only_one_shipping_method_with($element, $code): void
    {
        $this->i_want_to_browse_shipping_methods();
        Assert::true($this->index_page->is_single_resource_on_page([$element => $code]));
    }
    #[When('I want to modify a shipping method :shippingMethod')]
    #[When('/^I want to modify (this shipping method)$/')]
    public function i_want_to_modify_a_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $this->update_page->open(['id' => $shipping_method->get_id()]);
    }
    #[Then('I should not be able to edit its code')]
    public function the_code_field_should_be_disabled(): void
    {
        Assert::true($this->shipping_method_form_element->is_code_disabled());
    }
    #[Then('/^(this shipping method) name should be "([^"]+)"$/')]
    #[Then('/^(this shipping method) should still be named "([^"]+)"$/')]
    public function this_shipping_method_name_should_be(Shipping_Method_Interface $shipping_method, $shipping_method_name): void
    {
        $this->i_want_to_browse_shipping_methods();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $shipping_method->get_code(), 'name' => $shipping_method_name]));
    }
    #[Then('/^I should be notified that (code) is required$/')]
    public function i_should_be_notified_that_code_is_required(string $field): void
    {
        Assert::same($this->shipping_method_form_element->get_validation_message($field), sprintf('Please enter shipping method %s.', $field));
    }
    #[Then('I should be notified that name is required')]
    public function i_should_be_notified_that_name_is_required($locale_code = 'en_US'): void
    {
        Assert::same($this->shipping_method_form_element->get_validation_message('name', ['%localeCode%' => $locale_code]), 'Please enter shipping method name.');
    }
    #[Then('I should be notified that code needs to contain only specific symbols')]
    public function i_should_be_notified_that_code_needs_to_contain_only_specific_symbols(): void
    {
        $this->assert_field_validation_message('code', 'Shipping method code can only be comprised of letters, numbers, dashes and underscores.');
    }
    #[When('I archive the :name shipping method')]
    public function i_archive_the_shipping_method(string $name): void
    {
        $this->index_page->archive_shipping_method($name);
    }
    #[When('I restore the :name shipping method')]
    public function i_restore_the_shipping_method(string $name): void
    {
        $this->index_page->restore_shipping_method($name);
    }
    #[Then('I should be viewing non archival shipping methods')]
    public function i_should_be_viewing_non_archival_shipping_methods(): void
    {
        Assert::false($this->index_page->is_archival_filter_enabled());
    }
    #[Then('I should see a single shipping method in the list')]
    #[Then('I should see :numberOfShippingMethods shipping methods in the list')]
    #[Then('I should see :numberOfShippingMethods shipping methods on the list')]
    public function there_should_be_no_shipping_methods_on_the_list(int $number_of_shipping_methods = 1): void
    {
        Assert::same($this->index_page->count_items(), $number_of_shipping_methods);
    }
    #[Then('the only shipping method on the list should be :name')]
    public function the_only_shipping_method_on_the_list_should_be($name): void
    {
        Assert::same($this->index_page->count_items(), 1);
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $name]));
    }
    #[Then('shipping method with :element :name should not be added')]
    public function shipping_method_with_element_value_should_not_be_added($element, $name): void
    {
        $this->i_want_to_browse_shipping_methods();
        Assert::false($this->index_page->is_single_resource_on_page([$element => $name]));
    }
    #[When('I do not name it')]
    public function i_do_not_name_it(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('I do not specify its zone')]
    public function i_do_not_specify_its_zone(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('I remove its zone')]
    public function i_remove_its_zone(): void
    {
        $this->shipping_method_form_element->set_zone_code('');
    }
    #[Then('I should be notified that :element has to be selected')]
    #[Then('I should be notified that the :element is required')]
    public function i_should_be_notified_that_element_has_to_be_selected(string $element): void
    {
        $this->assert_field_validation_message($element, sprintf('Please select shipping method %s.', $element));
    }
    #[When('I remove its name from :language translation')]
    public function i_remove_its_name_from_translation(string $language): void
    {
        $this->shipping_method_form_element->set_name('', $language);
    }
    #[Given('I am browsing shipping methods')]
    #[When('I browse shipping methods')]
    #[When('I want to browse shipping methods')]
    public function i_want_to_browse_shipping_methods(): void
    {
        $this->index_page->open();
    }
    #[Given('I am browsing archival shipping methods')]
    public function i_am_browsing_archival_shipping_methods(): void
    {
        $this->index_page->open();
        $this->index_page->choose_archival('Yes');
        $this->index_page->filter();
    }
    #[Given('I filter archival shipping methods')]
    public function i_filter_archival_shipping_methods(): void
    {
        $this->index_page->choose_archival('Yes');
        $this->index_page->filter();
    }
    #[Then('the first shipping method on the list should have :field :value')]
    public function the_first_shipping_method_on_the_list_should_have(string $field, $value): void
    {
        $fields = $this->index_page->get_column_fields($field);
        Assert::same(reset($fields), $value);
    }
    #[Then('the last shipping method on the list should have :field :value')]
    public function the_last_shipping_method_on_the_list_should_have(string $field, $value): void
    {
        $fields = $this->index_page->get_column_fields($field);
        Assert::same(end($fields), $value);
    }
    #[Given('the shipping methods are already sorted :sortType by :field')]
    #[When('I switch the way shipping methods are sorted :sortType by :field')]
    #[When('I sort the shipping methods :sortType by :field')]
    public function i_sort_shipping_methods_by(string $sort_type, string $field): void
    {
        $this->index_page->sort_by($field);
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->shipping_method_form_element->enable();
    }
    #[When('I disable it')]
    public function i_disable_it(): void
    {
        $this->shipping_method_form_element->disable();
    }
    #[When('I specify a too long :field')]
    public function i_specify_a_too_long(string $field): void
    {
        $this->shipping_method_form_element->set_field(ucwords($field), str_repeat('a', 256));
    }
    #[Then('/^(this shipping method) should be disabled$/')]
    public function this_shipping_method_should_be_disabled(Shipping_Method_Interface $shipping_method): void
    {
        Assert::true($this->index_page->is_shipping_method_disabled($shipping_method));
    }
    #[Then('/^(this shipping method) should be enabled$/')]
    public function this_shipping_method_should_be_enabled(Shipping_Method_Interface $shipping_method): void
    {
        Assert::true($this->index_page->is_shipping_method_enabled($shipping_method));
    }
    #[When('I delete shipping method :shippingMethod')]
    #[When('I try to delete shipping method :shippingMethod')]
    public function i_delete_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['name' => $shipping_method->get_name()]);
    }
    #[Then('/^(this shipping method) should no longer exist in the registry$/')]
    public function this_shipping_method_should_no_longer_exist_in_the_registry(Shipping_Method_Interface $shipping_method): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['code' => $shipping_method->get_code()]));
    }
    #[Then('I should be notified that amount for :channel channel should not be blank')]
    public function i_should_be_notified_that_amount_for_channel_should_not_be_blank(Channel_Interface $channel): void
    {
        Assert::same($this->shipping_method_form_element->get_validation_message('calculator_configuration_amount', ['%channelCode%' => $channel->get_code()]), 'This value should not be blank.');
    }
    #[Then('I should be notified that shipping charge for :channel channel cannot be lower than 0')]
    public function i_should_be_notified_that_shipping_charge_for_channel_cannot_be_lower_than0(Channel_Interface $channel): void
    {
        Assert::same($this->shipping_method_form_element->get_validation_message('calculator_configuration_amount', ['%channelCode%' => $channel->get_code()]), 'Shipping charge cannot be lower than 0.');
    }
    #[When('I add the "Total weight greater than or equal" rule configured with :weight')]
    public function i_add_the_total_weight_greater_than_or_equal_rule_configured_with(int $weight): void
    {
        $this->shipping_method_form_element->add_rule(Total_Weight_Greater_Than_Or_Equal_Rule_Checker::TYPE);
        $this->shipping_method_form_element->fill_last_rule_option('Weight', (string) $weight);
    }
    #[When('I add the "Total weight greater than or equal" rule configured with invalid data')]
    public function i_add_the_total_weight_greater_than_or_equal_rule_configured_with_invalid_data(): void
    {
        $this->shipping_method_form_element->add_rule(Total_Weight_Greater_Than_Or_Equal_Rule_Checker::TYPE);
        $this->shipping_method_form_element->fill_last_rule_option('Weight', 'invalid data');
    }
    #[When('I add the "Total weight less than or equal" rule configured with :weight')]
    public function i_add_the_total_weight_less_than_or_equal_rule_configured_with(int $weight): void
    {
        $this->shipping_method_form_element->add_rule(Total_Weight_Less_Than_Or_Equal_Rule_Checker::TYPE);
        $this->shipping_method_form_element->fill_last_rule_option('Weight', (string) $weight);
    }
    #[When('/^I add the "([^"]+)" rule configured with (?:€|£|\$)([^"]+) for ("[^"]+" channel)$/')]
    public function i_add_the_items_total_less_than_or_equal_rule_configured_with(string $rule, mixed $value, Channel_Interface $channel): void
    {
        $rule_types = ['Items total less than or equal' => Order_Total_Less_Than_Or_Equal_Rule_Checker::TYPE, 'Items total greater than or equal' => Order_Total_Greater_Than_Or_Equal_Rule_Checker::TYPE];
        $this->shipping_method_form_element->add_rule($rule_types[$rule]);
        $this->shipping_method_form_element->fill_last_rule_option_for_channel($channel->get_code(), 'Amount', (string) $value);
    }
    #[When('/^I add the "Items total less than or equal" rule configured with invalid data for ("[^"]+" channel)$/')]
    public function i_add_the_items_total_less_than_or_equal_rule_configured_with_invalid_data(Channel_Interface $channel): void
    {
        $this->shipping_method_form_element->add_rule(Order_Total_Less_Than_Or_Equal_Rule_Checker::TYPE);
        $this->shipping_method_form_element->fill_last_rule_option_for_channel($channel->get_code(), 'Amount', 'Invalid data');
    }
    #[When('/^I remove the shipping charges of ("[^"]+" channel)$/')]
    public function i_remove_the_shipping_charges_of_channel(Channel_Interface $channel): void
    {
        $this->shipping_method_form_element->set_calculator_configuration_amount_for_channel($channel->get_code(), null);
    }
    #[Then('/^I should see that the shipping charges for ("[^"]+" channel) has (\d+) validation errors?$/')]
    public function i_should_see_that_the_shipping_charges_for_channel_has_count_validation_errors(Channel_Interface $channel, int $count): void
    {
        Assert::same($this->shipping_method_form_element->get_shipping_charges_validation_errors_count($channel->get_code()), $count);
    }
    #[Then('I should be notified that the weight rule has an invalid configuration')]
    public function i_should_be_notified_that_the_weight_rule_has_an_invalid_configuration(): void
    {
        $this->shared_storage->get('channel');
        Assert::same($this->shipping_method_form_element->get_validation_message('last_rule_weight'), 'Please enter a number.');
    }
    #[Then('I should be notified that the amount rule has an invalid configuration in :channel channel')]
    public function i_should_be_notified_that_the_amount_rule_has_an_invalid_configuration_in_channel(Channel_Interface $channel): void
    {
        Assert::same($this->shipping_method_form_element->get_validation_message('last_rule_amount', ['%channelCode%' => $channel->get_code()]), 'Please enter a valid money amount.');
    }
    #[Then('I should be notified that :field is too long')]
    #[Then('I should be notified that :field should be no longer than :maxLength characters')]
    public function i_should_be_notified_that_field_value_is_too_long(string $field, int $max_length = 255): void
    {
        $validation_message = $this->shipping_method_form_element->get_validation_message(String_Inflector::name_to_lowercase_code($field));
        Assert::contains($validation_message, sprintf('must not be longer than %d characters.', $max_length));
    }
    #[Then('the :shippingMethod shipping method should be successfully created')]
    public function the_shipping_method_should_be_successfully_created(Shipping_Method_Interface $shipping_method): void
    {
        $this->update_page->verify(['id' => $shipping_method->get_id()]);
        $this->the_shipment_method_should_appear_in_the_registry($shipping_method->get_name());
    }
    private function assert_field_validation_message(string $element, string $expected_message): void
    {
        Assert::same($this->shipping_method_form_element->get_validation_message($element), $expected_message);
    }
    #[Then('I should be notified that Maximum delivery time must be greater than or equal to the minimum.')]
    public function i_should_be_notified_that_max_delivery_is_greater_or_equal_min(): void
    {
        $this->assert_field_validation_message('max_delivery_time_days', 'Maximum delivery time must be greater than or equal to the minimum.');
    }
}
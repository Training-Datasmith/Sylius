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
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Sylius\Component\Shipping\Calculator\Default_Calculators;
use Symfony\Component\Http_Foundation\Request as HttpRequest;
use Webmozart\Assert\Assert;
final readonly class Managing_Shipping_Methods_Context implements Context
{
    use Validation_Trait;
    public const SORT_TYPES = ['ascending' => 'asc', 'descending' => 'desc'];
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Given('I am browsing archival shipping methods')]
    public function i_am_browsing_archival_shipping_methods(): void
    {
        $this->client->index(Resources::SHIPPING_METHODS);
        $this->client->add_filter('exists[archivedAt]', true);
        $this->client->filter();
    }
    #[Given('the shipping methods are already sorted :sortType by name')]
    #[When('I sort the shipping methods :sortType by name')]
    #[When('I switch the way shipping methods are sorted :sortType by name')]
    public function i_sort_shipping_methods_by_name(string $sort_type = 'ascending'): void
    {
        $this->client->sort(['translation.name' => self::SORT_TYPES[$sort_type], 'localeCode' => $this->get_admin_locale_code()]);
    }
    #[When('I add the :rule rule configured with :weight')]
    public function i_add_the_rule_configured_with_weight(string $rule, int $weight): void
    {
        $type = String_Inflector::name_to_lowercase_code($rule);
        $this->client->add_request_data('rules', [['type' => $type, 'configuration' => ['weight' => $weight]]]);
    }
    #[When('I add the "Total weight greater than or equal" rule configured with invalid data')]
    public function i_add_the_total_weight_greater_than_or_equal_rule_configured_with_invalid_data(): void
    {
        $this->client->add_request_data('rules', [['type' => 'total_weight_greater_than_or_equal', 'configuration' => ['weight' => true]]]);
    }
    #[When('/^I add the "([^"]+)" rule configured with (?:€|£|\$)([^"]+) for ("[^"]+" channel)$/')]
    public function i_add_the_rule_configured_with_for_channel(string $rule, int $value, Channel_Interface $channel): void
    {
        match ($rule) {
            'Items total less than or equal' => $this->client->add_request_data('rules', [['type' => 'order_total_less_than_or_equal', 'configuration' => [$channel->get_code() => ['amount' => $value]]]]),
            'Items total greater than or equal' => $this->client->add_request_data('rules', [['type' => 'order_total_greater_than_or_equal', 'configuration' => [$channel->get_code() => ['amount' => $value]]]]),
            default => throw new \InvalidArgumentException('Unsupported shipping method rule'),
        };
    }
    #[When('/^I add the "Items total less than or equal" rule configured with invalid data for ("[^"]+" channel)$/')]
    public function i_add_the_items_total_less_than_or_equal_rule_configured_with_invalid_data(Channel_Interface $channel): void
    {
        $this->client->add_request_data('rules', [['type' => 'order_total_greater_than_or_equal', 'configuration' => [$channel->get_code() => ['amount' => true]]]]);
    }
    #[When('I change my locale to :localeCode')]
    public function i_switch_the_locale_to_the_locale(string $locale_code): void
    {
        /** @var AdminUserInterface $adminUser */
        $admin_user = $this->shared_storage->get('administrator');
        $this->client->build_update_request(Resources::ADMINISTRATORS, (string) $admin_user->get_id());
        $this->client->update_request_data(['localeCode' => $locale_code]);
        $this->client->update();
    }
    #[When('I am browsing shipping methods')]
    #[When('I want to browse shipping methods')]
    #[When('I try to browse shipping methods')]
    #[When('I browse shipping methods')]
    public function i_browse_shipping_methods(): void
    {
        $response = $this->client->index(Resources::SHIPPING_METHODS);
        $this->shared_storage->set('response', $response);
    }
    #[When('I (try to )delete shipping method :shippingMethod')]
    public function i_delete_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $this->client->delete(Resources::SHIPPING_METHODS, $shipping_method->get_code());
    }
    #[When('I want to create a new shipping method')]
    #[When('I try to create a new shipping method')]
    public function i_want_to_create_a_new_shipping_method(): void
    {
        $this->client->build_create_request(Resources::SHIPPING_METHODS);
    }
    #[When('I try to create a new shipping method with valid data')]
    public function i_try_to_create_a_new_shipping_method_with_valid_data(): void
    {
        $this->client->build_create_request(Resources::SHIPPING_METHODS);
        $this->client->set_request_data(['code' => 'FED_EX_CARRIER', 'position' => 0, 'translations' => ['en_US' => ['name' => 'FedEx Carrier']], 'zone' => $this->iri_converter->get_iri_from_resource($this->shared_storage->get('zone')), 'calculator' => 'Flat rate per shipment', 'configuration' => [$this->shared_storage->get('channel')->get_code() => ['amount' => 50]]]);
    }
    #[When('I do not specify amount for :calculatorName calculator')]
    public function i_do_not_specify_amount_for_calculator(string $calculator_name): void
    {
        match ($calculator_name) {
            'Flat rate per shipment' => $this->client->add_request_data('calculator', Default_Calculators::FLAT_RATE),
            'Flat rate per unit' => $this->client->add_request_data('calculator', Default_Calculators::PER_UNIT_RATE),
            'default' => throw new \InvalidArgumentException('Unsupported calculator name'),
        };
        $channel_code = $this->shared_storage->get('channel')->get_code();
        $this->client->add_request_data('configuration', [$channel_code => ['amount' => null]]);
    }
    #[When('I remove its zone')]
    public function i_remove_its_zone(): void
    {
        $this->client->replace_request_data('zone', null);
    }
    #[When('I try to show :shippingMethod shipping method')]
    public function i_try_to_show_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $this->client->show(Resources::SHIPPING_METHODS, $shipping_method->get_code());
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = ''): void
    {
        $this->client->add_request_data('code', $code);
    }
    #[When('I specify its position as :position')]
    public function i_specify_its_position_as(int $position): void
    {
        $this->client->add_request_data('position', $position);
    }
    #[When('I name it :name in :localeCode')]
    #[When('I rename it to :name in :localeCode')]
    #[When('I do not name it')]
    #[When('I remove its name from :localeCode translation')]
    public function i_name_it_in(?string $name = '', ?string $locale_code = 'en_US'): void
    {
        $this->client->update_request_data(['translations' => [$locale_code => ['name' => $name]]]);
    }
    #[When('I describe it as :description in :localeCode')]
    public function i_describe_it_as_in(string $description, string $locale_code): void
    {
        $data = ['translations' => [$locale_code => []]];
        $data['translations'][$locale_code]['description'] = $description;
        $this->client->update_request_data($data);
    }
    #[When('/^I define it for the (zone named "[^"]+")$/')]
    #[When('I do not specify its zone')]
    public function i_define_it_for_the_zone(?Zone_Interface $zone = null): void
    {
        if (null !== $zone) {
            $this->client->add_request_data('zone', $this->iri_converter->get_iri_from_resource($zone));
        }
    }
    #[When('I disable it')]
    public function i_disable_it(): void
    {
        $this->client->add_request_data('enabled', false);
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->client->add_request_data('enabled', true);
    }
    #[When('I make it available in channel :channel')]
    public function i_make_it_available_in_channel(Channel_Interface $channel): void
    {
        $this->client->add_request_data('channels', [$this->iri_converter->get_iri_from_resource($channel)]);
    }
    #[When('I choose :shippingCalculator calculator')]
    public function i_choose_calculator(string $shipping_calculator): void
    {
        $this->client->add_request_data('calculator', $shipping_calculator);
    }
    #[When('I (try to) archive the :shippingMethod shipping method')]
    public function i_archive_the_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $this->client->custom_item_action(Resources::SHIPPING_METHODS, $shipping_method->get_code(), Http_Request::METHOD_PATCH, 'archive');
        $this->client->index(Resources::SHIPPING_METHODS);
    }
    #[When('I (try to) restore the :shippingMethod shipping method')]
    public function i_try_to_restore_the_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $this->client->custom_item_action(Resources::SHIPPING_METHODS, $shipping_method->get_code(), Http_Request::METHOD_PATCH, 'restore');
    }
    #[When('I specify its amount as :amount for :channel channel')]
    public function i_specify_its_amount_as_for_channel(Channel_Interface $channel, int $amount): void
    {
        $this->client->add_request_data('configuration', [$channel->get_code() => ['amount' => $amount]]);
    }
    #[When('I want to modify a shipping method :shippingMethod')]
    #[When('I try to modify a shipping method :shippingMethod')]
    #[When('/^I want to modify (this shipping method)$/')]
    public function i_want_to_modify_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $this->client->build_update_request(Resources::SHIPPING_METHODS, $shipping_method->get_code());
    }
    #[When('I sort the shipping methods :sortType by code')]
    #[When('I switch the way shipping methods are sorted :sortType by code')]
    public function i_sort_shipping_methods_by_code(string $sort_type = 'ascending'): void
    {
        $this->client->sort(['code' => self::SORT_TYPES[$sort_type]]);
    }
    #[When('I switch the way shipping methods are sorted by code')]
    public function i_switch_the_way_shipping_methods_are_sorted_by_code(): void
    {
        $this->client->sort(['code' => 'desc']);
    }
    #[When('I switch the way shipping methods are sorted by name')]
    public function i_switch_the_way_shipping_methods_are_sorted_by_name(): void
    {
        $this->client->sort(['translation.name' => 'desc']);
    }
    #[When('I filter archival shipping methods')]
    public function i_filter_archival_shipping_methods(): void
    {
        $this->client->add_filter('exists[archivedAt]', true);
        $this->client->filter();
    }
    #[Then('I should see :count shipping methods in the list')]
    public function i_should_see_shipping_methods_in_the_list(int $count): void
    {
        Assert::count($this->response_checker->get_collection($this->client->get_last_response()), $count);
    }
    #[Then('the shipping method :shippingMethod should be in the registry')]
    #[Then('the shipping method :shippingMethod should appear in the registry')]
    #[Then('the :shippingMethod shipping method should be successfully created')]
    public function the_shipping_method_should_appear_in_the_registry(string $name): void
    {
        Assert::true($this->response_checker->has_item_with_translation($this->client->index(Resources::SHIPPING_METHODS), 'en_US', 'name', $name), sprintf('Shipping method with name %s does not exists', $name));
    }
    #[Then('the shipping method :name should not appear in the registry')]
    public function the_shipping_method_should_not_appear_in_the_registry(string $name): void
    {
        Assert::false($this->response_checker->has_item_with_translation($this->client->index(Resources::SHIPPING_METHODS), 'en_US', 'name', $name), sprintf('Shipping method with name %s exists', $name));
    }
    #[Then('/^(this shipping method) should still be in the registry$/')]
    public function this_shipping_method_should_appear_in_the_registry(Shipping_Method_Interface $shipping_method): void
    {
        $name = $shipping_method->get_name();
        Assert::true($this->response_checker->has_item_with_translation($this->client->index(Resources::SHIPPING_METHODS), 'en_US', 'name', $name), sprintf('Shipping method with name %s does not exists', $name));
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Shipping method could not be deleted');
    }
    #[Then('/^(this shipping method) should no longer exist in the registry$/')]
    public function this_shipping_method_should_no_longer_exist_in_the_registry(Shipping_Method_Interface $shipping_method): void
    {
        $shipping_method_name = $shipping_method->get_name();
        Assert::false($this->response_checker->has_item_with_translation($this->client->index(Resources::SHIPPING_METHODS), 'en_US', 'name', $shipping_method_name), sprintf('Shipping method with name %s does not exists', $shipping_method_name));
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Shipping method could not be created');
    }
    #[Then('I should be notified that my access has been denied')]
    public function i_should_be_notified_that_my_access_has_been_denied(): void
    {
        Assert::true($this->response_checker->has_access_denied($this->client->get_last_response()));
    }
    #[Then('the shipping method :shippingMethod should be available in channel :channel')]
    public function the_shipping_method_should_be_available_in_channel(Shipping_Method_Interface $shipping_method, Channel_Interface $channel): void
    {
        Assert::true($this->response_checker->has_value_in_collection($this->client->show(Resources::SHIPPING_METHODS, $shipping_method->get_code()), 'channels', $this->iri_converter->get_iri_from_resource_in_section($channel, 'admin')), sprintf('Shipping method is not assigned to %s channel', $channel->get_name()));
    }
    #[Then('/^(this shipping method) name should be "([^"]+)"$/')]
    #[Then('/^(this shipping method) should still be named "([^"]+)"$/')]
    public function this_shipping_method_name_should_be(Shipping_Method_Interface $shipping_method, string $name): void
    {
        Assert::true($this->response_checker->has_translation($this->client->show(Resources::SHIPPING_METHODS, $shipping_method->get_code()), 'en_US', 'name', $name), 'Shipping method name has not been changed');
    }
    #[Then('/^(this shipping method) should be disabled$/')]
    public function this_shipping_method_should_be_disabled(Shipping_Method_Interface $shipping_method): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::SHIPPING_METHODS, $shipping_method->get_code()), 'enabled', false), 'Shipping method name is not disabled');
    }
    #[Then('/^(this shipping method) should be enabled$/')]
    public function this_shipping_method_should_be_enabled(Shipping_Method_Interface $shipping_method): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::SHIPPING_METHODS, $shipping_method->get_code()), 'enabled', true), 'Shipping method name is not disabled');
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->add_request_data('code', 'NEW_CODE');
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'), 'The code field with value NEW_CODE exist');
    }
    #[Then('I should be notified that shipping method with this code already exists')]
    public function i_should_be_notified_that_shipping_method_with_this_code_already_exists(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Shipping method  has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: The shipping method with given code already exists.');
    }
    #[Then('there should still be only one shipping method with code :value')]
    public function there_should_still_be_only_one_shipping_method_with(string $value): void
    {
        $response = $this->client->index(Resources::SHIPPING_METHODS);
        $items_count = $this->response_checker->count_collection_items($response);
        Assert::same($items_count, 1, sprintf('Expected 1 shipping method, but got %d', $items_count));
        Assert::true($this->response_checker->has_item_with_value($response, 'code', $value));
    }
    #[Then('the only shipping method on the list should be :name')]
    public function the_only_shipping_method_on_the_list_should_be(string $name): void
    {
        $response = $this->client->get_last_response();
        $items_count = $this->response_checker->count_collection_items($response);
        Assert::same($items_count, 1, sprintf('Expected 1 shipping method, but got %d', $items_count));
        Assert::true($this->response_checker->has_item_with_translation($response, 'en_US', 'name', $name));
    }
    #[Then('I should see :amount shipping methods on the list')]
    public function i_should_see_shipping_method_on_the_list(int $amount): void
    {
        $this->client->index(Resources::SHIPPING_METHODS);
        $response = $this->client->get_last_response();
        $items_count = $this->response_checker->count_collection_items($response);
        Assert::same($items_count, $amount, sprintf('Expected 1 shipping method, but got %d', $items_count));
    }
    #[Then('I should be notified that it is in use')]
    public function i_should_be_notified_that_it_is_in_use(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot delete, the shipping method is in use.');
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s: Please enter shipping method %s.', $element, $element));
    }
    #[Then('I should be notified that zone has to be selected')]
    public function i_should_be_notified_that_zone_has_to_be_selected(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'zone: Please select shipping method zone.');
    }
    #[Then('I should be notified that the zone is required')]
    public function i_should_be_notified_that_zone_has_to_be_iri_and_cannot_be_null(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The type of the "zone" attribute must be "array" (nested document) or "string" (IRI), "NULL" given.');
    }
    #[Then('shipping method with :element :value should not be added')]
    public function the_shipping_method_with_element_value_should_not_be_added(string $element, string $value): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::SHIPPING_METHODS), $element, $value), sprintf('Shipping method should not have %s "%s", but it does,', $element, $value));
    }
    #[Then('the first shipping method on the list should have code :value')]
    public function the_first_product_on_the_list_should_have(string $value): void
    {
        $shipping_methods = $this->response_checker->get_collection($this->client->get_last_response());
        Assert::same(reset($shipping_methods)['code'], $value);
    }
    #[Then('the first shipping method on the list should have name :value')]
    public function the_first_shipping_method_on_the_list_should_have(string $value): void
    {
        $shipping_methods = $this->response_checker->get_collection($this->client->get_last_response());
        Assert::same(reset($shipping_methods)['translations'][$this->get_admin_locale_code()]['name'], $value);
    }
    #[Then('the last shipping method on the list should have name :value')]
    public function the_last_shipping_method_on_the_list_should_have(string $value): void
    {
        $response = $this->shared_storage->has('response') ? $this->shared_storage->get('response') : $this->client->get_last_response();
        $shipping_methods = $this->response_checker->get_collection($response);
        Assert::same(end($shipping_methods)['translations']['en_US']['name'], $value);
    }
    #[Then('I should be viewing non archival shipping methods')]
    public function i_should_be_viewing_non_archival_shipping_methods(): void
    {
        // Intentionally left blank
    }
    #[Then('shipping method :shippingMethod should still have code :code')]
    public function shipping_method_should_still_have_code(Shipping_Method_Interface $shipping_method, string $code): void
    {
        Assert::same($this->response_checker->get_value($this->client->show(Resources::SHIPPING_METHODS, $shipping_method->get_code()), 'code'), $code);
    }
    #[Then('I should be notified that amount for :channel channel should not be blank')]
    public function i_should_be_notified_that_amount_for_channel_should_not_be_blank(Channel_Interface $channel): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('configuration[%s][amount]: This value should not be blank.', $channel->get_code()));
    }
    #[Then('I should be notified that code needs to contain only specific symbols')]
    public function i_should_be_notified_that_code_needs_to_contain_only_specific_symbols(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'code: Shipping method code can only be comprised of letters, numbers, dashes and underscores.');
    }
    #[Then('I should be notified that shipping charge for :channel channel cannot be lower than 0')]
    public function i_should_be_notified_that_shipping_charge_for_channel_cannot_be_lower_than0(Channel_Interface $channel): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('configuration[%s][amount]: Shipping charge cannot be lower than 0.', $channel->get_code()));
    }
    #[Then('I should be notified that the weight rule has an invalid configuration')]
    public function i_should_be_notified_that_the_weight_rule_has_an_invalid_configuration(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'configuration[weight]: This value should be of type numeric.');
    }
    #[Then('I should be notified that the amount rule has an invalid configuration in :channel channel')]
    public function i_should_be_notified_that_the_amount_rule_has_an_invalid_configuration_in_channel(Channel_Interface $channel): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('configuration[%s][amount]: This value should be of type numeric.', $channel->get_code()));
    }
    private function get_admin_locale_code(): string
    {
        /** @var AdminUserInterface $adminUser */
        $admin_user = $this->shared_storage->get('administrator');
        $response = $this->client->show(Resources::ADMINISTRATORS, (string) $admin_user->get_id());
        return $this->response_checker->get_value($response, 'localeCode');
    }
}
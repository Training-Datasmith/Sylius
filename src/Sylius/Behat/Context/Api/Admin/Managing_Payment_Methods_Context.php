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
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Payment_Methods_Context implements Context
{
    use Validation_Trait;
    public const SORT_TYPES = ['ascending' => 'asc', 'descending' => 'desc'];
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('/^I search by "([^"]+)" (code|name)$/')]
    public function i_search_by_name(string $phrase, string $field): void
    {
        $field = $field === 'name' ? 'translations.name' : $field;
        $this->client->add_filter($field, $phrase);
        $this->client->filter();
    }
    #[When('I choose enabled filter')]
    public function i_choose_enabled_filter(): void
    {
        $this->client->add_filter('enabled', true);
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->client->filter();
    }
    #[When('I want to modify the :paymentMethod payment method')]
    public function i_want_to_modify_a_payment_method(Payment_Method_Interface $payment_method): void
    {
        $this->client->build_update_request(Resources::PAYMENT_METHODS, $payment_method->get_code());
    }
    #[When('/^I set its "Username" as "([^"]+)", "Password" as "([^"]+)" and "Signature" as "([^"]+)"$/')]
    public function i_set_its_username_as_password_as_and_signature_as(string $username, string $password, string $signature): void
    {
        $this->update_gateway_config(['username' => $username, 'password' => $password, 'signature' => $signature]);
    }
    #[When('/^I set its "Publishable key" as "([^"]+)" and "Secret key" as "([^"]+)"$/')]
    public function i_set_its_publishable_key_as_and_secret_key_as(string $publishable_key, string $secret_key): void
    {
        $this->update_gateway_config(['publishable_key' => $publishable_key, 'secret_key' => $secret_key]);
    }
    #[When('I update its :field with :value')]
    public function i_update_its_with(string $field, string $value): void
    {
        $available_fields = ['Publishable key', 'Secret key', 'Username', 'Password', 'Signature', 'Sandbox'];
        if (!in_array($field, $available_fields)) {
            throw new \InvalidArgumentException(sprintf('There is no configuration for "%s" field.', $field));
        }
        $this->update_gateway_config([String_Inflector::name_to_lowercase_code($field) => $value]);
    }
    #[When('I name it :name in :localeCode')]
    #[When('I rename it to :name in :localeCode')]
    #[When('I remove its name from :localeCode translation')]
    public function i_name_it_in(string $locale_code, ?string $name = null): void
    {
        $this->client->add_request_data('translations', [$locale_code => ['name' => $name]]);
    }
    #[When('I enable sandbox mode')]
    public function i_enable_sandbox_mode(): void
    {
        $this->client->add_request_data('gatewayConfig', ['config' => ['sandbox' => true]]);
    }
    #[When('I do not name it')]
    public function i_do_not_name_it(): void
    {
        // Intentionally left blank to fulfill context expectation
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
    #[When('I delete the :paymentMethod payment method')]
    #[When('I try to delete the :paymentMethod payment method')]
    public function i_delete_payment_method(Payment_Method_Interface $payment_method): void
    {
        $this->client->delete(Resources::PAYMENT_METHODS, $payment_method->get_code());
    }
    #[When('I want to create a new offline payment method')]
    #[When('I want to create a new payment method with :factory gateway factory')]
    public function i_want_to_create_a_new_payment_method(string $factory = 'Offline'): void
    {
        $factory = str_replace(' ', '_', strtolower($factory));
        $this->client->build_create_request(Resources::PAYMENT_METHODS);
        $this->client->add_request_data('gatewayConfig', ['factoryName' => $factory, 'gatewayName' => $factory]);
    }
    #[When('I want to create a new payment method without gateway configuration')]
    public function i_want_to_create_a_new_payment_method_without_gateway_configuration(): void
    {
        $this->client->build_create_request(Resources::PAYMENT_METHODS);
        $this->client->add_request_data('code', 'TEST');
    }
    #[When('I want to create a new payment method without gateway name')]
    public function i_want_to_create_a_new_payment_method_without_gateway_name(): void
    {
        $this->client->build_create_request(Resources::PAYMENT_METHODS);
        $this->client->add_request_data('code', 'TEST');
        $this->client->add_request_data('gatewayConfig', ['factoryName' => 'offline']);
    }
    #[When('I want to create a new payment method without factory name')]
    public function i_want_to_create_a_new_payment_method_without_factory_name(): void
    {
        $this->client->build_create_request(Resources::PAYMENT_METHODS);
        $this->client->add_request_data('code', 'TEST');
        $this->client->add_request_data('gatewayConfig', ['gatewayName' => 'offline']);
    }
    #[When('I want to create a new payment method with wrong factory name')]
    public function i_want_to_create_a_new_payment_method_with_wrong_factory_name(): void
    {
        $this->client->build_create_request(Resources::PAYMENT_METHODS);
        $this->client->add_request_data('code', 'TEST');
        $this->client->add_request_data('gatewayConfig', ['factoryName' => 'gateway_with_wrong_factory_name', 'gatewayName' => 'gateway with wrong factory name']);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->client->add_request_data('code', $code);
    }
    #[When('I describe it as :description in :localeCode')]
    public function i_describe_it_as_in(string $description, string $locale_code): void
    {
        $this->client->add_request_data('translations', [$locale_code => ['description' => $description]]);
    }
    #[When('make it available in channel :channel')]
    public function i_make_it_available_in_channel(Channel_Interface $channel): void
    {
        $this->client->replace_request_data('channels', [$this->iri_converter->get_iri_from_resource_in_section($channel, 'admin')]);
    }
    #[When('I set its instruction as :instructions in :localeCode')]
    public function i_set_its_instruction_as_in(string $instructions, string $locale_code): void
    {
        $this->client->add_request_data('translations', [$locale_code => ['instructions' => $instructions]]);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I start sorting payment methods by name')]
    #[When('the payment methods are already sorted by name')]
    #[When('I switch the way payment methods are sorted to :sortType by name')]
    public function i_sort_shipping_methods_by_name(string $sort_type = 'ascending'): void
    {
        $this->client->sort(['translation.name' => self::SORT_TYPES[$sort_type], 'localeCode' => $this->get_admin_locale_code()]);
    }
    #[Given('the payment methods are already sorted by code')]
    #[When('I start sorting payment methods by code')]
    #[When('I switch the way payment methods are sorted to :sortType by code')]
    public function i_sort_shipping_methods_by_code(string $sort_type = 'ascending'): void
    {
        $this->client->sort(['code' => self::SORT_TYPES[$sort_type], 'localeCode' => $this->get_admin_locale_code()]);
    }
    #[When('I configure it for username :username with :signature signature')]
    public function i_configure_it_for_username_with_signature(string $username, string $signature): void
    {
        $this->client->add_request_data('gatewayConfig', ['config' => ['username' => $username, 'signature' => $signature, 'sandbox' => true]]);
    }
    #[When('I configure it for username :username with :signature signature and password, but without sandbox')]
    public function i_configure_it_for_username_with_signature_but_without_sandbox(string $username, string $signature): void
    {
        $this->client->add_request_data('gatewayConfig', ['config' => ['username' => $username, 'signature' => $signature, 'password' => 'TEST', 'sandbox' => null]]);
    }
    #[When('I configure it for username :username with :signature signature and password, but with sandbox that has wrong type')]
    public function i_configure_it_for_username_with_signature_but_with_wrong_sandbox_type(string $username, string $signature): void
    {
        $this->client->add_request_data('gatewayConfig', ['config' => ['username' => $username, 'signature' => $signature, 'password' => 'TEST', 'sandbox' => 'test']]);
    }
    #[When('I configure it with only :element')]
    public function i_configure_it_with_only(string $element): void
    {
        $element = str_replace(' ', '_', strtolower($element));
        $this->client->add_request_data('gatewayConfig', ['config' => [$element => 'TEST', $element === 'secret_key' ? 'publishable_key' : 'secret_key' => null]]);
    }
    #[When('I do not specify configuration password')]
    public function i_do_not_specify_configuration_password(): void
    {
        $this->client->add_request_data('gatewayConfig', ['config' => ['password' => null]]);
    }
    #[Given('I am browsing payment methods')]
    #[When('I browse payment methods')]
    public function i_browse_payment_methods(): void
    {
        $this->client->index(Resources::PAYMENT_METHODS);
    }
    #[When('I change my locale to :localeCode')]
    public function i_change_my_locale_to(string $locale_code): void
    {
        /** @var AdminUserInterface $adminUser */
        $admin_user = $this->shared_storage->get('administrator');
        $this->client->build_update_request(Resources::ADMINISTRATORS, (string) $admin_user->get_id());
        $this->client->update_request_data(['localeCode' => $locale_code]);
        $this->client->update();
    }
    #[Then('the first payment method on the list should have :field :value')]
    public function the_first_payment_method_on_the_list_should_have(string $field, string $value): void
    {
        $response = $this->client->get_last_response();
        $payment_methods = $this->response_checker->get_collection($response);
        Assert::same($this->get_field_value_of_first_payment_method($payment_methods[0], $field), $value);
    }
    #[Then('the last payment method on the list should have :field :value')]
    public function the_last_payment_method_on_the_list_should_have(string $field, string $value): void
    {
        $response = $this->client->index(Resources::PAYMENT_METHODS);
        if ($field === 'name') {
            $payment_methods = $this->response_checker->get_collection($response);
            Assert::same(end($payment_methods)['translations']['en_US']['name'], $value);
            return;
        }
        $count = $this->response_checker->count_collection_items($response);
        Assert::true($this->response_checker->has_item_on_position_with_value($this->client->get_last_response(), $count - 1, $field, $value), sprintf('There should be payment method with %s "%s" on position %d, but it does not.', $field, $value, $count - 1));
    }
    #[Then('I should see a single payment method in the list')]
    #[Then('I should see :amount payment methods in the list')]
    public function i_should_see_payment_methods_in_the_list(int $amount = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $amount);
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('The type of the "%s" attribute must be "string", "NULL" given.', $element));
    }
    #[Then('I should be notified that I have to specify payment method :element')]
    public function i_should_be_notified_that_i_need_to_specify_payment_method_name(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s: Please enter payment method %s.', $element, $element));
    }
    #[Then('I should be notified that I have to specify gateway configuration')]
    public function i_should_be_notified_that_i_have_to_specify_gateway_configuration(): void
    {
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'gatewayConfig: This value should not be blank.');
    }
    #[Then('I should be notified that I have to specify gateway name')]
    public function i_should_be_notified_that_i_have_to_specify_gateway_name(): void
    {
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'gatewayConfig.gatewayName: Please enter gateway name.');
    }
    #[Then('I should be notified that I have to specify factory name')]
    public function i_should_be_notified_that_i_have_to_specify_factory_name(): void
    {
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'gatewayConfig.factoryName: Please enter gateway factory name.');
    }
    #[Then('I should be notified that I have to specify factory name that is available')]
    public function i_should_be_notified_that_i_have_to_specify_factory_name_that_is_available(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'gatewayConfig.factoryName: Invalid gateway factory. Available factories are ');
    }
    #[Then('the payment method with :element :value should not be added')]
    public function the_payment_method_with_element_value_should_not_be_added(string $element, string $value): void
    {
        if ($element === 'name') {
            Assert::false(in_array($value, $this->get_payment_method_names_from_collection()), sprintf('Payment method should have name "%s", but it does not', $value));
            return;
        }
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::PAYMENT_METHODS), $element, $value), sprintf('Payment method with %s: %s exists', $element, $value));
    }
    #[Then('this payment method should still be named :paymentMethodName')]
    public function this_payment_method_name_should_still_be_named(string $payment_method_name): void
    {
        Assert::in_array($payment_method_name, $this->get_payment_method_names_from_collection(), sprintf('Payment method with name %s does not exist', $payment_method_name));
    }
    #[Then('the code field should be disabled')]
    #[Then('I should not be able to edit its code')]
    public function the_code_field_should_be_disabled(): void
    {
        $this->client->update_request_data(['code' => 'NEW_CODE']);
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'));
    }
    #[Then('the factory name field should be disabled')]
    public function the_factory_name_field_should_be_disabled(): void
    {
        $this->client->add_request_data('gatewayConfig', ['factoryName' => 'NEWFACTORYNAME']);
        $this->client->update();
        Assert::false($this->response_checker->has_value($this->client->get_last_response(), 'gatewayConfig', 'NEWFACTORYNAME'));
    }
    #[Then('/^(this payment method) should be enabled/')]
    public function this_payment_method_should_be_enabled(Payment_Method_Interface $payment_method): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::PAYMENT_METHODS, $payment_method->get_code()), 'enabled', true), 'This payment method should be enabled');
    }
    #[Then('/^(this payment method) should be disabled$/')]
    public function this_shipping_method_should_be_disabled(Payment_Method_Interface $payment_method): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::PAYMENT_METHODS, $payment_method->get_code()), 'enabled', false), 'This payment method should be disabled');
    }
    #[Then('the payment method :paymentMethod should have instructions :instructions in :localeCode')]
    public function the_payment_method_should_have_instructions_in(Payment_Method_Interface $payment_method, string $instructions, string $locale_code): void
    {
        $translations = $this->response_checker->get_value($this->client->show(Resources::PAYMENT_METHODS, $payment_method->get_code()), 'translations');
        Assert::same($translations[$locale_code]['instructions'], $instructions, sprintf('Payment method does not have %s instruction', $instructions));
    }
    #[Then('the payment method :paymentMethod should be available in channel :channel')]
    public function the_payment_method_should_be_available_in_channel(Payment_Method_Interface $payment_method, Channel_Interface $channel): void
    {
        $this->client->show(Resources::PAYMENT_METHODS, $payment_method->get_code());
        $channels_array = $this->response_checker->get_value($this->client->get_last_response(), 'channels');
        Assert::true(in_array($this->iri_converter->get_iri_from_resource_in_section($channel, 'admin'), $channels_array));
    }
    #[Then('/^(this payment method) should no longer exist in the registry$/')]
    public function this_payment_method_should_no_longer_exist_in_the_registry(Payment_Method_Interface $payment_method): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::PAYMENT_METHODS), 'code', $payment_method->get_code()), sprintf('Payment method with code %s exists but should not', $payment_method->get_code()));
    }
    #[Then('I should be notified that payment method with this code already exists')]
    public function i_should_be_notified_that_payment_method_with_this_code_already_exists(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response), 'Payment method  has been created successfully, but it should not');
        Assert::same($this->response_checker->get_error($response), 'code: The payment method with given code already exists.');
    }
    #[Then('there should still be only one payment method with :element :code')]
    public function there_should_still_be_only_one_payment_method_with(string $element, string $code): void
    {
        $response = $this->client->index(Resources::PAYMENT_METHODS);
        $items_count = $this->response_checker->count_collection_items($response);
        Assert::same($items_count, 1, sprintf('Expected 1 payment method, but got %d', $items_count));
        Assert::true($this->response_checker->has_item_with_value($response, $element, $code));
    }
    #[Then('/^this payment method "([^"]+)" should be "([^"]+)"$/')]
    public function this_payment_method_element_should_be(string $element, string $value): void
    {
        if ($element === 'Name') {
            Assert::in_array($value, $this->get_payment_method_names_from_collection(), sprintf('Payment method should have name "%s", but it does not', $value));
            return;
        }
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::PAYMENT_METHODS), $element, $value), sprintf('Payment method should have %s "%s", but it does,', $element, $value));
    }
    #[Then('/^its gateway configuration "([^"]+)" should be "([^"]+)"$/')]
    public function its_gateway_configuration_should_be(string $element, string $value): void
    {
        $gateway_config = $this->response_checker->get_value($this->client->get_last_response(), 'gatewayConfig');
        Assert::same($value, $gateway_config['config'][String_Inflector::name_to_lowercase_code($element)], sprintf('Gateway configuration should have %s "%s", but it does not', $element, $value));
    }
    #[Then('this payment method should be in sandbox mode')]
    public function this_payment_method_should_be_in_sandbox_mode(): void
    {
        $gateway_config = $this->response_checker->get_value($this->client->get_last_response(), 'gatewayConfig');
        Assert::same($gateway_config['config']['sandbox'], true, 'Gateway configuration should be in sandbox mode, but it is not');
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Payment method could not be created');
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Payment method could not be deleted');
    }
    #[Then('I should be notified that it is in use')]
    public function i_should_be_notified_that_it_is_in_use(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot delete, the payment method is in use.');
    }
    #[Then('the payment method :paymentMethodName should appear in the registry')]
    #[Then('the payment method :paymentMethodName should be in the registry')]
    #[Then('I should see the payment method :paymentMethodName in the list')]
    public function the_payment_method_should_appear_in_the_registry(string $payment_method_name): void
    {
        Assert::in_array($payment_method_name, $this->get_payment_method_names_from_collection(), sprintf('Payment method with name %s does not exist', $payment_method_name));
    }
    #[Then('I should see the payment method :paymentMethodName')]
    public function i_should_see_the_payment_method(string $payment_method_name): void
    {
        Assert::true(in_array($payment_method_name, $this->get_filtered_out_payment_method_names_from_collection()), sprintf('Payment method with name %s does not exist', $payment_method_name));
    }
    #[Then('I should not see the payment method :paymentMethodName')]
    public function i_should_not_see_the_payment_method(string $payment_method_name): void
    {
        Assert::false(in_array($payment_method_name, $this->get_filtered_out_payment_method_names_from_collection()), sprintf('Payment method with name %s exist, but should not', $payment_method_name));
    }
    #[Then('/^(this payment method) should still be in the registry$/')]
    public function this_payment_method_should_still_be_in_the_registry(Payment_Method_Interface $payment_method): void
    {
        $this->the_payment_method_should_appear_in_the_registry($payment_method->get_name());
    }
    private function get_admin_locale_code(): string
    {
        /** @var AdminUserInterface $adminUser */
        $admin_user = $this->shared_storage->get('administrator');
        $response = $this->client->show(Resources::ADMINISTRATORS, (string) $admin_user->get_id());
        return $this->response_checker->get_value($response, 'localeCode');
    }
    /**
     * @param array<string, mixed> $paymentMethod
     */
    private function get_field_value_of_first_payment_method(array $payment_method, string $field): ?string
    {
        if ($field === 'code') {
            return $payment_method['code'];
        }
        if ($field === 'name') {
            return $payment_method['translations'][$this->get_admin_locale_code()]['name'];
        }
        return null;
    }
    /** @return string[] */
    private function get_payment_method_names_from_collection(): array
    {
        $payment_methods = $this->response_checker->get_collection($this->client->index(Resources::PAYMENT_METHODS));
        return array_map(fn(array $payment_method) => $payment_method['translations']['en_US']['name'], $payment_methods);
    }
    /** @return string[] */
    private function get_filtered_out_payment_method_names_from_collection(): array
    {
        $payment_methods = $this->response_checker->get_collection($this->client->get_last_response());
        return array_map(fn(array $payment_method) => $payment_method['translations']['en_US']['name'], $payment_methods);
    }
    /**
     * @param array<string, string> $config
     */
    private function update_gateway_config(array $config): void
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $payment_method = $this->shared_storage->get('payment_method');
        $gateway_configuration_iri = $this->iri_converter->get_iri_from_resource_in_section($payment_method->get_gateway_config(), 'admin');
        $this->client->add_request_data('gatewayConfig', ['@id' => $gateway_configuration_iri, 'config' => $config]);
    }
}
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
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Context\Ui\Admin\Helper\Validation_Trait;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Payment_Method\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Payment_Method\Update_Page_Interface;
use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Payment\Model\Payment_Method_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Payment_Methods_Context implements Context
{
    use Validation_Trait;
    /**
     * @param string[] $gatewayFactories
     */
    public function __construct(private Create_Page_Interface $create_page, private Index_Page_Interface $index_page, private Update_Page_Interface $update_page, private Current_Page_Resolver_Interface $current_page_resolver, private array $gateway_factories)
    {
    }
    #[When('I want to modify the :paymentMethod payment method')]
    public function i_want_to_modify_a_payment_method(Payment_Method_Interface $payment_method): void
    {
        $this->update_page->open(['id' => $payment_method->get_id()]);
    }
    #[When('I name it :name in :language')]
    #[When('I rename it to :name in :language')]
    #[When('I remove its name from :language translation')]
    public function i_name_it_in(string $language, ?string $name = null): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        $current_page->name_it($name ?? '', $language);
    }
    #[When('I enable sandbox mode')]
    public function i_enable_sandbox_mode(): void
    {
        $this->update_page->enable_sandbox_mode();
    }
    #[When('I do not name it')]
    public function i_do_not_name_it(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->update_page->enable();
    }
    #[When('I disable it')]
    public function i_disable_it(): void
    {
        $this->update_page->disable();
    }
    #[When('I delete the :paymentMethod payment method')]
    #[When('I try to delete the :paymentMethod payment method')]
    public function i_delete_payment_method(Payment_Method_Interface $payment_method): void
    {
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['code' => $payment_method->get_code(), 'name' => $payment_method->get_name()]);
    }
    #[Then('/^(?:this payment method|its gateway configuration) "([^"]+)" should be "([^"]+)"$/')]
    public function its_gateway_configuration_should_be(string $element, string $value): void
    {
        Assert::true($this->update_page->has_resource_values([String_Inflector::name_to_lowercase_code($element) => $value]), sprintf('Expected "%s" to be "%s", but it is not.', String_Inflector::name_to_lowercase_code($element), $value));
    }
    #[Then('this payment method should be in sandbox mode')]
    public function this_payment_method_should_be_in_sandbox_mode(): void
    {
        Assert::true($this->update_page->is_payment_method_in_sandbox_mode());
    }
    #[When('I want to create a new offline payment method')]
    #[When('I want to create a new payment method with :factory gateway factory')]
    public function i_want_to_create_a_new_payment_method(string $factory = 'Offline'): void
    {
        $this->create_page->open(['factory' => array_search($factory, $this->gateway_factories, true)]);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->create_page->specify_code($code ?? '');
    }
    #[When('I describe it as :description in :language')]
    public function i_describe_it_as_in(string $description, string $language): void
    {
        $this->create_page->describe_it($description, $language);
    }
    #[When('make it available in channel :channel')]
    public function i_make_it_available_in_channel(string $channel): void
    {
        $this->create_page->check_channel($channel);
    }
    #[Given('I set its instruction as :instructions in :language')]
    public function i_set_its_instruction_as_in(string $instructions, string $language): void
    {
        $this->create_page->set_instructions($instructions, $language);
    }
    /**
     *
     * @throws ElementNotFoundException
     */
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I cancel my changes')]
    public function i_cancel_my_changes(): void
    {
        $this->create_page->cancel_changes();
    }
    #[When('I check (also) the :paymentMethodName payment method')]
    public function i_check_the_payment_method(string $payment_method_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $payment_method_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[Then('I should see the payment method :paymentMethodName')]
    public function i_should_see_the_payment_method(string $payment_method_name): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $payment_method_name]));
    }
    #[Then('I should not see the payment method :paymentMethodName')]
    public function i_should_not_see_the_payment_method(string $payment_method_name): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['name' => $payment_method_name]));
    }
    #[Then('the payment method :paymentMethodName should appear in the registry')]
    #[Then('the payment method :paymentMethodName should be in the registry')]
    #[Then('I should see the payment method :paymentMethodName in the list')]
    public function the_payment_method_should_appear_in_the_registry(string $payment_method_name): void
    {
        $this->there_should_still_be_only_one_payment_method_with('name', $payment_method_name);
    }
    #[Given('/^(this payment method) should still be in the registry$/')]
    public function this_payment_method_should_still_be_in_the_registry(Payment_Method_Interface $payment_method): void
    {
        $this->the_payment_method_should_appear_in_the_registry($payment_method->get_name());
    }
    #[Given('I am browsing payment methods')]
    #[When('I browse payment methods')]
    public function i_browse_payment_methods(): void
    {
        $this->index_page->open();
    }
    #[When('I choose enabled filter')]
    public function i_choose_enabled_filter(): void
    {
        $this->index_page->choose_enabled_filter();
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->index_page->filter();
    }
    #[Then('the first payment method on the list should have :field :value')]
    public function the_first_payment_method_on_the_list_should_have(string $field, string $value): void
    {
        Assert::same($this->index_page->get_column_fields($field)[0], $value);
    }
    #[Then('the last payment method on the list should have :field :value')]
    public function the_last_payment_method_on_the_list_should_have(string $field, string $value): void
    {
        $values = $this->index_page->get_column_fields($field);
        Assert::same(end($values), $value);
    }
    #[Given('the payment methods are already sorted by :field')]
    #[When('I switch the way payment methods are sorted by :field')]
    #[When('I start sorting payment methods by :field')]
    #[When('I switch the way payment methods are sorted to descending by :field')]
    public function i_sort_payment_methods_by(string $field): void
    {
        $this->index_page->sort_by($field);
    }
    #[Then('I should see a single payment method in the list')]
    #[Then('I should see :amount payment methods in the list')]
    public function i_should_see_payment_methods_in_the_list(int $amount = 1): void
    {
        Assert::same($this->index_page->count_items(), $amount);
    }
    /**
     *
     * @throws ElementNotFoundException
     */
    #[Then('I should be notified that :element is required')]
    #[Then('I should be notified that I have to specify payment method :element')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        $this->assert_field_validation_message($element, sprintf('Please enter payment method %s.', $element));
    }
    /**
     * @throws ElementNotFoundException
     */
    #[Then('I should be notified that gateway name should contain only letters and underscores')]
    public function i_should_be_notified_that_gateway_name_should_contain_only_letters_and_underscores(): void
    {
        Assert::same($this->create_page->get_validation_message('gateway_name'), 'Gateway name should contain only letters and underscores.');
    }
    #[Then('the payment method with :element :value should not be added')]
    public function the_payment_method_with_element_value_should_not_be_added(string $element, string $value): void
    {
        $this->i_browse_payment_methods();
        Assert::false($this->index_page->is_single_resource_on_page([$element => $value]));
    }
    #[Then('/^(this payment method) should still be named "([^"]+)"$/')]
    public function this_shipping_method_name_should_be(Payment_Method_Interface $payment_method, string $payment_method_name): void
    {
        $this->i_browse_payment_methods();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $payment_method->get_code(), 'name' => $payment_method_name]));
    }
    /**
     * @throws ElementNotFoundException
     */
    private function assert_field_validation_message(string $element, string $expected_message): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        Assert::same($current_page->get_validation_message($element), $expected_message);
    }
    #[Then('the code field should be disabled')]
    #[Then('I should not be able to edit its code')]
    public function the_code_field_should_be_disabled(): void
    {
        Assert::true($this->update_page->is_code_disabled());
    }
    #[Then('the factory name field should be disabled')]
    public function the_factory_name_field_should_be_disabled(): void
    {
        Assert::true($this->update_page->is_factory_name_field_disabled());
    }
    #[Then('I should not be able to edit its usePayum field')]
    public function the_use_payum_field_should_be_disabled(): void
    {
        Assert::true($this->update_page->is_use_payum_field_disabled());
    }
    #[Then('this payment method should be enabled')]
    public function this_payment_method_should_be_enabled(): void
    {
        Assert::true($this->update_page->is_payment_method_enabled());
    }
    #[Then('this payment method should be disabled')]
    public function this_payment_method_should_be_disabled(): void
    {
        Assert::false($this->update_page->is_payment_method_enabled());
    }
    #[Given('the payment method :paymentMethod should have instructions :instructions in :language')]
    public function the_payment_method_should_have_instructions_in(Payment_Method_Interface $payment_method, string $instructions, string $language): void
    {
        $this->i_want_to_modify_a_payment_method($payment_method);
        Assert::same($this->update_page->get_payment_method_instructions($language), $instructions);
    }
    #[Then('the payment method :paymentMethod should be available in channel :channelName')]
    public function the_payment_method_should_be_available_in_channel(Payment_Method_Interface $payment_method, string $channel_name): void
    {
        $this->i_want_to_modify_a_payment_method($payment_method);
        Assert::true($this->update_page->is_available_in_channel($channel_name));
    }
    #[Then('/^(this payment method) should no longer exist in the registry$/')]
    public function this_payment_method_should_no_longer_exist_in_the_registry(Payment_Method_Interface $payment_method): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['code' => $payment_method->get_code(), 'name' => $payment_method->get_name()]));
    }
    /**
     * @throws ElementNotFoundException
     */
    #[Then('I should be notified that payment method with this code already exists')]
    public function i_should_be_notified_that_payment_method_with_this_code_already_exists(): void
    {
        Assert::same($this->create_page->get_validation_message('code'), 'The payment method with given code already exists.');
    }
    #[Then('there should still be only one payment method with :element :code')]
    public function there_should_still_be_only_one_payment_method_with(string $element, string $code): void
    {
        $this->i_browse_payment_methods();
        Assert::true($this->index_page->is_single_resource_on_page([$element => $code]));
    }
    #[When('I do not specify configuration password')]
    public function i_do_not_specify_configuration_password(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[Then('I should be redirected to the previous page of only enabled payment methods')]
    public function i_should_be_redirected_to_the_previous_filtered_page_with_filter(): void
    {
        Assert::true($this->index_page->is_enabled_filter_applied());
    }
    protected function resolve_current_page(): Sylius_Page_Interface
    {
        return $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
    }
}
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
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Friends_Of_Behat\Page_Object_Extension\Page\Unexpected_Page_Exception;
use Sylius\Behat\Page\Shop\Checkout\Complete_Page_Interface;
use Sylius\Behat\Page\Shop\Checkout\Select_Payment_Page_Interface;
use Sylius\Behat\Page\Shop\Checkout\Select_Shipping_Page_Interface;
use Webmozart\Assert\Assert;
final readonly class Checkout_Shipping_Context implements Context
{
    public function __construct(private Select_Shipping_Page_Interface $select_shipping_page, private Select_Payment_Page_Interface $select_payment_page, private Complete_Page_Interface $complete_page)
    {
    }
    #[When('I navigate directly to the addressing step in the checkout steps panel')]
    public function i_navigate_directly_to_the_addressing_step_in_the_checkout_steps_panel(): void
    {
        $this->select_shipping_page->change_address_by_step_label();
    }
    #[Given('the visitor has proceeded :shippingMethodName shipping method')]
    #[Given('the customer has proceeded with :shippingMethodName shipping method')]
    #[Given('the visitor proceed with :shippingMethodName shipping method')]
    #[Given('the customer proceed with :shippingMethodName shipping method')]
    #[Given('I completed the shipping step with :shippingMethodName shipping method')]
    #[Given('I have proceeded with :shippingMethodName shipping method')]
    #[Given('I have proceeded selecting :shippingMethodName shipping method')]
    #[When('I proceed with :shippingMethodName shipping method')]
    #[When('the visitor proceeds with :shippingMethod shipping method')]
    #[When('the customer proceeds with :shippingMethod shipping method')]
    public function i_have_proceeded_with_selecting_shipping_method(string $shipping_method_name): void
    {
        if (!$this->select_shipping_page->is_open()) {
            $this->select_shipping_page->open();
        }
        $this->select_shipping_page->select_shipping_method($shipping_method_name);
        $this->select_shipping_page->next_step();
    }
    #[Given('I have selected :shippingMethodName shipping method')]
    #[When('I select :shippingMethodName shipping method')]
    #[When('I change shipping method to :shippingMethodName')]
    public function i_select_shipping_method(string $shipping_method_name): void
    {
        $this->select_shipping_page->select_shipping_method($shipping_method_name);
    }
    #[When('I complete the shipping step')]
    #[When('I complete the shipping step with the first shipping method')]
    public function i_complete_the_shipping_step(): void
    {
        $this->select_shipping_page->next_step();
    }
    #[When('I decide to change my address')]
    public function i_decide_to_change_my_address(): void
    {
        $this->select_shipping_page->change_address();
    }
    #[When('I decide to change shipping method')]
    #[When('I go back to the shipping step')]
    #[When('I go to the shipping step')]
    #[When('I want to complete the shipping step')]
    #[When('the customer wants to complete the shipping step')]
    public function i_want_to_complete_the_shipping_step(): void
    {
        $this->select_shipping_page->open();
    }
    #[Then('I should not be able to select :shippingMethodName shipping method')]
    public function i_should_not_be_able_to_select_shipping_method(string $shipping_method_name): void
    {
        Assert::false(in_array($shipping_method_name, $this->select_shipping_page->get_shipping_methods(), true));
    }
    #[Then('I should have :shippingMethodName shipping method available as the first choice')]
    public function i_should_have_shipping_method_available_as_first_choice($shipping_method_name): void
    {
        $shipping_methods = $this->select_shipping_page->get_shipping_methods();
        Assert::same(reset($shipping_methods), $shipping_method_name);
    }
    #[Then('I should have :shippingMethodName shipping method available as the last choice')]
    public function i_should_have_shipping_method_available_as_last_choice($shipping_method_name): void
    {
        $shipping_methods = $this->select_shipping_page->get_shipping_methods();
        Assert::same(end($shipping_methods), $shipping_method_name);
    }
    #[Then('I should be on the checkout shipping step')]
    #[Then('I should be redirected to the shipping step')]
    public function i_should_be_on_the_checkout_shipping_step(): void
    {
        $this->select_shipping_page->verify();
    }
    #[Then('I should be informed that my order cannot be shipped to this address')]
    #[Then('there should be information about no available shipping methods')]
    public function i_should_be_informed_that_my_order_cannot_be_shipped_to_this_address(): void
    {
        Assert::true($this->select_shipping_page->has_no_available_shipping_methods_message());
    }
    #[Then('I should be able to go to the complete step again')]
    public function i_should_be_able_to_go_to_the_complete_step_again(): void
    {
        $this->select_shipping_page->next_step();
        $this->complete_page->verify();
    }
    #[Then('I should be able to go to the payment step again')]
    public function i_should_be_able_to_go_to_the_payment_step_again(): void
    {
        $this->select_shipping_page->next_step();
        $this->select_payment_page->verify();
    }
    #[Then('I should see shipping method :shippingMethodName with fee :fee')]
    public function i_should_see_shipping_fee(string $shipping_method_name, string $fee): void
    {
        Assert::true($this->select_shipping_page->has_shipping_method_fee($shipping_method_name, $fee));
    }
    #[Then('I should see :shippingMethodName shipping method')]
    public function i_should_see_shipping_method(string $shipping_method_name): void
    {
        Assert::true($this->select_shipping_page->has_shipping_method($shipping_method_name));
    }
    #[Then('I should see selected :shippingMethodName shipping method')]
    public function i_should_see_selected_shipping_method($shipping_method_name): void
    {
        Assert::same($this->select_shipping_page->get_selected_shipping_method_name(), $shipping_method_name);
    }
    #[Then('I should not see :shippingMethodName shipping method')]
    public function i_should_not_see_shipping_method(string $shipping_method_name): void
    {
        Assert::false($this->select_shipping_page->has_shipping_method($shipping_method_name));
    }
    #[Then('I should be checking out as :email')]
    public function i_should_be_checking_out_as(string $email): void
    {
        Assert::same($this->select_shipping_page->get_purchaser_identifier(), 'Checking out as ' . $email . '.');
    }
    #[Then('the checkout shipping method step should be completed')]
    #[Then('the customer should have checkout shipping method step completed')]
    #[Then('the visitor should have checkout shipping method step completed')]
    public function the_customer_should_have_checkout_shipping_method_step_completed(): void
    {
        Assert::false($this->select_shipping_page->is_open(), 'Customer should have checkout shipping method step completed, but it is not.');
    }
    #[Then('I should not be able to proceed checkout shipping step')]
    public function i_should_not_be_able_to_proceed_checkout_shipping_step(): void
    {
        $this->select_shipping_page->try_to_open();
        try {
            $this->select_shipping_page->next_step();
            if ($this->select_shipping_page->is_open()) {
                return;
            }
        } catch (Element_Not_Found_Exception) {
            return;
        }
        throw new Unexpected_Page_Exception('It should not be possible to complete checkout shipping step.');
    }
    #[Then('I should still be on the shipping step')]
    public function i_should_still_be_on_the_shipping_step(): void
    {
        Assert::true($this->select_shipping_page->is_open(), 'Shipping page is not open.');
    }
    #[Then('I should not be able to complete the shipping step')]
    public function i_should_not_be_able_to_complete_the_shipping_step(): void
    {
        Assert::false($this->select_shipping_page->is_next_step_button_enabled(), 'The "next step" button should be disabled, but it does not.');
    }
}
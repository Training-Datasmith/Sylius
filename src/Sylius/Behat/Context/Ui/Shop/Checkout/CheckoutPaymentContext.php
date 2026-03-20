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
use Webmozart\Assert\Assert;
final readonly class Checkout_Payment_Context implements Context
{
    public function __construct(private Select_Payment_Page_Interface $select_payment_page, private Complete_Page_Interface $complete_page)
    {
    }
    #[Given('I completed the payment step with :paymentMethodName payment method')]
    #[Given('the visitor has proceeded :paymentMethodName payment')]
    #[Given('the customer has proceeded :paymentMethodName payment')]
    #[Given('the visitor proceed with :paymentMethodName payment')]
    #[Given('the customer proceed with :paymentMethodName payment')]
    #[When('/^I choose "([^"]*)" payment method$/')]
    #[When('the visitor proceeds with :paymentMethod payment method')]
    #[When('the customer proceeds with :paymentMethod payment method')]
    public function i_choose_payment_method(string $payment_method_name): void
    {
        $this->select_payment_page->select_payment_method($payment_method_name ?: 'Offline');
        $this->select_payment_page->next_step();
    }
    #[When('I want to pay for order')]
    public function i_want_to_pay_for_order(): void
    {
        $this->select_payment_page->try_to_open();
    }
    #[When('I am at the checkout payment step')]
    #[When('I go back to payment step of the checkout')]
    #[When('the customer is at the checkout payment step')]
    public function i_am_at_the_checkout_payment_step(): void
    {
        $this->select_payment_page->open();
    }
    #[When('/^I complete(?:|d) the payment step$/')]
    public function i_complete_the_payment_step(): void
    {
        $this->select_payment_page->next_step();
    }
    #[When('I select :paymentMethodName payment method')]
    public function i_select_payment_method(string $payment_method_name): void
    {
        $this->select_payment_page->select_payment_method($payment_method_name);
    }
    #[When('I do not select any payment method')]
    public function i_do_not_select_any_payment_method(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[Then('I should be on the checkout payment step')]
    public function i_should_be_on_the_checkout_payment_step(): void
    {
        $this->select_payment_page->verify();
    }
    #[Then('I should be able to select :paymentMethodName payment method')]
    public function i_should_be_able_to_select_payment_method(string $payment_method_name): void
    {
        Assert::true($this->select_payment_page->has_payment_method($payment_method_name));
    }
    #[Then('I should not be able to select :paymentMethodName payment method')]
    public function i_should_not_be_able_to_select_payment_method(string $payment_method_name): void
    {
        Assert::false($this->select_payment_page->has_payment_method($payment_method_name));
    }
    #[Then('I should be redirected to the payment step')]
    public function i_should_be_redirected_to_the_payment_step(): void
    {
        $this->select_payment_page->verify();
    }
    #[Then('I should be able to go to the summary page again')]
    public function i_should_be_able_to_go_to_the_summary_page_again(): void
    {
        $this->select_payment_page->next_step();
        $this->complete_page->verify();
    }
    #[Then('I should have :paymentMethodName payment method available as the first choice')]
    public function i_should_have_payment_method_available_as_first_choice(string $payment_method_name): void
    {
        $payment_methods = $this->select_payment_page->get_payment_methods();
        Assert::same(reset($payment_methods), $payment_method_name);
    }
    #[Then('I should have :paymentMethodName payment method available as the last choice')]
    public function i_should_have_payment_method_available_as_last_choice(string $payment_method_name): void
    {
        $payment_methods = $this->select_payment_page->get_payment_methods();
        Assert::same(end($payment_methods), $payment_method_name);
    }
    #[Then('I should not be able to proceed checkout payment step')]
    public function i_should_not_be_able_to_proceed_checkout_payment_step(): void
    {
        $this->select_payment_page->try_to_open();
        try {
            $this->select_payment_page->next_step();
        } catch (Element_Not_Found_Exception) {
            return;
        }
        throw new Unexpected_Page_Exception('It should not be possible to complete checkout payment step.');
    }
    #[Then('I should see :firstPaymentMethodName and :secondPaymentMethodName payment methods')]
    public function i_should_see_and_payment_methods(string ...$payment_methods_names): void
    {
        foreach ($payment_methods_names as $payment_method_name) {
            Assert::true($this->select_payment_page->has_payment_method($payment_method_name), sprintf('There is no %s payment method', $payment_method_name));
        }
    }
    #[Then('the customer should have checkout payment step completed')]
    #[Then('the visitor should have checkout payment step completed')]
    public function the_customer_should_have_checkout_payment_step_completed(): void
    {
        Assert::false($this->select_payment_page->is_open(), 'Customer should have checkout payment step completed, but it is not.');
    }
    #[Then('I should not see :firstPaymentMethodName and :secondPaymentMethodName payment methods')]
    public function i_should_not_see_and_payment_methods(string ...$payment_methods_names): void
    {
        foreach ($payment_methods_names as $payment_method_name) {
            Assert::false($this->select_payment_page->has_payment_method($payment_method_name), sprintf('There is %s payment method', $payment_method_name));
        }
    }
    #[Then('I should not be able to complete the payment step')]
    public function i_should_not_be_able_to_complete_the_payment_step(): void
    {
        Assert::true($this->select_payment_page->is_next_step_button_unavailable(), 'The "next step" button should be disabled, but it does not.');
    }
    #[Then('there should be information about no payment methods available for my order')]
    public function there_should_be_information_about_no_payment_methods_available_for_my_order(): void
    {
        Assert::true($this->select_payment_page->has_no_available_payment_methods_warning(), 'There should be warning about no available payment methods, but it does not.');
    }
}
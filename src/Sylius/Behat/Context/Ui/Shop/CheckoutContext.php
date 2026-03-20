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
namespace Sylius\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Friends_Of_Behat\Page_Object_Extension\Page\Unexpected_Page_Exception;
use Sylius\Behat\Context\Ui\Shop\Checkout\Checkout_Addressing_Context;
use Sylius\Behat\Context\Ui\Shop\Checkout\Checkout_Payment_Context;
use Sylius\Behat\Context\Ui\Shop\Checkout\Checkout_Shipping_Context;
use Sylius\Behat\Element\Shop\Account\Register_Element_Interface;
use Sylius\Behat\Page\Shop\Account\Register_Page_Interface;
use Sylius\Behat\Page\Shop\Checkout\Address_Page_Interface;
use Sylius\Behat\Page\Shop\Checkout\Complete_Page_Interface;
use Sylius\Behat\Page\Shop\Checkout\Select_Payment_Page_Interface;
use Sylius\Behat\Page\Shop\Checkout\Select_Shipping_Page_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Webmozart\Assert\Assert;
final readonly class Checkout_Context implements Context
{
    public function __construct(private Address_Page_Interface $address_page, private Select_Payment_Page_Interface $select_payment_page, private Select_Shipping_Page_Interface $select_shipping_page, private Complete_Page_Interface $complete_page, private Register_Page_Interface $register_page, private Register_Element_Interface $register_element, private Current_Page_Resolver_Interface $current_page_resolver, private Checkout_Addressing_Context $addressing_context, private Checkout_Shipping_Context $shipping_context, private Checkout_Payment_Context $payment_context, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Given('I was at the checkout summary step')]
    public function i_was_at_the_checkout_summary_step(): void
    {
        $this->addressing_context->i_specified_the_billing_address();
        $this->i_proceed_order_with_shipping_method_and_payment('Free', 'Offline');
    }
    #[Given('I have proceeded selecting :paymentMethodName payment method')]
    #[When('I proceed with selecting :paymentMethodName payment method')]
    public function i_proceed_selecting_payment_method(string $payment_method_name): void
    {
        $this->addressing_context->i_specified_the_billing_address();
        $this->shipping_context->i_complete_the_shipping_step();
        $this->payment_context->i_choose_payment_method($payment_method_name);
    }
    #[Given('I have proceeded order with :shippingMethodName shipping method and :paymentMethodName payment')]
    #[When('I proceed with :shippingMethodName shipping method and :paymentMethodName payment')]
    public function i_proceed_order_with_shipping_method_and_payment(string $shipping_method_name, string $payment_method_name): void
    {
        if (!$this->select_shipping_page->is_open()) {
            $this->select_shipping_page->open();
        }
        $this->select_shipping_page->select_shipping_method($shipping_method_name);
        $this->select_shipping_page->next_step();
        $this->select_payment_page->select_payment_method($payment_method_name ?: 'Offline');
        $this->select_payment_page->next_step();
    }
    #[When('I proceed through checkout process')]
    #[When('I proceed through checkout process in the :localeCode locale')]
    #[When('I proceed through checkout process in the :localeCode locale with email :email')]
    public function i_proceed_through_checkout_process(string $locale_code = 'en_US', ?string $email = null): void
    {
        $this->addressing_context->i_proceed_selecting_billing_country(null, $locale_code, $email);
        $this->shipping_context->i_complete_the_shipping_step();
        $this->payment_context->i_complete_the_payment_step();
    }
    #[When('I proceed through checkout with :shippingMethod shipping method')]
    public function i_have_proceeded_through_checkout_process_with_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $this->addressing_context->i_proceed_selecting_billing_country();
        $this->shipping_context->i_have_proceeded_with_selecting_shipping_method($shipping_method->get_name());
        if ($this->select_payment_page->is_open()) {
            $this->payment_context->i_complete_the_payment_step();
        }
        $this->shared_storage->set('shipping_method', $shipping_method);
    }
    #[When('I proceed with selecting :shippingMethodName shipping method')]
    public function i_proceed_with_selecting_shipping_method(string $shipping_method_name): void
    {
        $this->addressing_context->i_proceed_selecting_billing_country();
        $this->shipping_context->i_have_proceeded_with_selecting_shipping_method($shipping_method_name);
    }
    #[When('I go to the addressing step')]
    public function i_go_to_the_addressing_step(): void
    {
        if ($this->select_shipping_page->is_open()) {
            $this->select_shipping_page->change_address_by_step_label();
            return;
        }
        if ($this->select_payment_page->is_open()) {
            $this->select_payment_page->change_address_by_step_label();
            return;
        }
        if ($this->complete_page->is_open()) {
            $this->complete_page->change_address();
            return;
        }
        throw new Unexpected_Page_Exception('It is impossible to go to addressing step from current page.');
    }
    #[Then('the subtotal of :item item should be :price')]
    public function the_subtotal_of_item_should_be($item, $price): void
    {
        /** @var AddressPageInterface|SelectPaymentPageInterface|SelectShippingPageInterface|CompletePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->address_page, $this->select_payment_page, $this->select_shipping_page, $this->complete_page]);
        Assert::eq($current_page->get_item_subtotal($item), $price);
    }
    #[Then('I should not be able to change email')]
    public function i_should_not_be_able_to_change_email(): void
    {
        Assert::false($this->address_page->has_email_input());
    }
    #[When('I register with previously used :email email and :password password')]
    public function i_register_with_previously_used_email_and_password(string $email, string $password): void
    {
        $this->register_page->open();
        $this->register_element->specify_email($email);
        $this->register_element->specify_password($password);
        $this->register_element->verify_password($password);
        $this->register_element->specify_first_name('Carrot');
        $this->register_element->specify_last_name('Ironfoundersson');
        $this->register_element->register();
    }
}
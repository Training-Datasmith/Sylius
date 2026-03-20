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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Page\Shop\Order\Show_Page_Interface;
use Sylius\Behat\Page\Shop\Order\Thank_You_Page_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Webmozart\Assert\Assert;
final readonly class Checkout_Order_Details_Context implements Context
{
    public function __construct(private Show_Page_Interface $order_details, private Thank_You_Page_Interface $thank_you_page)
    {
    }
    #[When('/^I want to browse order details for (this order)$/')]
    public function i_want_to_browse_order_details_for_this_order(Order_Interface $order): void
    {
        $this->order_details->open(['tokenValue' => $order->get_token_value()]);
    }
    #[When('I try to pay with :paymentMethodName payment method')]
    public function i_change_payment_method_to(string $payment_method_name): void
    {
        $this->order_details->choose_payment_method($payment_method_name);
        $this->order_details->pay();
    }
    #[When('I retry the payment with :paymentMethodName payment method')]
    public function i_change_payment_method_after_checkout(string $payment_method_name): void
    {
        $this->thank_you_page->go_to_the_change_payment_method_page();
        $this->order_details->choose_payment_method($payment_method_name);
        $this->order_details->pay();
    }
    #[When('I want to pay for my order')]
    public function i_want_to_pay_for_my_order(): void
    {
        $this->thank_you_page->go_to_the_change_payment_method_page();
    }
    #[When('I try to pay for my order')]
    public function i_try_to_pay_for_my_order(): void
    {
        $this->thank_you_page->go_to_the_change_payment_method_page();
        $this->order_details->pay();
    }
    #[Then('I should be able to pay (again)')]
    public function i_should_be_able_to_pay(): void
    {
        Assert::true($this->order_details->has_pay_action());
    }
    #[Then('I should not be able to pay (again)')]
    public function i_should_not_be_able_to_pay(): void
    {
        Assert::false($this->order_details->can_be_paid());
    }
    #[Then('I should see :quantity as number of items')]
    public function i_should_see_as_number_of_items(int $quantity): void
    {
        Assert::same($this->order_details->get_amount_of_items(), $quantity);
    }
    #[Then('I should have chosen :paymentMethodName payment method')]
    public function i_should_have_chosen_payment_method(string $payment_method_name): void
    {
        $this->thank_you_page->go_to_the_change_payment_method_page();
        Assert::same($this->order_details->get_chosen_payment_method(), $payment_method_name);
    }
    #[Then('I should be notified to choose a payment method')]
    public function i_should_be_notified_to_choose_a_payment_method(): void
    {
        Assert::contains($this->order_details->get_payment_validation_message(), 'Please select a payment method.');
    }
}
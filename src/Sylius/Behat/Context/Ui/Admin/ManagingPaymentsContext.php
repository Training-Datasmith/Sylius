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
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Order\Show_Page_Interface;
use Sylius\Behat\Page\Admin\Payment\Index_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Payments_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page, private Show_Page_Interface $order_show_page, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[Given('I am browsing payments')]
    #[When('I browse payments')]
    public function i_am_browsing_payments(): void
    {
        $this->index_page->open();
    }
    #[When('I choose :paymentState as a payment state')]
    public function i_choose_as_a_payment_state(string $payment_state): void
    {
        $this->index_page->choose_state_to_filter($payment_state);
    }
    #[When('I complete the payment of order :orderNumber')]
    public function i_complete_the_payment_of_order(string $order_number): void
    {
        $this->index_page->complete_payment_of_order_with_number($order_number);
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->index_page->filter();
    }
    #[When('I go to the details of the first payment\'s order')]
    public function i_go_to_the_details_of_the_first_payment_s_order(): void
    {
        $this->index_page->show_order_page_for_nth_payment(1);
    }
    #[When('I choose :channelName as a channel filter')]
    public function i_choose_channel_as_a_channel_filter(string $channel_name): void
    {
        $this->index_page->choose_channel_filter($channel_name);
    }
    #[When('I want to view the payment requests of the first payment')]
    public function i_want_to_view_the_payment_requests_of_the_first_payment(): void
    {
        $this->index_page->show_payment_request_of_nth_payment(1);
    }
    #[Then('I should see :count payments in the list')]
    #[Then('I should see a single payment in the list')]
    public function i_should_see_payments_in_the_list(int $count = 1): void
    {
        Assert::same($count, $this->index_page->count_items());
    }
    #[Then('the payment of the :orderNumber order should be :paymentState for :customer')]
    public function the_payment_of_the_order_should_be_for(string $order_number, string $payment_state, Customer_Interface $customer): void
    {
        $parameters = ['number' => $order_number, 'state' => $payment_state, 'customer' => $customer->get_email()];
        Assert::true($this->index_page->is_single_resource_on_page($parameters));
    }
    #[Then('I should see order page with details of order :order')]
    #[Then('I should see the details of order :order')]
    public function i_should_see_order_page_with_details_of_order(Order_Interface $order): void
    {
        Assert::true($this->order_show_page->is_open(['id' => $order->get_id()]));
    }
    #[Then('I should see (also) the payment of the :orderNumber order')]
    public function i_should_see_the_payment_of_the_order(string $order_number): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['number' => $order_number]));
    }
    #[Then('I should see the payment of order :orderNumber as :paymentState')]
    public function i_should_see_the_payment_of_order_as(string $order_number, string $payment_state): void
    {
        Assert::same($payment_state, $this->index_page->get_payment_state_by_order_number($order_number));
    }
    #[Then('I should be notified that the payment has been completed')]
    public function i_should_be_notified_that_the_payment_has_been_completed(): void
    {
        $this->notification_checker->check_notification('Payment has been completed.', Notification_Type::success());
    }
    #[Then('I should not see a payment of order :orderNumber')]
    #[Then('I should not see the payment of the :orderNumber order')]
    public function i_should_not_see_a_payment_of_order(string $order_number): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['number' => $order_number]));
    }
    #[Then('/^I should see payment for (the "[^"]+" order) as (\d+)(?:|st|nd|rd|th) in the list$/')]
    public function i_should_see_payment_for_the_order_in_the_list(string $order_number, int $position): void
    {
        Assert::true($this->index_page->is_payment_with_order_number_in_position($order_number, $position));
    }
    #[When('/^I sort payments by date in (ascending|descending) order$/')]
    public function i_sort_payments_by_registration_date(string $order): void
    {
        $this->sort_by($order, 'createdAt');
    }
    private function sort_by(string $order, string $field): void
    {
        $this->index_page->sort_by($field, str_starts_with($order, 'de') ? 'desc' : 'asc');
    }
}
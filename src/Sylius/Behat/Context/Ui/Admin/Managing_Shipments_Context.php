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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Order\Show_Page_Interface as OrderShowPageInterface;
use Sylius\Behat\Page\Admin\Shipment\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Shipment\Show_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Shipments_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page, private Order_Show_Page_Interface $order_show_page, private Notification_Checker_Interface $notification_checker, private Show_Page_Interface $show_page)
    {
    }
    #[When('I browse shipments')]
    public function i_browse_shipments(): void
    {
        $this->index_page->open();
    }
    #[Then('the shipment of the :orderNumber order should be :shippingState for :customer')]
    #[Then('the shipment of the :orderNumber order should be :shippingState for :customer in :channel channel')]
    public function shipment_of_order_should_be(string $order_number, string $shipping_state, Customer_Interface $customer, ?Channel $channel = null): void
    {
        $parameters = ['order' => $order_number, 'state' => $shipping_state, 'customer' => $customer->get_email()];
        if ($channel !== null) {
            $parameters = ['channel' => $channel->get_name()];
        }
        Assert::true($this->index_page->is_single_resource_on_page($parameters));
    }
    #[When('I choose :shipmentState as a shipment state')]
    public function i_choose_shipment_state(string $shipment_state): void
    {
        $this->index_page->choose_state_to_filter($shipment_state);
    }
    #[When('I choose :channelName as a channel filter')]
    public function i_choose_channel_as_a_channel_filter(string $channel_name): void
    {
        $this->index_page->choose_channel_filter($channel_name);
    }
    #[When('I choose :shippingMethodName as a shipping method filter')]
    public function i_choose_as_a_shipping_method_filter(string $shipping_method_name): void
    {
        $this->index_page->choose_shipping_method_filter($shipping_method_name);
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->index_page->filter();
    }
    #[When('I view the first shipment of the order :order')]
    public function i_view_the_shipment_of_the_order(Order_Interface $order): void
    {
        $this->show_page->open(['id' => $order->get_shipments()->first()->get_id()]);
    }
    #[Then('I should see( only) :count shipment(s) in the list')]
    #[Then('I should see a single shipment in the list')]
    public function i_should_see_count_shipments_in_list(int $count = 1): void
    {
        Assert::same($this->index_page->count_items(), $count);
    }
    #[Then('I should see a shipment of order :orderNumber')]
    public function i_should_see_shipment_with_order_number(string $order_number): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['order' => $order_number]));
    }
    #[Then('I should not see a shipment of order :orderNumber')]
    public function i_should_not_see_shipment_with_order_number(string $order_number): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['order' => $order_number]));
    }
    #[When('I ship the shipment of order :orderNumber')]
    public function i_ship_shipment_of_order(string $order_number): void
    {
        $this->index_page->ship_shipment_of_order_with_number($order_number);
    }
    #[Then('I should see the shipment of order :orderNumber as :shippingState')]
    public function i_should_see_the_shipment_of_order_as(string $order_number, string $shipping_state): void
    {
        Assert::same($shipping_state, $this->index_page->get_shipment_status_by_order_number($order_number));
    }
    #[Then('I should be notified that the shipment has been successfully shipped')]
    public function i_should_be_notified_that_the_shipment_has_been_successfully_shipped(): void
    {
        $this->notification_checker->check_notification('Shipment has been successfully shipped.', Notification_Type::success());
    }
    #[When('I move to the details of first shipment\'s order')]
    public function i_move_to_details_of_first_shipment(): void
    {
        $this->index_page->show_order_page_for_nth_shipment(1);
    }
    #[When('I ship the shipment of order :orderNumber with :trackingCode tracking code')]
    public function i_ship_the_shipment_of_order_with_tracking_code(string $order_number, string $tracking_code): void
    {
        $this->index_page->ship_shipment_of_order_with_tracking_code($order_number, $tracking_code);
    }
    #[Then('I should see order page with details of order :order')]
    #[Then('I should see the details of order :order')]
    public function i_should_see_order_page_with_details_of_order(Order_Interface $order): void
    {
        Assert::true($this->order_show_page->is_open(['id' => $order->get_id()]));
    }
    #[Then('/^I should see shipment for (the "[^"]+" order) as (\d+)(?:|st|nd|rd|th) in the list$/')]
    public function i_should_see_shipment_for_the_order_in_the_list(string $order_number, int $position): void
    {
        Assert::true($this->index_page->is_shipment_with_order_number_in_position($order_number, $position));
    }
    #[Then('I should see :amount :product units in the list')]
    public function i_should_see_units_in_the_list(int $amount, string $product_name): void
    {
        Assert::same($this->show_page->get_amount_of_units($product_name), $amount);
    }
    #[Then('I should see the shipment of order :orderNumber shipped at :dateTime')]
    public function i_should_see_the_shipping_date_as(string $order_number, string $date_time): void
    {
        Assert::same($this->index_page->get_shipped_at_date($order_number), $date_time);
    }
    #[Then('I should see the shipment state as :shipmentState')]
    public function i_should_see_the_shipment_state_as(string $shipment_state): void
    {
        Assert::same($this->show_page->get_state(), $shipment_state);
    }
}
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
use Friends_Of_Behat\Page_Object_Extension\Page\Unexpected_Page_Exception;
use Sylius\Behat\Page\Admin\Dashboard_Page_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Webmozart\Assert\Assert;
final readonly class Dashboard_Context implements Context
{
    public function __construct(private Dashboard_Page_Interface $dashboard_page)
    {
    }
    #[Given('I am on the administration dashboard')]
    #[When('I (try to )open administration dashboard')]
    #[When('I (try to )view statistics')]
    public function i_view_statistics(): void
    {
        try {
            $this->dashboard_page->open();
        } catch (Unexpected_Page_Exception) {
        }
    }
    /**
     * @throws UnexpectedPageException
     */
    #[When('I view statistics for :channel channel')]
    public function i_view_statistics_for_channel(Channel_Interface $channel): void
    {
        $this->dashboard_page->open(['channel' => $channel->get_code()]);
    }
    /**
     * @throws UnexpectedPageException
     */
    #[When('/^I view statistics for ("[^"]+" channel) and (current|previous|next) year split by (month|day)$/')]
    public function i_view_statistics_for_channel_and_year(Channel_Interface $channel, string $period, string $interval): void
    {
        if (!$this->dashboard_page->is_open(['channel' => $channel->get_code()])) {
            $this->dashboard_page->open(['channel' => $channel->get_code()]);
        }
        match ($interval) {
            'month' => $this->dashboard_page->choose_year_split_by_months_interval(),
            'day' => $this->dashboard_page->choose_month_split_by_days_interval(),
            default => throw new \InvalidArgumentException(sprintf('Interval "%s" is not supported.', $interval)),
        };
        match ($period) {
            'previous' => $this->dashboard_page->choose_previous_period(),
            'next' => $this->dashboard_page->choose_next_period(),
            default => null,
        };
    }
    /**
     * @throws UnexpectedPageException
     */
    #[When('/^I view statistics for ("[^"]+" channel) and (previous|next) year$/')]
    public function i_view_statistics_for_previous_period(Channel_Interface $channel, string $period): void
    {
        if (!$this->dashboard_page->is_open(['channel' => $channel->get_code()])) {
            $this->dashboard_page->open(['channel' => $channel->get_code()]);
        }
        match ($period) {
            'previous' => $this->dashboard_page->choose_previous_period(),
            'next' => $this->dashboard_page->choose_next_period(),
            default => null,
        };
    }
    #[When('I choose :channelName channel')]
    public function i_choose_channel(string $channel_name): void
    {
        $this->dashboard_page->choose_channel($channel_name);
    }
    #[When('I search for product :product via the navbar')]
    public function i_search_for_product_via_the_navbar(Product_Interface $product): void
    {
        $this->dashboard_page->search_for_product_via_navbar($product);
    }
    #[When('I log out')]
    public function i_log_out(): void
    {
        $this->dashboard_page->log_out();
    }
    #[Then('I should see :number paid orders')]
    public function i_should_see_paid_orders(int $number): void
    {
        Assert::same($this->dashboard_page->get_number_of_paid_orders(), $number);
    }
    #[Then('I should see :number new customers')]
    public function i_should_see_new_customers(int $number): void
    {
        Assert::same($this->dashboard_page->get_number_of_new_customers(), $number);
    }
    #[Then('there should be total sales of :total')]
    public function there_should_be_total_sales_of(string $total): void
    {
        Assert::same($this->dashboard_page->get_total_sales(), $total);
    }
    #[Then('the average order value should be :value')]
    public function my_average_order_value_should_be(string $value): void
    {
        Assert::same($this->dashboard_page->get_average_order_value(), $value, 'Expected average order value to be equal to %2$s, but it is %s.');
    }
    #[Then('I should see :number new customers in the list')]
    public function i_should_see_new_customers_in_the_list(int $number): void
    {
        Assert::same($this->dashboard_page->get_number_of_new_customers_in_the_list(), $number);
    }
    #[Then('I should see :number new orders in the list')]
    public function i_should_see_new_orders_in_the_list(int $number): void
    {
        Assert::same($this->dashboard_page->get_number_of_new_orders_in_the_list(), $number);
    }
    #[Then('I should not see the administration dashboard')]
    public function i_should_not_see_the_administration_dashboard(): void
    {
        Assert::false($this->dashboard_page->is_open());
    }
    #[Then('I should see :count order(s) to process in the pending actions')]
    public function i_should_see_orders_to_process_in_the_pending_actions(int $count): void
    {
        Assert::same($this->dashboard_page->get_number_of_orders_to_process(), $count);
    }
    #[Then('I should see :count shipment(s) to ship in the pending actions')]
    public function i_should_see_shipments_to_ship_in_the_pending_actions(int $count): void
    {
        Assert::same($this->dashboard_page->get_number_of_shipments_to_ship(), $count);
    }
    #[Then('I should see :count pending payment(s) in the pending actions')]
    public function i_should_see_pending_payments_in_the_pending_actions(int $count): void
    {
        Assert::same($this->dashboard_page->get_number_of_pending_payments(), $count);
    }
    #[Then('I should see :count product review(s) to approve in the pending actions')]
    public function i_should_see_product_reviews_to_approve_in_the_pending_actions(int $count): void
    {
        Assert::same($this->dashboard_page->get_number_of_product_reviews_to_approve(), $count);
    }
    #[Then('I should see :count product variant(s) out of stock in the pending actions')]
    public function i_should_see_product_variants_out_of_stock_in_the_pending_actions(int $count): void
    {
        Assert::same($this->dashboard_page->get_number_of_product_variants_out_of_stock(), $count);
    }
}
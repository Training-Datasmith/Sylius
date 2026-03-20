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
namespace Sylius\Behat\Page\Admin;

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Mink\Session;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Symfony\Component\Routing\Router_Interface;
class Dashboard_Page extends Sylius_Page implements Dashboard_Page_Interface
{
    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param array<TKey, TValue>|\ArrayAccess<TKey, TValue> $minkParameters
     */
    public function __construct(Session $session, array|\ArrayAccess $mink_parameters, Router_Interface $router, protected Table_Accessor_Interface $table_accessor)
    {
    }
    /** @throws ElementNotFoundException */
    public function get_total_sales(): string
    {
        return $this->get_element('total_sales')->get_text();
    }
    /** @throws ElementNotFoundException */
    public function get_number_of_paid_orders(): int
    {
        return (int) $this->get_element('paid_orders')->get_text();
    }
    /** @throws ElementNotFoundException */
    public function get_number_of_new_orders_in_the_list(): int
    {
        $this->wait_for_element('order_list');
        return $this->table_accessor->count_table_body_rows($this->get_element('order_list'));
    }
    /** @throws ElementNotFoundException */
    public function get_number_of_new_customers(): int
    {
        return (int) $this->get_element('new_customers')->get_text();
    }
    /** @throws ElementNotFoundException */
    public function get_number_of_new_customers_in_the_list(): int
    {
        $this->wait_for_element('customer_list');
        return $this->table_accessor->count_table_body_rows($this->get_element('customer_list'));
    }
    /** @throws ElementNotFoundException */
    public function get_average_order_value(): string
    {
        return $this->get_element('average_order_value')->get_text();
    }
    public function get_dashboard_header(): string
    {
        return $this->get_element('dashboard_header')->get_text();
    }
    /** @throws ElementNotFoundException */
    public function log_out(): void
    {
        $this->get_element('logout')->click();
    }
    /** @throws ElementNotFoundException */
    public function choose_channel(string $channel_name): void
    {
        $this->get_element('channel_choosing_button')->click();
        $this->get_element('channel_choosing_list', ['%channelName%' => $channel_name])->click();
        $this->wait_for_statistics_update();
    }
    /** @throws ElementNotFoundException */
    public function choose_year_split_by_months_interval(): void
    {
        $this->get_element('year_split_by_months_statistics_button')->click();
        $this->wait_for_statistics_update();
    }
    /** @throws ElementNotFoundException */
    public function choose_month_split_by_days_interval(): void
    {
        $this->get_element('month_split_by_days_statistics_button')->click();
    }
    /** @throws ElementNotFoundException */
    public function choose_previous_period(): void
    {
        $this->get_element('previous_period')->click();
        $this->wait_for_statistics_update();
    }
    /** @throws ElementNotFoundException */
    public function choose_next_period(): void
    {
        $this->get_element('next_period')->click();
        $this->wait_for_statistics_update();
    }
    public function search_for_product_via_navbar(Product_Interface $product_name): void
    {
        $form = $this->get_element('product_navbar_search');
        $form->find('css', 'input')->set_value($product_name);
        $form->find('css', 'button')->click();
    }
    public function get_number_of_orders_to_process(): int
    {
        $this->wait_for_element('orders_to_process');
        return (int) $this->get_element('orders_to_process_count')->get_text();
    }
    public function get_number_of_pending_payments(): int
    {
        $this->wait_for_element('pending_payments');
        return (int) $this->get_element('pending_payments_count')->get_text();
    }
    public function get_number_of_product_reviews_to_approve(): int
    {
        $this->wait_for_element('product_reviews_to_approve');
        return (int) $this->get_element('product_reviews_to_approve_count')->get_text();
    }
    public function get_number_of_product_variants_out_of_stock(): int
    {
        $this->wait_for_element('product_variants_out_of_stock');
        return (int) $this->get_element('product_variants_out_of_stock_count')->get_text();
    }
    public function get_number_of_shipments_to_ship(): int
    {
        $this->wait_for_element('shipments_to_ship');
        return (int) $this->get_element('shipments_to_ship_count')->get_text();
    }
    public function get_route_name(): string
    {
        return 'sylius_admin_dashboard';
    }
    /** @return array<string, string> */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['average_order_value' => '[data-test-average-order-value]', 'channel_choosing_button' => '[data-test-choose-channel-button]', 'channel_choosing_list' => '[data-test-choose-channel-list] a:contains("%channelName%")', 'customer_list' => '#customers', 'dashboard_header' => '[data-test-dashboard-header]', 'dropdown' => 'i.dropdown', 'logout' => '[data-test-user-dropdown-item="Logout"]', 'month_split_by_days_statistics_button' => 'button[data-stats-button="month"]', 'new_customers' => '[data-test-new-customers]', 'next_period' => '[data-test-next-period]', 'order_list' => '[data-test-new-orders]', 'orders_to_process' => '[data-test-orders-to-process]', 'orders_to_process_count' => '[data-test-orders-to-process-count]', 'paid_orders' => '[data-test-paid-orders]', 'pending_payments' => '[data-test-pending-payments]', 'pending_payments_count' => '[data-test-pending-payments-count]', 'product_reviews_to_approve' => '[data-test-product-reviews-to-approve]', 'product_reviews_to_approve_count' => '[data-test-product-reviews-to-approve-count]', 'product_variants_out_of_stock' => '[data-test-product-variants-out-of-stock]', 'product_variants_out_of_stock_count' => '[data-test-product-variants-out-of-stock-count]', 'previous_period' => '[data-test-previous-period]', 'product_navbar_search' => '[data-test-navbar-product-search]', 'shipments_to_ship' => '[data-test-shipments-to-ship]', 'shipments_to_ship_count' => '[data-test-shipments-to-ship-count]', 'statistics_component' => '[data-test-statistics-component]', 'total_sales' => '[data-test-total-sales]', 'year_split_by_months_statistics_button' => '[data-test-year-split-into-months]']);
    }
    protected function wait_for_statistics_update(): void
    {
        sleep(1);
        // we need to sleep, as sometimes the check below is executed faster than the form sets the busy attribute
        $live_element = $this->get_element('statistics_component');
        $live_element->wait_for(2500, fn(): bool => !$live_element->has_attribute('busy'));
    }
    private function wait_for_element(string $element): void
    {
        $live_element = $this->get_element($element);
        $live_element->wait_for(2500, fn(): bool => !$live_element->has_attribute('busy'));
    }
}
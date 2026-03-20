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
namespace Sylius\Behat\Page\Admin\Order;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Session;
use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Routing\Router_Interface;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, Table_Accessor_Interface $table_accessor, string $route_name, protected Autocomplete_Helper_Interface $autocomplete_helper)
    {
        parent::__construct($session, $mink_parameters, $router, $table_accessor, $route_name);
    }
    public function specify_filter_date_from(string $date_time): void
    {
        $date_and_time = explode(' ', $date_time);
        $this->get_document()->fill_field('criteria_date_from_date', $date_and_time[0]);
        $this->get_document()->fill_field('criteria_date_from_time', $date_and_time[1] ?? '');
    }
    public function specify_filter_date_to(string $date_time): void
    {
        $date_and_time = explode(' ', $date_time);
        $this->get_document()->fill_field('criteria_date_to_date', $date_and_time[0]);
        $this->get_document()->fill_field('criteria_date_to_time', $date_and_time[1] ?? '');
    }
    public function specify_filter_channel(string $channel_name): void
    {
        $this->specify_autocomplete_filter($this->get_element('filter_channel'), $channel_name);
    }
    public function specify_filter_shipping_method(string $method_name): void
    {
        $this->specify_autocomplete_filter($this->get_element('filter_shipping_method'), $method_name);
    }
    public function choose_filter_currency(string $currency_name): void
    {
        $this->get_element('filter_currency')->select_option($currency_name);
    }
    public function specify_filter_total_greater_than(string $total): void
    {
        $this->get_document()->fill_field('criteria_total_greaterThan', $total);
    }
    public function specify_filter_total_less_than(string $total): void
    {
        $this->get_document()->fill_field('criteria_total_lessThan', $total);
    }
    public function specify_filter_product(string $product_name): void
    {
        $this->specify_autocomplete_filter($this->get_element('filter_product'), $product_name);
    }
    public function specify_filter_variant(string $variant_name): void
    {
        $this->specify_autocomplete_filter($this->get_element('filter_variant'), $variant_name);
    }
    public function specify_filter_customer(string $customer_name): void
    {
        $this->specify_autocomplete_filter($this->get_element('filter_customer'), $customer_name);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['filter_channel' => '#criteria_channel', 'filter_currency' => '#criteria_total_currency', 'filter_customer' => '#criteria_customer', 'filter_product' => '#criteria_product', 'filter_shipping_method' => '#criteria_shipping_method', 'filter_variant' => '#criteria_variant']);
    }
    protected function specify_autocomplete_filter(Node_Element $autocomplete, string $value): void
    {
        if (!$this->are_filters_visible()) {
            $this->toggle_filters();
        }
        $this->autocomplete_helper->select_by_name($this->get_driver(), $autocomplete->get_xpath(), $value);
        $this->wait_for_form_update();
    }
}
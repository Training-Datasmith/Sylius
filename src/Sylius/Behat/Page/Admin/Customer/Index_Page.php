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
namespace Sylius\Behat\Page\Admin\Customer;

use Behat\Mink\Session;
use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Sylius\Component\Customer\Model\Customer_Interface;
use Symfony\Component\Routing\Router_Interface;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, Table_Accessor_Interface $table_accessor, string $route_name, protected Autocomplete_Helper_Interface $autocomplete_helper)
    {
        parent::__construct($session, $mink_parameters, $router, $table_accessor, $route_name);
    }
    public function is_customer_enabled(Customer_Interface $customer): bool
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $row = $table_accessor->get_row_with_fields($table, ['email' => $customer->get_email()]);
        $enabled_field = $table_accessor->get_field_from_row($table, $row, 'enabled');
        return $enabled_field->has('css', '[data-test-status-enabled]') ? true : false;
    }
    public function is_customer_verified(Customer_Interface $customer): bool
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $row = $table_accessor->get_row_with_fields($table, ['email' => $customer->get_email()]);
        $verified_field = $table_accessor->get_field_from_row($table, $row, 'verified');
        return $verified_field->has('css', '[data-test-status-enabled]') ? true : false;
    }
    public function set_filter_group(string $group_name): void
    {
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('filter_group')->get_xpath(), $group_name);
        $this->wait_for_form_update();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['filter_group' => '#criteria_group']);
    }
}
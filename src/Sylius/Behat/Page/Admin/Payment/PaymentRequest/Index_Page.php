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
namespace Sylius\Behat\Page\Admin\Payment\Payment_Request;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Session;
use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Routing\Router_Interface;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, Table_Accessor_Interface $table_accessor, string $route_name, protected readonly Autocomplete_Helper_Interface $autocomplete_helper)
    {
        parent::__construct($session, $mink_parameters, $router, $table_accessor, $route_name);
    }
    public function choose_payment_method_to_filter(string $payment_method_name): void
    {
        $this->specify_autocomplete_filter($this->get_element('filter_payment_method'), $payment_method_name);
    }
    public function choose_action_to_filter(string $action): void
    {
        $this->get_element('filter_action')->select_option($action);
    }
    public function choose_state_to_filter(string $state): void
    {
        $this->get_element('filter_state')->select_option($state);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['filter_action' => '#criteria_action', 'filter_payment_method' => '#criteria_payment_method', 'filter_state' => '#criteria_state']);
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
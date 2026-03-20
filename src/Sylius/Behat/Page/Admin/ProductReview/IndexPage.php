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
namespace Sylius\Behat\Page\Admin\Product_Review;

use Behat\Mink\Session;
use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Routing\Router_Interface;
use Webmozart\Assert\Assert;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function __construct(Session $session, array|\ArrayAccess $mink_parameters, Router_Interface $router, Table_Accessor_Interface $table_accessor, string $route_name, protected Autocomplete_Helper_Interface $autocomplete_helper)
    {
        parent::__construct($session, $mink_parameters, $router, $table_accessor, $route_name);
    }
    public function accept(array $parameters): void
    {
        $this->change_state('accept', $parameters);
    }
    public function reject(array $parameters): void
    {
        $this->change_state('reject', $parameters);
    }
    public function filter_by_state(string $state): void
    {
        $this->get_element('state_filter')->select_option($state);
    }
    public function filter_by_title(string $phrase): void
    {
        $this->get_element('title_filter')->set_value($phrase);
    }
    public function filter_by_product(string $product_name): void
    {
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('product_filter')->get_xpath(), $product_name);
        $this->wait_for_form_update();
    }
    protected function change_state(string $state, array $parameters): void
    {
        $action = $this->get_actions_for_resource($parameters)->find('css', sprintf('[data-test-action="%s"]', $state));
        Assert::not_null($action, sprintf('There is no "%s" action available for this resource', $state));
        $action->find('css', 'button')->click();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['product_filter' => '#criteria_product', 'state_filter' => '#criteria_status', 'title_filter' => '#criteria_title_value']);
    }
}
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
namespace Sylius\Behat\Page\Admin\Inventory;

use Behat\Mink\Session;
use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Routing\Router_Interface;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function __construct(Session $session, array|\ArrayAccess $mink_parameters, Router_Interface $router, Table_Accessor_Interface $table_accessor, string $route_name, protected Autocomplete_Helper_Interface $autocomplete_helper)
    {
        parent::__construct($session, $mink_parameters, $router, $table_accessor, $route_name);
    }
    public function specify_filter_type(string $field, string $type): void
    {
        $this->get_document()->fill_field(sprintf('criteria_%s_value', $field), $type);
    }
    public function specify_filter_value(string $field, string $value): void
    {
        $this->get_document()->fill_field(sprintf('criteria_%s_value', $field), $value);
    }
    public function filter_by_product(string $product_name): void
    {
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('product_filter')->get_xpath(), $product_name);
        $this->wait_for_form_update();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['product_filter' => '#criteria_product']);
    }
}
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
namespace Sylius\Behat\Page\Admin\Product;

use Behat\Mink\Session;
use Sylius\Behat\Page\Admin\Crud\Index_Page as CrudIndexPage;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Behat\Service\Checker\Image_Existence_Checker_Interface;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Routing\Router_Interface;
class Index_Page extends Crud_Index_Page implements Index_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, Table_Accessor_Interface $table_accessor, string $route_name, protected Image_Existence_Checker_Interface $image_existence_checker, protected Autocomplete_Helper_Interface $autocomplete_helper)
    {
        parent::__construct($session, $mink_parameters, $router, $table_accessor, $route_name);
    }
    public function filter(): void
    {
        $this->get_element('filter')->press();
    }
    public function filter_by_taxon(string $taxon_name): void
    {
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('taxon_filter')->get_xpath(), $taxon_name);
        $this->wait_for_form_update();
    }
    public function filter_by_main_taxon(string $taxon_name): void
    {
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('main_taxon_filter')->get_xpath(), $taxon_name);
        $this->wait_for_form_update();
    }
    public function choose_channel_filter(string $channel_name): void
    {
        $this->get_element('channel_filter')->select_option($channel_name);
    }
    public function has_product_accessible_image(string $product_code): bool
    {
        $product_row = $this->get_table_accessor()->get_row_with_fields($this->get_element('table'), ['code' => $product_code]);
        $image_url = $product_row->find('css', 'img')->get_attribute('src');
        return $this->image_existence_checker->does_image_with_url_exist($image_url, 'sylius_admin_product_thumbnail');
    }
    public function show_product_page(string $product_name): void
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $row = $table_accessor->get_row_with_fields($table, ['name' => $product_name]);
        $field = $table_accessor->get_field_from_row($table, $row, 'actions');
        $field->find('css', '[data-test-show-action="Details"]')->click();
    }
    public function check_first_product_has_data_attribute(string $attribute_name): bool
    {
        return $this->get_element('first_product')->find('css', sprintf('[%s]', $attribute_name)) !== null;
    }
    public function check_last_product_has_data_attribute(string $attribute_name): bool
    {
        return $this->get_element('last_product')->find('css', sprintf('[%s]', $attribute_name)) !== null;
    }
    /** @return array<string, string> */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['channel_filter' => '#criteria_channel', 'enabled_filter' => '#criteria_enabled', 'first_product' => '.table > tbody > tr:first-child', 'last_product' => '.table > tbody > tr:last-child', 'main_taxon_filter' => '#criteria_main_taxon', 'taxon_filter' => '#criteria_taxon']);
    }
}
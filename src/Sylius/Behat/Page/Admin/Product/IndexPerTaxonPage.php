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

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Page\Admin\Crud\Index_Page as CrudIndexPage;
use Sylius\Behat\Service\Driver_Helper;
use Webmozart\Assert\Assert;
class Index_Per_Taxon_Page extends Crud_Index_Page implements Index_Per_Taxon_Page_Interface
{
    public function get_product_position(string $product_name): int
    {
        /** @var NodeElement $productsRow */
        $products_row = $this->get_element('table')->find('css', sprintf('tbody > tr:contains("%s")', $product_name));
        Assert::not_null($products_row, 'There are no row with given product\'s name!');
        return (int) $products_row->find('css', '.sylius-product-taxon-position')->get_value();
    }
    public function has_products_in_order(array $product_names): bool
    {
        $products_on_page = $this->get_column_fields('name');
        foreach ($products_on_page as $key => $product) {
            if ($product_names[$key] !== $product) {
                return false;
            }
        }
        return true;
    }
    public function set_position_of_product(string $product_name, string $position): void
    {
        /** @var NodeElement $productsRow */
        $products_row = $this->get_element('table')->find('css', sprintf('tbody > tr:contains("%s")', $product_name));
        Assert::not_null($products_row, 'There are no row with given product\'s name!');
        $products_row->find('css', '.sylius-product-taxon-position')->set_value($position);
    }
    public function save_positions(): void
    {
        $save_configuration_button = $this->get_element('save_configuration_button');
        $save_configuration_button->press();
        $this->get_document()->wait_for(5, fn(): bool => null === $save_configuration_button->find('css', '.loading'));
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    public function filter_by_name(string $name): void
    {
        $this->get_element('name_filter')->set_value($name);
    }
    /** @return array<string, string> */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['name_filter' => '#criteria_search_value', 'save_configuration_button' => '[data-test-save-configuration-button]']);
    }
}
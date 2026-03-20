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
namespace Sylius\Behat\Element\Admin\Product;

use Behat\Mink\Session;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Product\Model\Product_Association_Type_Interface;
class Associations_Form_Element extends Base_Form_Element implements Associations_Form_Element_Interface
{
    public function __construct(Session $session, $mink_parameters, protected readonly Autocomplete_Helper_Interface $autocomplete_helper)
    {
    }
    public function associate_products(Product_Association_Type_Interface $product_association_type, array $products_names): void
    {
        $this->change_tab();
        $association_field = $this->get_element('associations', ['%association%' => $product_association_type->get_code()]);
        foreach ($products_names as $product_name) {
            $this->autocomplete_helper->select_by_name($this->get_driver(), $association_field->get_xpath(), $product_name);
            $this->wait_for_form_update();
        }
    }
    public function remove_associated_product(Product_Interface $product, Product_Association_Type_Interface $product_association_type): void
    {
        $this->change_tab();
        $association_field = $this->get_element('associations', ['%association%' => $product_association_type->get_code()]);
        $this->autocomplete_helper->remove_by_name($this->get_driver(), $association_field->get_xpath(), $product->get_name());
    }
    public function has_associated_product(Product_Interface $product, Product_Association_Type_Interface $product_association_type): bool
    {
        $this->change_tab();
        $association_field = $this->get_element('associations', ['%association%' => $product_association_type->get_code()]);
        $selected_items = $this->autocomplete_helper->get_selected_items($this->get_driver(), $association_field->get_xpath());
        return in_array($product->get_name(), $selected_items, true);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['associations' => '[name="sylius_admin_product[associations][%association%][]"]', 'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]']);
    }
    protected function change_tab(): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $this->get_element('side_navigation_tab', ['%name%' => 'associations'])->click();
    }
}
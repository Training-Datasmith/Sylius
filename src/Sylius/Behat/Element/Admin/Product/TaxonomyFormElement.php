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

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Mink\Session;
use Friends_Of_Behat\Symfony_Extension\Mink\Mink_Parameters;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Sylius\Component\Taxonomy\Model\Taxon_Interface;
class Taxonomy_Form_Element extends Base_Form_Element implements Taxonomy_Form_Element_Interface
{
    public function __construct(Session $session, array|Mink_Parameters $mink_parameters, protected readonly Autocomplete_Helper_Interface $autocomplete_helper)
    {
    }
    public function select_main_taxon(string $taxon_name): void
    {
        $this->change_tab();
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('main_taxon')->get_xpath(), $taxon_name);
        $this->wait_for_form_update();
    }
    public function get_main_taxon(): ?string
    {
        $this->change_tab();
        try {
            return $this->get_element('selected_main_taxon')->get_text();
        } catch (Element_Not_Found_Exception) {
            return null;
        }
    }
    public function check_product_taxon(Taxon_Interface $taxon): void
    {
        $this->change_tab();
        $this->get_element('product_taxons_checkbox', ['%code%' => $taxon->get_code()])->check();
    }
    public function uncheck_product_taxon(Taxon_Interface $taxon): void
    {
        $this->change_tab();
        $this->get_element('product_taxons_checkbox', ['%code%' => $taxon->get_code()])->uncheck();
    }
    public function check_all_taxons(): void
    {
        $this->change_tab();
        $this->get_element('product_taxons_check_all')->click();
    }
    public function uncheck_all_taxons(): void
    {
        $this->change_tab();
        $this->get_element('product_taxons_uncheck_all')->click();
    }
    public function filter_taxons_by(string $phrase): void
    {
        $this->change_tab();
        $this->get_element('product_taxons_filter')->set_value($phrase);
    }
    public function is_taxon_visible_in_main_taxon_list(string $taxon_name): bool
    {
        $this->change_tab();
        $elements = $this->autocomplete_helper->search($this->get_driver(), $this->get_element('main_taxon')->get_xpath(), $taxon_name);
        foreach ($elements as $element) {
            if (str_contains((string) $element, $taxon_name)) {
                return true;
            }
        }
        return false;
    }
    public function is_taxon_chosen(string $taxon_code): bool
    {
        $this->change_tab();
        return $this->get_element('product_taxons_checkbox', ['%code%' => $taxon_code])->is_checked();
    }
    public function has_taxon(string $taxon_code): bool
    {
        $this->change_tab();
        return $this->has_element('product_taxons_checkbox', ['%code%' => $taxon_code]);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['main_taxon' => '[data-test-main-taxon]', 'product_taxons_check_all' => '[data-test-product-taxons-check-all]', 'product_taxons_checkbox' => '[data-test-product-taxons] [data-id="%code%"] input[type="checkbox"]', 'product_taxons_filter' => '[data-test-product-taxons-filter]', 'product_taxons_uncheck_all' => '[data-test-product-taxons-uncheck-all]', 'selected_main_taxon' => '[data-test-main-taxon] option:selected', 'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]']);
    }
    protected function change_tab(): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $this->get_element('side_navigation_tab', ['%name%' => 'taxonomy'])->click();
    }
}
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
namespace Sylius\Behat\Element\Admin\Channel;

use Behat\Mink\Session;
use Friends_Of_Behat\Symfony_Extension\Mink\Mink_Parameters;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
class Exclude_Taxons_From_Showing_Lowest_Price_Input_Element extends Base_Form_Element implements Exclude_Taxons_From_Showing_Lowest_Price_Input_Element_Interface
{
    public function __construct(Session $session, array|Mink_Parameters $mink_parameters, protected Autocomplete_Helper_Interface $autocomplete_helper)
    {
    }
    public function exclude_taxon(Taxon_Interface $taxon): void
    {
        $exclude_taxon_element = $this->get_element('taxons_excluded_from_showing_lowest_price');
        $this->autocomplete_helper->select_by_name($this->get_driver(), $exclude_taxon_element->get_xpath(), $taxon->get_name());
        $this->wait_for_form_update();
    }
    public function remove_excluded_taxon(Taxon_Interface $taxon): void
    {
        $exclude_taxon_element = $this->get_element('taxons_excluded_from_showing_lowest_price');
        $this->autocomplete_helper->remove_by_name($this->get_driver(), $exclude_taxon_element->get_xpath(), $taxon->get_name());
        $this->wait_for_form_update();
    }
    public function has_taxon_excluded(Taxon_Interface $taxon): bool
    {
        return null !== $this->get_element('taxons_excluded_from_showing_lowest_price')->find('css', sprintf('option:selected:contains("%s")', $taxon->get_name()));
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['taxons_excluded_from_showing_lowest_price' => '[data-test-taxons-excluded-from-showing-lowest-price]']);
    }
}
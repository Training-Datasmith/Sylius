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
namespace Sylius\Behat\Element\Admin\Crud\Index;

use Sylius\Behat\Element\Sylius_Element;
class Search_Filter_Element extends Sylius_Element implements Search_Filter_Element_Interface
{
    public function search_with(string $phrase): void
    {
        $this->get_element('filter_search')->set_value($phrase);
        $this->get_element('filter_button')->press();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['filter_search' => '#criteria_search_value', 'filter_button' => '[data-test-filter]']);
    }
}
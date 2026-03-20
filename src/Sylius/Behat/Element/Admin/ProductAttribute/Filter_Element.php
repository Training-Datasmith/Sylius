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
namespace Sylius\Behat\Element\Admin\Product_Attribute;

use Sylius\Behat\Element\Sylius_Element;
class Filter_Element extends Sylius_Element implements Filter_Element_Interface
{
    public function choose_type(string $type): void
    {
        $this->get_element('filter_type')->select_option($type, true);
    }
    public function choose_translatable(string $translatable): void
    {
        $this->get_element('filter_translatable')->select_option($translatable);
    }
    public function filter(): void
    {
        $this->get_element('filter_button')->press();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['filter_button' => '[data-test-filter]', 'filter_translatable' => '#criteria_translatable', 'filter_type' => '#criteria_type']);
    }
}
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
namespace Sylius\Behat\Element\Product\Index_Page;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Element\Sylius_Element;
class Vertical_Menu_Element extends Sylius_Element implements Vertical_Menu_Element_Interface
{
    public function get_menu_items(): array
    {
        $menu = $this->get_element('vertical-menu');
        return array_map(fn(Node_Element $element): string => $element->get_text(), $menu->find_all('css', '[data-test-vertical-menu-item]'));
    }
    public function can_navigate_to_parent_taxon(): bool
    {
        $menu = $this->get_element('vertical-menu');
        return $menu->find('css', '[data-test-vertical-menu-go-level-up]') !== null;
    }
    protected function get_defined_elements(): array
    {
        return ['vertical-menu' => '[data-test-vertical-menu]'];
    }
}
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
namespace Sylius\Behat\Element\Shop;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Element\Sylius_Element;
class Menu_Element extends Sylius_Element implements Menu_Element_Interface
{
    public function get_menu_items(): array
    {
        $menu = $this->get_element('menu');
        return array_map(fn(Node_Element $element): string => $element->get_attribute('data-test-menu-item'), $menu->find_all('css', '[data-test-menu-item]'));
    }
    protected function get_defined_elements(): array
    {
        return ['menu' => '[data-test-menu]'];
    }
}
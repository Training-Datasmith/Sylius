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
namespace Sylius\Behat\Service;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\Node_Element;
use Behat\Mink\Session;
use D_More\Chrome_Driver\Chrome_Driver;
abstract class Tabs_Helper
{
    public static function switch_tab(Session $session, Node_Element $tabs_container, string $data_tab_hook): void
    {
        $driver = $session->get_driver();
        if (false === $driver instanceof Chrome_Driver && false === $driver instanceof Selenium2Driver) {
            return;
        }
        $tab = $tabs_container->find('css', sprintf('[data-test-tab*="%s"]', $data_tab_hook));
        if ($tab->has_class('active')) {
            return;
        }
        $tab->click();
        $session->get_page()->wait_for(5, fn(): array|null|false|int|float|string => $tab->has_class('active'));
    }
}
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

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Session;
abstract class Autocomplete_Helper
{
    public static function choose_value(Session $session, Node_Element $element, string $value): void
    {
        static::activate_autocomplete_dropdown($session, $element);
        $element->find('css', sprintf('div.item:contains("%s")', $value))->click();
        static::wait_for_element_to_be_visible($session, $element);
    }
    /**
     * @param string[] $values
     */
    public static function choose_values(Session $session, Node_Element $element, array $values): void
    {
        static::activate_autocomplete_dropdown($session, $element);
        foreach ($values as $value) {
            $element->find('css', sprintf('div.item:contains("%s")', $value))->click();
            Driver_Helper::wait_for_asynchronous_actions_to_finish($session);
        }
        static::wait_for_element_to_be_visible($session, $element);
    }
    public static function remove_value(Session $session, Node_Element $element, string $value): void
    {
        $session->wait(3000, sprintf('$(document.evaluate("%s", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue).dropdown("is visible")', str_replace('"', '\"', $element->get_xpath())));
        $element_to_remove = $element->find('css', sprintf('a.ui.label:contains("%s")', $value));
        $element_to_remove->find('css', 'i.delete')->click();
        Driver_Helper::wait_for_asynchronous_actions_to_finish($session);
    }
    public static function is_value_visible(Session $session, Node_Element $element, string $value): bool
    {
        $result = $element->find('css', sprintf('div.item:contains("%s")', $value));
        return null !== $result;
    }
    private static function activate_autocomplete_dropdown(Session $session, Node_Element $element): void
    {
        Driver_Helper::wait_for_asynchronous_actions_to_finish($session);
        $element->click();
        Driver_Helper::wait_for_asynchronous_actions_to_finish($session);
        static::wait_for_element_to_be_visible($session, $element);
    }
    private static function wait_for_element_to_be_visible(Session $session, Node_Element $element): void
    {
        $escaped_x_path = str_replace('"', '\"', $element->get_xpath());
        $session->wait(5000, sprintf('document.evaluate("%s", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeStep.nodeType === 1 && document.evaluate("%s", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeStep.offsetParent !== null', $escaped_x_path, $escaped_x_path));
    }
}
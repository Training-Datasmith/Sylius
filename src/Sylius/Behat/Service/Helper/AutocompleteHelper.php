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
namespace Sylius\Behat\Service\Helper;

use Behat\Mink\Driver\Driver_Interface;
final class Autocomplete_Helper implements Autocomplete_Helper_Interface
{
    public function get_selected_items(Driver_Interface $driver, string $selector): array
    {
        $selector = $this->normalize_selector($selector);
        $result = $driver->evaluate_script(<<<SCRIPT
            (function () {
                let select = document.evaluate("//SELECT[{$selector}]", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
                let selectedOptions = [];
        
                [...select.options].forEach((option) => selectedOptions[option.value] = option.textContent);
        
                return selectedOptions;
            })();
        SCRIPT);
        return is_array($result) ? $result : [];
    }
    public function search(Driver_Interface $driver, string $selector, string $search_string): mixed
    {
        $selector = $this->normalize_selector($selector);
        $driver->execute_script(<<<SCRIPT
            (function () {
                let element = document.evaluate("{$selector}", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
                element.tomselect.load('{$search_string}');
                element.tomselect.open();
            })();
        SCRIPT);
        $driver->wait(2000, <<<SCRIPT
        (function () {
            let element = document.evaluate("{$selector}", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
            return element.tomselect.loading === 0;
        })();
        SCRIPT);
        return $driver->evaluate_script(<<<SCRIPT
            (function () {
                let element = document.evaluate("{$selector}", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
                let searchResults = [];
        
                element.parentElement.querySelectorAll('[data-selectable]').forEach((node) => searchResults[node.dataset.value] = node.textContent);
        
                return searchResults;
            })();
        SCRIPT);
    }
    public function select_by_name(Driver_Interface $driver, string $selector, string $name): void
    {
        $selector = $this->normalize_selector($selector);
        $found_items = array_flip($this->search($driver, $selector, $name));
        $value = $this->get_value_by_phrase($found_items, $name);
        $this->add_item_by_value($driver, $selector, $value);
    }
    public function remove_by_name(Driver_Interface $driver, string $selector, string $name): void
    {
        $selector = $this->normalize_selector($selector);
        $selected_items = array_flip($this->get_selected_items($driver, $selector));
        $value = $this->get_value_by_phrase($selected_items, $name);
        $this->remove_item_by_value($driver, $selector, $value);
    }
    public function select_by_value(Driver_Interface $driver, string $selector, string $value): void
    {
        $selector = $this->normalize_selector($selector);
        $found_items = $this->search($driver, $selector, $value);
        if (!array_key_exists($value, $found_items)) {
            throw new \InvalidArgumentException(sprintf('Could not find "%s" in the autocomplete', $value));
        }
        $this->add_item_by_value($driver, $selector, $value);
    }
    public function remove_by_value(Driver_Interface $driver, string $selector, string $value): void
    {
        $selector = $this->normalize_selector($selector);
        $selected_items = $this->get_selected_items($driver, $selector);
        if (!array_key_exists($value, $selected_items)) {
            throw new \InvalidArgumentException(sprintf('Could not find "%s" in the autocomplete selected items', $value));
        }
        $this->remove_item_by_value($driver, $selector, $value);
    }
    public function clear(Driver_Interface $driver, string $selector): void
    {
        $selector = $this->normalize_selector($selector);
        $driver->execute_script(<<<SCRIPT
            (function () {
                let element = document.evaluate("{$selector}", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
                element.tomselect.clear();
                element.tomselect.refreshOptions();
            })();
        SCRIPT);
    }
    private function add_item_by_value(Driver_Interface $driver, string $selector, int|string $value): void
    {
        $driver->execute_script(<<<SCRIPT
            (function () {
                let element = document.evaluate("{$selector}", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
                element.tomselect.addItem('{$value}');
                element.tomselect.refreshOptions();
            })();
        SCRIPT);
    }
    private function remove_item_by_value(Driver_Interface $driver, string $selector, int|string $value): void
    {
        $driver->execute_script(<<<SCRIPT
            (function () {
                let element = document.evaluate("{$selector}", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
                element.tomselect.removeItem('{$value}');
                element.tomselect.refreshOptions();
            })();
        SCRIPT);
    }
    private function get_value_by_phrase(array $found_items, string $phrase): int|string
    {
        foreach ($found_items as $found_name => $found_value) {
            if (str_contains((string) $found_name, $phrase)) {
                return $found_value;
            }
        }
        throw new \InvalidArgumentException(sprintf('Could not find "%s" in the autocomplete', $phrase));
    }
    private function normalize_selector(string $selector): string
    {
        return str_replace('"', '\'', $selector);
    }
}
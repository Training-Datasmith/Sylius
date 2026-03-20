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
namespace Sylius\Behat\Element;

use Behat\Mink\Element\Node_Element;
use Friends_Of_Behat\Page_Object_Extension\Element\Element as BaseElement;
use Sylius\Behat\Service\Driver_Helper;
abstract class Sylius_Element extends Base_Element
{
    /** @param array<string, mixed> $parameters */
    protected function get_element(string $name, array $parameters = []): Node_Element
    {
        $this->wait_for_live_component_to_finish();
        return parent::get_element($name, $parameters);
    }
    protected function wait_for_live_component_to_finish(): void
    {
        if (Driver_Helper::is_javascript($this->get_driver()) === false) {
            return;
        }
        // Wait for ALL LiveComponents to complete their operations
        // Returns immediately if condition is already met
        // Max 10 seconds for: DOM ready, no loading indicators, no busy elements
        $this->get_session()->wait(10000, "document.readyState === 'complete' && " . "document.querySelectorAll('[data-live-is-loading]').length === 0 && " . "document.querySelectorAll('[busy]').length === 0");
    }
}
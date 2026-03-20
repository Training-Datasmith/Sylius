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
namespace Sylius\Behat\Behaviour;

use Behat\Mink\Element\Node_Element;
trait Toggles
{
    abstract protected function get_toggleable_element(): Node_Element;
    /**
     * @throws \RuntimeException If already enabled
     */
    public function enable(): void
    {
        $toggleable_element = $this->get_toggleable_element();
        $this->assert_checkbox_state($toggleable_element, false);
        $toggleable_element->check();
    }
    /**
     * @throws \RuntimeException If already disabled
     */
    public function disable(): void
    {
        $toggleable_element = $this->get_toggleable_element();
        $this->assert_checkbox_state($toggleable_element, true);
        $toggleable_element->uncheck();
    }
    /**
     * @param bool $expectedState
     *
     * @throws \RuntimeException
     */
    private function assert_checkbox_state(Node_Element $toggleable_element, $expected_state): void
    {
        if ($toggleable_element->is_checked() !== $expected_state) {
            throw new \RuntimeException(sprintf("Toggleable element state is '%s' but expected '%s'.", $toggleable_element->is_checked() ? 'true' : 'false', $expected_state ? 'true' : 'false'));
        }
    }
}
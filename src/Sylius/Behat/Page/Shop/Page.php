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
namespace Sylius\Behat\Page\Shop;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Page\Sylius_Page;
abstract class Page extends Sylius_Page implements Page_Interface
{
    public function fill_element(string $value, string $element, array $parameters = []): void
    {
        $found_element = $this->get_element($element, $parameters);
        $found_element->set_value($value);
    }
    /**
     * @param array<string, string> $parameters
     */
    public function get_validation_message(string $element, array $parameters = []): string
    {
        $found_element = $this->get_field_element($element, $parameters);
        $validation_message = $found_element->find('css', '.invalid-feedback');
        if (null === $validation_message) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '.invalid-feedback');
        }
        return $validation_message->get_text();
    }
    protected function wait_for_element_update(string $element): void
    {
        $element = $this->get_element($element);
        usleep(500000);
        // we need to sleep, as sometimes the check below is executed faster than the form sets the busy attribute
        $element->wait_for(1500, fn(): bool => !$element->has_attribute('busy'));
    }
    /**
     * @param array<string, string> $parameters
     *
     * @throws ElementNotFoundException
     */
    protected function get_field_element(string $element, array $parameters): Node_Element
    {
        $element = $this->get_element($element, $parameters);
        while (null !== $element && !$element->has_class('field')) {
            $element = $element->get_parent();
        }
        return $element;
    }
}
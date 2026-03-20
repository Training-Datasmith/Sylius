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
namespace Sylius\Behat\Element\Admin\Crud;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Element\Sylius_Element;
class Form_Element extends Sylius_Element implements Form_Element_Interface
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
    public function get_validation_errors(): string
    {
        $validation_message = $this->get_document()->find('css', 'form .alert.alert-danger');
        if (null === $validation_message) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', 'form .alert.alert-danger');
        }
        return $validation_message->get_text();
    }
    public function has_form_error_alert(): bool
    {
        return $this->has_element('form_error_alert');
    }
    /**
     * @return array<string, string>
     */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['form' => 'form', 'form_error_alert' => '[data-test-form-error-alert]']);
    }
    protected function wait_for_form_update(): void
    {
        $form = $this->get_element('form');
        usleep(500000);
        // we need to sleep, as sometimes the check below is executed faster than the form sets the busy attribute
        $form->wait_for(1500, fn(): bool => !$form->has_attribute('busy'));
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
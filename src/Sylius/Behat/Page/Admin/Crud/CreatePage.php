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
namespace Sylius\Behat\Page\Admin\Crud;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Driver_Exception;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Mink\Session;
use Friends_Of_Behat\Page_Object_Extension\Page\Unexpected_Page_Exception;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Driver_Helper;
use Symfony\Component\Routing\Router_Interface;
class Create_Page extends Sylius_Page implements Create_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, protected readonly string $route_name)
    {
    }
    public function create(): void
    {
        if (Driver_Helper::is_javascript($this->get_driver())) {
            $this->blur();
            $this->wait_for_form_update();
        }
        $this->get_document()->press_button('Create');
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    public function get_validation_message(string $element, array $parameters = []): string
    {
        $found_element = $this->get_field_element($element, $parameters);
        if (null === $found_element) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Field element');
        }
        $validation_message = $found_element->find('css', '.invalid-feedback');
        if (null === $validation_message) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '.invalid-feedback');
        }
        return $validation_message->get_text();
    }
    public function get_route_name(): string
    {
        return $this->route_name;
    }
    public function cancel_changes(): void
    {
        $this->get_element('cancel_button')->click();
    }
    public function get_message_invalid_form(): string
    {
        return $this->get_document()->find('css', '.ui.icon.negative.message')->get_text();
    }
    /**
     * @return array<string, string>
     */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['form' => 'form', 'cancel_button' => '[data-test-cancel-changes-button]']);
    }
    protected function wait_for_form_update(): void
    {
        $form = $this->get_element('form');
        sleep(1);
        // we need to sleep, as sometimes the check below is executed faster than the form sets the busy attribute
        $form->wait_for(1500, fn() => !$form->has_attribute('busy'));
    }
    protected function verify_status_code(): void
    {
        try {
            $status_code = $this->get_session()->get_status_code();
        } catch (Driver_Exception) {
            return;
            // Ignore drivers which cannot check the response status code
        }
        if ($status_code >= 200 && $status_code <= 299 || $status_code === 422) {
            return;
        }
        $current_url = $this->get_session()->get_current_url();
        $message = sprintf('Could not open the page: "%s". Received an error status code: %s', $current_url, $status_code);
        throw new Unexpected_Page_Exception($message);
    }
    /**
     * @throws ElementNotFoundException
     */
    protected function get_field_element(string $element, array $parameters = []): Node_Element
    {
        $element = $this->get_element($element, $parameters);
        while (null !== $element && !$element->has_class('field')) {
            $element = $element->get_parent();
        }
        return $element;
    }
}
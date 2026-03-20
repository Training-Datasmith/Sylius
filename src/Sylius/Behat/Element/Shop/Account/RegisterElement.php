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
namespace Sylius\Behat\Element\Shop\Account;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Mink\Session;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Element\Sylius_Element;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Behat\Service\Shared_Storage_Interface;
class Register_Element extends Sylius_Element implements Register_Element_Interface
{
    use Secure_Password_Trait;
    public function __construct(Session $session, $mink_parameters = [], protected ?Shared_Storage_Interface $shared_storage = null)
    {
    }
    public function register(): void
    {
        $this->get_element('register_button')->click();
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    public function specify_email(?string $email): void
    {
        $this->get_element('email')->set_value($email);
        $this->wait_for_form_update();
    }
    public function get_email(): string
    {
        return $this->get_element('email')->get_value();
    }
    public function specify_first_name(?string $first_name): void
    {
        $this->get_element('first_name')->set_value($first_name);
        $this->wait_for_form_update();
    }
    public function specify_last_name(?string $last_name): void
    {
        $this->get_element('last_name')->set_value($last_name);
        $this->wait_for_form_update();
    }
    public function specify_password(string $password): void
    {
        $this->get_element('password')->set_value($this->replace_with_secure_password($password));
        $this->wait_for_form_update();
    }
    public function specify_phone_number(string $phone_number): void
    {
        $this->get_element('phone_number')->set_value($phone_number);
        $this->wait_for_form_update();
    }
    public function verify_password(string $password): void
    {
        $this->get_element('password_verification')->set_value($this->confirm_secure_password($password));
        $this->wait_for_form_update();
    }
    public function subscribe_to_the_newsletter(): void
    {
        $this->get_element('newsletter')->check();
        $this->wait_for_form_update();
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
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['email' => '[data-test-email]', 'first_name' => '[data-test-first-name]', 'form' => '[data-live-name-value="sylius_shop:account:register:form"]', 'last_name' => '[data-test-last-name]', 'newsletter' => '[data-test-subscribed-to-newsletter]', 'password' => '[data-test-password-first]', 'password_verification' => '[data-test-password-second]', 'phone_number' => '[data-test-phone-number]', 'register_button' => '[data-test-button="register-button"]']);
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
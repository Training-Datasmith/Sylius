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
namespace Sylius\Behat\Page\Shop\Account;

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Component\Core\Formatter\String_Inflector;
class Register_Page extends Sylius_Page implements Register_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_register';
    }
    /**
     * @throws ElementNotFoundException
     */
    public function check_validation_message_for(string $element, string $message): bool
    {
        $error_label = $this->get_element(String_Inflector::name_to_code($element))->get_parent()->find('css', '[data-test-validation-error]');
        if (null === $error_label) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '[data-test-validation-error]');
        }
        return $message === $error_label->get_text();
    }
    public function register(): void
    {
        $this->get_element('create_account_button')->press();
    }
    public function specify_email(string $email): void
    {
        $this->get_element('email')->set_value($email);
    }
    public function specify_first_name(string $first_name): void
    {
        $this->get_element('first_name')->set_value($first_name);
    }
    public function specify_last_name(string $last_name): void
    {
        $this->get_element('last_name')->set_value($last_name);
    }
    public function specify_password(string $password): void
    {
        $this->get_element('password')->set_value($password);
    }
    public function specify_phone_number(string $phone_number): void
    {
        $this->get_element('phone_number')->set_value($phone_number);
    }
    public function verify_password(string $password): void
    {
        $this->get_element('password_verification')->set_value($password);
    }
    public function subscribe_to_the_newsletter(): void
    {
        $this->get_element('subscribe_newsletter')->check();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['create_account_button' => '[data-test-register-button]', 'email' => '[data-test-email]', 'first_name' => '[data-test-first-name]', 'last_name' => '[data-test-last-name]', 'password' => '[data-test-password-first]', 'password_verification' => '[data-test-password-second]', 'phone_number' => '[data-test-phone-number]', 'subscribe_newsletter' => '[data-test-subscribed-to-newsletter]']);
    }
}
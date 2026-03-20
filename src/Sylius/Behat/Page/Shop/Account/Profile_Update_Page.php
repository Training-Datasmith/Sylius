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
use Sylius\Behat\Service\Driver_Helper;
class Profile_Update_Page extends Sylius_Page implements Profile_Update_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_account_profile_update';
    }
    public function check_validation_message_for(string $element, string $message): bool
    {
        $error_label = $this->get_element($element)->get_parent()->find('css', '[data-test-validation-error]');
        if (null === $error_label) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '[data-test-validation-error]');
        }
        return $message === $error_label->get_text();
    }
    public function specify_first_name(?string $first_name): void
    {
        $this->get_element('first_name')->set_value($first_name);
    }
    public function specify_phone_number(?string $phone_number): void
    {
        $this->get_element('phone_number')->set_value($phone_number);
    }
    public function get_phone_number(): string
    {
        return $this->get_element('phone_number')->get_value();
    }
    public function specify_last_name(?string $last_name): void
    {
        $this->get_element('last_name')->set_value($last_name);
    }
    public function specify_email(?string $email): void
    {
        $this->get_element('email')->set_value($email);
    }
    public function save_changes(): void
    {
        $this->get_element('save_changes_button')->press();
        $this->wait_for_element_to_be_ready();
    }
    public function subscribe_to_the_newsletter(): void
    {
        $this->get_element('subscribe_newsletter')->check();
    }
    public function is_subscribed_to_the_newsletter(): bool
    {
        return $this->get_element('subscribe_newsletter')->is_checked();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['email' => '[data-test-email]', 'first_name' => '[data-test-first-name]', 'last_name' => '[data-test-last-name]', 'phone_number' => '[data-test-phone-number]', 'save_changes_button' => '[data-test-button="save-changes"]', 'subscribe_newsletter' => '[data-test-subscribe-newsletter]']);
    }
    protected function wait_for_element_to_be_ready(): void
    {
        if (Driver_Helper::is_javascript($this->get_driver())) {
            $this->get_document()->wait_for(1, fn(): bool => $this->is_open());
        }
    }
}
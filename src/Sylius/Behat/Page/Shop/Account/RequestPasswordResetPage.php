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
class Request_Password_Reset_Page extends Sylius_Page implements Request_Password_Reset_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_request_password_reset_token';
    }
    /**
     * @throws ElementNotFoundException
     */
    public function check_validation_message_for(string $element, string $message): bool
    {
        $error_label = $this->get_element($element)->get_parent()->find('css', '[data-test-validation-error]');
        if (null === $error_label) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '[data-test-validation-error]');
        }
        return $message === $error_label->get_text();
    }
    public function reset(): void
    {
        $this->get_element('reset_button')->click();
    }
    public function specify_email(?string $email): void
    {
        $this->get_element('email')->set_value($email);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['email' => '[data-test-reset-email]', 'reset_button' => '[data-test-request-password-reset-button]']);
    }
}
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
use Behat\Mink\Session;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Symfony\Component\Routing\Router_Interface;
class Reset_Password_Page extends Sylius_Page implements Reset_Password_Page_Interface
{
    use Secure_Password_Trait;
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, protected Shared_Storage_Interface $shared_storage)
    {
    }
    public function get_route_name(): string
    {
        return 'sylius_shop_password_reset';
    }
    public function reset(): void
    {
        $this->get_document()->press_button('Reset');
    }
    public function specify_new_password(string $password): void
    {
        $this->get_element('password')->set_value($this->replace_with_secure_password($password));
    }
    public function specify_confirm_password(string $password): void
    {
        $this->get_element('confirm_password')->set_value($this->confirm_secure_password($password));
    }
    public function check_validation_message_for(string $element, string $message): bool
    {
        $error_label = $this->get_element($element)->get_parent()->find('css', '[data-test-validation-error]');
        if (null === $error_label) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '[data-test-validation-error]');
        }
        return $message === $error_label->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['password' => '[data-test-password-reset-new]', 'confirm_password' => '[data-test-password-reset-confirmation]']);
    }
}
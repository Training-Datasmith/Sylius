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
namespace Sylius\Behat\Page\Admin\Account;

use Behat\Mink\Session;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Symfony\Component\Routing\Router_Interface;
class Login_Page extends Sylius_Page implements Login_Page_Interface
{
    use Secure_Password_Trait;
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, private Shared_Storage_Interface $shared_storage)
    {
    }
    public function has_validation_error_with(string $message): bool
    {
        return $this->get_element('validation_error')->get_text() === $message;
    }
    public function log_in(): void
    {
        $this->get_document()->press_button('Login');
    }
    public function specify_password(string $password): void
    {
        $this->get_document()->fill_field('Password', $this->retrieve_secure_password($password));
    }
    public function specify_username(string $username): void
    {
        $this->get_document()->fill_field('Username', $username);
    }
    public function get_route_name(): string
    {
        return 'sylius_admin_login';
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['validation_error' => '[data-test-invalid-credentials-message]']);
    }
}
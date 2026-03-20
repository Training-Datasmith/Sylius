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

use Behat\Mink\Session;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Symfony\Component\Routing\Router_Interface;
class Login_Page extends Sylius_Page implements Login_Page_Interface
{
    use Secure_Password_Trait;
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, protected Table_Accessor_Interface $table_accessor, private Shared_Storage_Interface $shared_storage)
    {
    }
    public function get_route_name(): string
    {
        return 'sylius_shop_login';
    }
    public function has_validation_error_with(string $message): bool
    {
        return $this->get_element('flash_message')->get_text() === $message;
    }
    public function log_in(): void
    {
        $this->get_element('login_button')->click();
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    public function specify_password(string $password): void
    {
        $this->get_element('password')->set_value($this->retrieve_secure_password($password));
    }
    public function specify_username(string $username): void
    {
        $this->get_element('username')->set_value($username);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['login_button' => '[data-test-button="login-button"]', 'password' => '[data-test-login-password]', 'username' => '[data-test-login-username]', 'flash_message' => '[data-test-sylius-flash-message]']);
    }
}
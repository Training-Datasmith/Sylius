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

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Page\Sylius_Page;
class Request_Password_Reset_Page extends Sylius_Page implements Request_Password_Reset_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_admin_render_reset_password_page';
    }
    public function specify_email(string $email): void
    {
        $this->get_element('email')->set_value($email);
    }
    public function get_email_validation_message(): string
    {
        $error_label = $this->get_element('email')->get_parent()->find('css', '.invalid-feedback');
        if (null === $error_label) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '.invalid-feedback');
        }
        return $error_label->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['email' => '[data-test-email]']);
    }
}
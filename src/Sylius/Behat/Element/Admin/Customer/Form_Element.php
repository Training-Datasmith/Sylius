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
namespace Sylius\Behat\Element\Admin\Customer;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Session;
use Sylius\Behat\Behaviour\Names_It;
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Behaviour\Toggles;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Shared_Storage_Interface;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    use Names_It;
    use Secure_Password_Trait;
    use Specifies_Its_Field;
    use Toggles;
    public function __construct(Session $session, $mink_parameters = [], protected ?Shared_Storage_Interface $shared_storage = null)
    {
    }
    public function get_full_name(): string
    {
        $first_name_element = $this->get_element('first_name')->get_value();
        $last_name_element = $this->get_element('last_name')->get_value();
        return sprintf('%s %s', $first_name_element, $last_name_element);
    }
    public function get_first_name(): string
    {
        return $this->get_element('first_name')->get_value();
    }
    public function get_last_name(): string
    {
        return $this->get_element('last_name')->get_value();
    }
    public function get_password(): string
    {
        return $this->get_element('password')->get_value();
    }
    public function subscribe_to_the_newsletter(): void
    {
        $this->get_document()->check_field('Subscribe to the newsletter');
    }
    public function is_subscribed_to_the_newsletter(): bool
    {
        return $this->get_document()->has_checked_field('Subscribe to the newsletter');
    }
    public function get_group_name(): string
    {
        return $this->get_element('group')->get_text();
    }
    public function verify_user(): void
    {
        $this->get_document()->check_field('Verified');
    }
    public function specify_first_name(string $name): void
    {
        $this->get_element('first_name')->set_value($name);
    }
    public function specify_last_name(string $name): void
    {
        $this->get_element('last_name')->set_value($name);
    }
    public function specify_email(string $email): void
    {
        $this->get_element('email')->set_value($email);
    }
    public function specify_birthday(string $birthday): void
    {
        $this->get_element('birthday')->set_value($birthday);
    }
    public function specify_password(string $password): void
    {
        $this->get_element('password')->set_value($this->replace_with_secure_password($password));
    }
    public function choose_gender(string $gender): void
    {
        $this->get_element('gender')->select_option($gender);
    }
    public function choose_group(string $group): void
    {
        $this->get_element('group')->select_option($group);
    }
    protected function get_toggleable_element(): Node_Element
    {
        return $this->get_element('enabled');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['birthday' => '[data-test-birthday]', 'email' => '[data-test-email]', 'enabled' => '[data-test-enabled]', 'first_name' => '[data-test-first-name]', 'gender' => '[data-test-gender]', 'group' => '[data-test-group]', 'last_name' => '[data-test-last-name]', 'password' => '[data-test-password]']);
    }
}
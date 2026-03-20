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
namespace Sylius\Behat\Element\Admin\Zone;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Behaviour\Names_It;
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    use Names_It;
    use Specifies_Its_Field;
    use Checks_Code_Immutability;
    public function get_name(): string
    {
        return $this->get_element('name')->get_value();
    }
    public function get_priority(): int
    {
        return (int) $this->get_element('priority')->get_value();
    }
    public function get_type(): string
    {
        return $this->get_element('type')->get_value();
    }
    public function is_type_field_disabled(): bool
    {
        return $this->get_element('type')->has_attribute('disabled');
    }
    public function get_scope(): string
    {
        return $this->get_element('scope')->get_value();
    }
    public function select_scope(string $scope): void
    {
        $this->get_document()->select_field_option('Scope', $scope);
    }
    public function has_member(string $member): bool
    {
        return $this->has_element('zone_member', ['%name%' => $member]);
    }
    public function count_members(): int
    {
        return count($this->get_element('zone_members')->find_all('css', '[data-test-zone-member]'));
    }
    public function add_member(): void
    {
        $this->get_element('add_member')->click();
        $this->wait_for_element(5, 'zone_member_added');
    }
    public function prioritize_it(int $priority): void
    {
        $this->get_element('priority')->set_value($priority);
    }
    public function remove_member(string $member): void
    {
        $this->get_element('zone_member_delete', ['%name%' => $member])->click();
        $this->wait_for_element(5, 'zone_member', ['%name%' => $member], false);
    }
    public function choose_member(string $name): void
    {
        $select = $this->get_element('zone_member_last')->find('css', 'select');
        if (null === $select) {
            throw new Element_Not_Found_Exception($this->get_session(), 'select', 'css', 'select');
        }
        $select->select_option($name);
    }
    public function get_form_validation_message(): string
    {
        return $this->get_element('form_validation_message')->get_text();
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['add_member' => '[data-test-add-member]', 'code' => '[data-test-code]', 'form_validation_message' => 'form > div.alert.alert-danger.d-block', 'name' => '[data-test-name]', 'priority' => '#sylius_admin_zone_priority', 'scope' => '[data-test-scope]', 'type' => '[data-test-type]', 'zone_member' => '[data-test-zone-member]:contains("%name%")', 'zone_member_added' => '[data-test-zone-member]:last-child option:not([selected="selected"])', 'zone_member_delete' => '[data-test-zone-member]:contains("%name%") button[name$="[delete]"]', 'zone_member_last' => '[data-test-members]:last-child', 'zone_members' => '[data-test-members]']);
    }
    protected function wait_for_element(int $timeout, string $element_name, array $parameters = [], bool $should_exist = true): bool
    {
        return $this->get_document()->wait_for($timeout, fn(): bool => $should_exist && $this->has_element($element_name, $parameters));
    }
}
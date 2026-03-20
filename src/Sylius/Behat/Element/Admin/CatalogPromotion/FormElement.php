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
namespace Sylius\Behat\Element\Admin\Catalog_Promotion;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Session;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Sylius\Behat\Service\Tabs_Helper;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    public function __construct(Session $session, $mink_parameters, protected readonly Autocomplete_Helper_Interface $autocomplete_helper)
    {
    }
    public function name_it(string $name): void
    {
        $this->get_element('name')->set_value($name);
    }
    public function label_it(string $label, string $locale_code): void
    {
        $this->get_element('label', ['%locale_code%' => $locale_code])->set_value($label);
    }
    public function describe_it(string $description, string $locale_code): void
    {
        $this->get_element('description', ['%locale_code%' => $locale_code])->set_value($description);
    }
    public function prioritize_it(int $priority): void
    {
        $this->get_element('priority')->set_value($priority);
    }
    public function change_enable_to(bool $enabled): void
    {
        $this->get_element('enabled')->set_value($enabled);
    }
    public function check_channel(string $channel_name): void
    {
        $this->get_element('channels')->check_field($channel_name);
    }
    public function set_exclusiveness(bool $is_exclusive): void
    {
        $this->get_element('exclusive')->set_value($is_exclusive);
    }
    public function uncheck_channel(string $channel_name): void
    {
        $this->get_element('channels')->uncheck_field($channel_name);
    }
    public function specify_start_date(\DateTimeInterface $start_date): void
    {
        $timestamp = $start_date->get_timestamp();
        $this->get_element('start_date_date')->set_value(date('Y-m-d', $timestamp));
        $this->get_element('start_date_time')->set_value(date('H:i', $timestamp));
    }
    public function specify_end_date(\DateTimeInterface $end_date): void
    {
        $timestamp = $end_date->get_timestamp();
        $this->get_element('end_date_date')->set_value(date('Y-m-d', $timestamp));
        $this->get_element('end_date_time')->set_value(date('H:i', $timestamp));
        $this->wait_for_form_update();
    }
    public function add_scope(string $type): void
    {
        $this->get_element('add_scope_button', ['%type%' => $type])->press();
        $this->wait_for_form_update();
    }
    public function add_action(string $type): void
    {
        $this->get_element('add_action_button', ['%type%' => $type])->press();
        $this->wait_for_form_update();
    }
    public function select_scope_option(array $names): void
    {
        $last_scope = $this->get_element('last_scope');
        foreach ($names as $name) {
            $this->autocomplete_helper->select_by_name($this->get_driver(), $last_scope->find('css', 'select')->get_xpath(), $name);
        }
        $this->wait_for_form_update();
    }
    public function fill_action_option(string $option, string $value): void
    {
        $last_action = $this->get_element('last_action');
        $last_action->fill_field($option, $value);
    }
    public function fill_action_option_for_channel(string $channel_code, string $option, string $value): void
    {
        $last_action = $this->get_element('last_action');
        Tabs_Helper::switch_tab($this->get_session(), $last_action, $channel_code);
        $last_action->find('css', sprintf('[id$="_configuration_%s"]', $channel_code))->fill_field($option, $value);
    }
    public function get_last_scope_names(): array
    {
        $last_scope = $this->get_element('last_scope');
        return array_map(fn(Node_Element $element) => $element->get_text(), $last_scope->find_all('css', 'option[selected="selected"]'));
    }
    public function get_last_action_option(string $option): string
    {
        $last_action = $this->get_element('last_action');
        return $last_action->find_field($option)->get_value();
    }
    public function get_last_action_option_for_channel(string $channel_code, string $option): string
    {
        $last_action = $this->get_element('last_action');
        Tabs_Helper::switch_tab($this->get_session(), $last_action, $channel_code);
        return $last_action->find('css', sprintf('[id$="_configuration_%s"]', $channel_code))->find_field($option)->get_value();
    }
    public function check_if_scope_configuration_form_is_visible(): bool
    {
        return $this->has_element('last_scope');
    }
    public function check_if_action_configuration_form_is_visible(): bool
    {
        return $this->has_element('last_action');
    }
    public function get_field_value_in_locale(string $field, string $locale_code): string
    {
        return $this->get_element($field, ['%locale_code%' => $locale_code])->get_value();
    }
    public function get_validation_messages(): array
    {
        $errors = $this->get_element('form')->find_all('css', '.alert-danger');
        return array_map(fn(Node_Element $element) => $element->get_text(), $errors);
    }
    public function remove_scope_option(array $names): void
    {
        $last_scope = $this->get_element('last_scope');
        foreach ($names as $name) {
            $this->autocomplete_helper->remove_by_name($this->get_driver(), $last_scope->find('css', 'select')->get_xpath(), $name);
        }
        $this->wait_for_form_update();
    }
    public function remove_last_action(): void
    {
        $this->get_element('last_action')->find('css', '[data-test-delete-action]')->click();
    }
    public function remove_last_scope(): void
    {
        $this->get_element('last_scope')->find('css', '[data-test-delete-action]')->click();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['add_action_button' => '[data-test-actions] [data-test-add-%type%]', 'add_scope_button' => '[data-test-scopes] [data-test-add-%type%]', 'channels' => '#sylius_admin_catalog_promotion_channels', 'description' => '[name="sylius_admin_catalog_promotion[translations][%locale_code%][description]"]', 'enabled' => '#sylius_admin_catalog_promotion_enabled', 'end_date_date' => '#sylius_admin_catalog_promotion_endDate_date', 'end_date_time' => '#sylius_admin_catalog_promotion_endDate_time', 'exclusive' => '#sylius_admin_catalog_promotion_exclusive', 'form' => '[data-live-name-value="sylius_admin:catalog_promotion:form"]', 'label' => '[name="sylius_admin_catalog_promotion[translations][%locale_code%][label]"]', 'last_action' => '[data-test-actions] [data-test-entry-row]:last-child', 'last_scope' => '[data-test-scopes] [data-test-entry-row]:last-child', 'name' => '#sylius_admin_catalog_promotion_name', 'priority' => '#sylius_admin_catalog_promotion_priority', 'start_date_date' => '#sylius_admin_catalog_promotion_startDate_date', 'start_date_time' => '#sylius_admin_catalog_promotion_startDate_time']);
    }
}
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
namespace Sylius\Behat\Element\Admin\Promotion;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Mink\Session;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Sylius\Behat\Service\Tabs_Helper;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    public function __construct(Session $session, $mink_parameters, protected readonly Autocomplete_Helper_Interface $autocomplete_helper)
    {
    }
    public function get_priority(): int
    {
        return (int) $this->get_element('priority')->get_value();
    }
    public function set_priority(?int $priority): void
    {
        $this->get_element('priority')->set_value($priority);
    }
    public function set_starts_at(\DateTimeInterface $date_time): void
    {
        $timestamp = $date_time->get_timestamp();
        $this->get_element('starts_at_date')->set_value(date('Y-m-d', $timestamp));
        $this->get_element('starts_at_time')->set_value(date('H:i', $timestamp));
    }
    public function set_ends_at(\DateTimeInterface $date_time): void
    {
        $timestamp = $date_time->get_timestamp();
        $this->get_element('ends_at_date')->set_value(date('Y-m-d', $timestamp));
        $this->get_element('ends_at_time')->set_value(date('H:i', $timestamp));
    }
    public function set_usage_limit(int $limit): void
    {
        $this->get_element('usage_limit')->set_value($limit);
    }
    public function make_exclusive(): void
    {
        $this->get_element('exclusive')->check();
    }
    public function make_not_applies_to_discounted_item(): void
    {
        $this->get_element('applies_to_discounted')->uncheck();
    }
    public function make_coupon_based(): void
    {
        $this->get_element('coupon_based')->check();
    }
    public function check_channel(string $name): void
    {
        $this->get_element('channels')->check_field($name);
    }
    public function set_label(string $label, string $locale_code): void
    {
        $this->get_element('label', ['%locale_code%' => $locale_code])->set_value($label);
    }
    public function has_label(string $label, string $locale_code): bool
    {
        return $label === $this->get_element('label', ['%locale_code%' => $locale_code])->get_value();
    }
    public function add_action(string $type): void
    {
        $this->get_element('add_action_button', ['%type%' => $type])->press();
        $this->wait_for_form_update();
    }
    public function remove_last_action(): void
    {
        $this->get_last_action()->find('css', 'button[data-test-delete]')->press();
        $this->wait_for_form_update();
    }
    public function fill_action_option(string $option, string $value): void
    {
        $this->get_last_action()->fill_field($option, $value);
    }
    public function fill_action_option_for_channel(string $channel_code, string $option, string $value): void
    {
        $last_action = $this->get_channel_configuration_of_last_action($channel_code);
        $last_action->fill_field($option, $value);
    }
    public function select_action_option(string $option, string $value, bool $multiple = false): void
    {
        $this->get_last_action()->find('named', ['select', $option])->select_option($value, $multiple);
    }
    public function add_rule(string $type): void
    {
        $this->get_element('add_rule_button', ['%type%' => $type])->press();
        $this->wait_for_form_update();
    }
    public function remove_last_rule(): void
    {
        $this->get_last_rule()->find('css', 'button[data-test-delete]')->press();
        $this->wait_for_form_update();
    }
    public function select_rule_option(string $option, string $value, bool $multiple = false): void
    {
        $this->get_last_rule()->find('named', ['select', $option])->select_option($value, $multiple);
    }
    public function fill_rule_option(string $option, string $value): void
    {
        $this->get_last_rule()->fill_field($option, $value);
    }
    public function fill_rule_option_for_channel(string $channel_code, string $option, string $value): void
    {
        $last_rule = $this->get_channel_configuration_of_last_rule($channel_code);
        $last_rule->fill_field($option, $value);
    }
    public function select_autocomplete_rule_options(array $values, ?string $channel_code = null): void
    {
        $count = count($this->get_element('rules')->find_all('css', '[data-test-entry-row]'));
        $locator = $channel_code ? sprintf('#sylius_admin_promotion_rules_%d_configuration_%s select', $count - 1, $channel_code) : sprintf('#sylius_admin_promotion_rules_%d_configuration select', $count - 1);
        foreach ($values as $value) {
            $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_last_rule()->find('css', $locator)->get_xpath(), $value);
        }
        $this->wait_for_form_update();
    }
    public function select_autocomplete_action_filter_options(array $values, string $channel_code, string $filter_type): void
    {
        $count = count($this->get_element('actions')->find_all('css', '[data-test-entry-row]'));
        $locator = sprintf('#sylius_admin_promotion_actions_%d_configuration_%s_filters_%s_filter select', $count - 1, $channel_code, $filter_type);
        foreach ($values as $value) {
            $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_last_action()->find('css', $locator)->get_xpath(), $value);
        }
        $this->wait_for_form_update();
    }
    public function check_if_rule_configuration_form_is_visible(): bool
    {
        return $this->has_element('rule_count');
    }
    public function check_if_action_configuration_form_is_visible(): bool
    {
        return $this->has_element('action_amount');
    }
    public function get_validation_message_for_action(): string
    {
        $action_form = $this->get_last_action();
        $found_element = $action_form->find('css', '.invalid-feedback');
        if (null === $found_element) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Tag', 'css', '.invalid-feedback');
        }
        return $found_element->get_text();
    }
    public function get_validation_message_for_translation(string $element, string $locale_code): string
    {
        $found_element = $this->get_element($element, ['%locale_code%' => $locale_code])->get_parent();
        $validation_message = $found_element->find('css', '.invalid-feedback');
        if (null === $validation_message) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '.invalid-feedback');
        }
        return $validation_message->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['action_amount' => '#sylius_admin_promotion_actions_0_configuration_WEB-US_amount', 'actions' => '#sylius_admin_promotion_actions', 'add_action_button' => '[data-test-actions] [data-test-add-%type%]', 'add_rule_button' => '[data-test-rules] [data-test-add-%type%]', 'applies_to_discounted' => '#sylius_admin_promotion_appliesToDiscounted', 'channels' => '#sylius_admin_promotion_channels', 'code' => '#sylius_admin_promotion_code', 'coupon_based' => '#sylius_admin_promotion_couponBased', 'ends_at_date' => '#sylius_admin_promotion_endsAt_date', 'ends_at_time' => '#sylius_admin_promotion_endsAt_time', 'exclusive' => '#sylius_admin_promotion_exclusive', 'label' => '[name="sylius_admin_promotion[translations][%locale_code%][label]"]', 'last_action' => '[data-test-actions] [data-test-entry-row]:last-child', 'last_rule' => '[data-test-rules] [data-test-entry-row]:last-child', 'minimum' => '#sylius_admin_promotion_actions_0_configuration_WEB-US_filters_price_range_filter_min', 'maximum' => '#sylius_admin_promotion_actions_0_configuration_WEB-US_filters_price_range_filter_max', 'name' => '#sylius_admin_promotion_name', 'priority' => '#sylius_admin_promotion_priority', 'rule_count' => '#sylius_admin_promotion_rules_0_configuration_count', 'rules' => '#sylius_admin_promotion_rules', 'starts_at_date' => '#sylius_admin_promotion_startsAt_date', 'starts_at_time' => '#sylius_admin_promotion_startsAt_time', 'translation_tab' => '[data-test-promotion-translations-accordion="%locale_code%"]', 'usage_limit' => '#sylius_admin_promotion_usageLimit']);
    }
    protected function get_last_action(): Node_Element
    {
        return $this->get_element('last_action');
    }
    protected function get_channel_configuration_of_last_action(string $channel_code): Node_Element
    {
        $last_action = $this->get_last_action();
        Tabs_Helper::switch_tab($this->get_session(), $last_action, $channel_code);
        return $last_action->find('css', sprintf('[id^="sylius_admin_promotion_actions_"][id$="_configuration_%s"]', $channel_code));
    }
    protected function get_last_rule(): Node_Element
    {
        return $this->get_element('last_rule');
    }
    protected function get_channel_configuration_of_last_rule(string $channel_code): Node_Element
    {
        $last_rule = $this->get_last_rule();
        Tabs_Helper::switch_tab($this->get_session(), $last_rule, $channel_code);
        return $last_rule->find('css', sprintf('[id^="sylius_admin_promotion_rules_"][id$="_configuration_%s"]', $channel_code));
    }
}
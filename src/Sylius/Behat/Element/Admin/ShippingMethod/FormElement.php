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
namespace Sylius\Behat\Element\Admin\Shipping_Method;

use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Behat\Service\Tabs_Helper;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    public function get_code(): string
    {
        return $this->get_element('code')->get_value();
    }
    public function set_code(string $code): void
    {
        $this->get_element('code')->set_value($code);
    }
    public function is_code_disabled(): bool
    {
        return $this->get_element('code')->has_attribute('disabled');
    }
    public function get_name(string $locale_code = 'en_US')
    {
        return $this->get_element('name', ['%localeCode%' => $locale_code])->get_value();
    }
    public function set_name(string $name, string $locale_code = 'en_US'): void
    {
        $this->get_element('name', ['%localeCode%' => $locale_code])->set_value($name);
    }
    public function get_position(): int
    {
        return (int) $this->get_element('position')->get_value();
    }
    public function set_position(int $position): void
    {
        $this->get_element('position')->set_value($position);
    }
    public function get_description(string $locale_code = 'en_US'): string
    {
        return $this->get_element('description', ['%localeCode%' => $locale_code])->get_value();
    }
    public function set_description(string $description, string $locale_code = 'en_US'): void
    {
        $this->get_element('description', ['%localeCode%' => $locale_code])->set_value($description);
    }
    public function get_zone_code(): string
    {
        return $this->get_element('zone')->get_value();
    }
    public function set_zone_code(string $code): void
    {
        $this->get_element('zone')->set_value($code);
    }
    public function disable(): void
    {
        $this->get_element('enabled')->uncheck();
    }
    public function enable(): void
    {
        $this->get_element('enabled')->check();
    }
    public function check_channel(string $channel_code): void
    {
        $this->get_element('channel', ['%channelCode%' => $channel_code])->check();
    }
    public function has_checked_channel(string $channel_code): bool
    {
        return $this->get_element('channel', ['%channelCode%' => $channel_code])->is_checked();
    }
    public function set_calculator_configuration_amount_for_channel(string $channel_code, ?int $amount): void
    {
        $this->select_calculator_configuration_channel_tab($channel_code);
        $this->get_element('calculator_configuration_amount', ['%channelCode%' => $channel_code])->set_value((string) $amount);
    }
    public function choose_calculator(string $calculator_name): void
    {
        $this->get_element('calculator')->select_option($calculator_name);
        $this->wait_for_form_update();
    }
    public function add_rule(string $type): void
    {
        $this->get_element('add_rule_button', ['%type%' => $type])->press();
        $this->wait_for_form_update();
    }
    public function fill_last_rule_option(string $field_name, string $value): void
    {
        $last_rule = $this->get_element('last_rule');
        $last_rule->fill_field($field_name, $value);
    }
    public function fill_last_rule_option_for_channel(string $channel_code, string $field_name, string $value): void
    {
        $last_rule = $this->get_element('last_rule');
        Tabs_Helper::switch_tab($this->get_session(), $last_rule, $channel_code);
        $last_rule->find('css', sprintf('[id$="_configuration_%s"]', $channel_code))->fill_field($field_name, $value);
    }
    public function get_shipping_charges_validation_errors_count(string $channel_code): int
    {
        return count($this->get_element('calculator_configuration_channel_tab_content', ['%channelCode%' => $channel_code])->find_all('css', '.invalid-feedback'));
    }
    public function set_field(string $field, string $value): void
    {
        $this->get_document()->fill_field($field, $value);
    }
    /**
     * @return array<string, string>
     */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['add_rule_button' => '[data-test-rules] [data-test-add-%type%]', 'calculator' => '#sylius_admin_shipping_method_calculator', 'calculator_configuration_amount' => '#sylius_admin_shipping_method_configuration_%channelCode%_amount', 'calculator_configuration_channel_tab' => '[data-test-calculator-configuration] [data-test-channel-tab^="%channelCode%_"]', 'calculator_configuration_channel_tab_content' => '[data-test-calculator-configuration] [data-test-channel-tab-content^="%channelCode%_"]', 'channel' => '[name="sylius_admin_shipping_method[channels][]"][value="%channelCode%"]', 'code' => '#sylius_admin_shipping_method_code', 'description' => '#sylius_admin_shipping_method_translations_%localeCode%_description', 'enabled' => '#sylius_admin_shipping_method_enabled', 'form' => '[data-live-name-value="sylius_admin:shipping_method:form"]', 'last_rule' => '[data-test-rules] [data-test-entry-row]:last-child', 'last_rule_amount' => '[data-test-rules] [data-test-entry-row]:last-child [id$="_configuration_%channelCode%_amount"]', 'last_rule_weight' => '[data-test-rules] [data-test-entry-row]:last-child [id$="_configuration_weight"]', 'max_delivery_time_days' => '#sylius_admin_shipping_method_maxDeliveryTimeDays', 'min_delivery_time_days' => '#sylius_admin_shipping_method_minDeliveryTimeDays', 'name' => '#sylius_admin_shipping_method_translations_%localeCode%_name', 'position' => '#sylius_admin_shipping_method_position', 'zone' => '#sylius_admin_shipping_method_zone']);
    }
    protected function select_calculator_configuration_channel_tab(string $channel_code): void
    {
        if (!Driver_Helper::is_javascript($this->get_driver())) {
            throw new \RuntimeException('This method can be used only with JavaScript enabled');
        }
        $this->get_element('calculator_configuration_channel_tab', ['%channelCode%' => $channel_code])->click();
    }
}
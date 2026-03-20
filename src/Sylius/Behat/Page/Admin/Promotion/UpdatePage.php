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
namespace Sylius\Behat\Page\Admin\Promotion;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Behaviour\Counts_Channel_Based_Errors;
use Sylius\Behat\Behaviour\Names_It;
use Sylius\Behat\Page\Admin\Crud\Update_Page as BaseUpdatePage;
class Update_Page extends Base_Update_Page implements Update_Page_Interface
{
    use Checks_Code_Immutability;
    use Counts_Channel_Based_Errors;
    use Names_It;
    public function check_channels_state(string $channel_name): bool
    {
        $field = $this->get_document()->find_field($channel_name);
        return (bool) $field->get_value();
    }
    public function has_starts_at(\DateTimeInterface $date_time): bool
    {
        $timestamp = $date_time->get_timestamp();
        return $this->get_element('starts_at_date')->get_value() === date('Y-m-d', $timestamp) && $this->get_element('starts_at_time')->get_value() === date('H:i', $timestamp);
    }
    public function has_ends_at(\DateTimeInterface $date_time): bool
    {
        $timestamp = $date_time->get_timestamp();
        return $this->get_element('ends_at_date')->get_value() === date('Y-m-d', $timestamp) && $this->get_element('ends_at_time')->get_value() === date('H:i', $timestamp);
    }
    public function is_coupon_management_available(): bool
    {
        return $this->has_element('manage_coupons_button');
    }
    public function manage_coupons(): void
    {
        $this->get_element('manage_coupons_button')->click();
    }
    public function has_any_rule(): bool
    {
        $items = $this->get_element('rules')->find_all('css', 'div[data-form-collection="item"]');
        return 0 < count($items);
    }
    public function has_rule(string $name): bool
    {
        $items = $this->get_element('rules')->find_all('css', 'div[data-form-collection="item"]');
        foreach ($items as $item) {
            $selected_option = $item->find('css', 'option[selected="selected"]');
            /** @var NodeElement $selectedOption */
            if ($selected_option->get_text() === $name) {
                return true;
            }
        }
        return false;
    }
    public function remove_action_field_value(string $channel_code, string $field): void
    {
        $this->get_element('action_field', ['%channelCode%' => $channel_code, '%field%' => $field])->set_value('');
    }
    public function get_item_percentage_discount_action_value(string $channel_code): string
    {
        return $this->get_element('action_field', ['%channelCode%' => $channel_code, '%field%' => 'percentage'])->get_value() . '%';
    }
    public function specify_order_percentage_discount_action_value(string $discount): void
    {
        $this->get_element('order_percentage_action_field')->set_value($discount);
    }
    public function get_order_percentage_discount_action_value(): string
    {
        $action = $this->get_element('order_percentage_action_field');
        return $action->find('css', 'input')->get_value() . '%';
    }
    public function remove_rule_amount(string $channel_code): void
    {
        $this->get_element('rule_amount', ['%channelCode%' => $channel_code])->set_value('');
    }
    public function get_action_validation_errors_count(string $channel_code): int
    {
        return $this->count_channel_errors($this->get_element('actions', ['%name%' => $channel_code]), $channel_code);
    }
    public function get_rule_validation_errors_count(string $channel_code): int
    {
        return $this->count_channel_errors($this->get_element('rules'), $channel_code);
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['action_field' => '[id^="sylius_admin_promotion_actions_"][id$="_configuration_%channelCode%_%field%"]', 'actions' => '#sylius_admin_promotion_actions', 'applies_to_discounted' => '#sylius_admin_promotion_appliesToDiscounted', 'code' => '#sylius_admin_promotion_code', 'ends_at' => '#sylius_admin_promotion_endsAt', 'ends_at_date' => '#sylius_admin_promotion_endsAt_date', 'ends_at_time' => '#sylius_admin_promotion_endsAt_time', 'exclusive' => '#sylius_admin_promotion_exclusive', 'coupon_based' => '#sylius_admin_promotion_couponBased', 'manage_coupons_button' => '[data-test-manage-coupons]', 'name' => '#sylius_admin_promotion_name', 'order_percentage_action_field' => '[id^="sylius_admin_promotion_actions_"][id$="_configuration_percentage"]', 'priority' => '#sylius_admin_promotion_priority', 'rule_amount' => '[id^="sylius_admin_promotion_rules_"][id$="_configuration_%channelCode%_amount"]', 'rules' => '#sylius_admin_promotion_rules', 'usage_limit' => '#sylius_admin_promotion_usageLimit', 'starts_at' => '#sylius_admin_promotion_startsAt', 'starts_at_date' => '#sylius_admin_promotion_startsAt_date', 'starts_at_time' => '#sylius_admin_promotion_startsAt_time']);
    }
}
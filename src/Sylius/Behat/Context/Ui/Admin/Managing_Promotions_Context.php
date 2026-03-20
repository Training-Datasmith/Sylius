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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Context\Ui\Admin\Helper\Validation_Trait;
use Sylius\Behat\Element\Admin\Promotion\Form_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as IndexPageCouponInterface;
use Sylius\Behat\Page\Admin\Promotion\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Promotion\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Promotion\Update_Page_Interface;
use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Promotion_Interface;
use Sylius\Component\Core\Promotion\Action\Fixed_Discount_Promotion_Action_Command;
use Sylius\Component\Core\Promotion\Action\Percentage_Discount_Promotion_Action_Command;
use Sylius\Component\Core\Promotion\Action\Unit_Fixed_Discount_Promotion_Action_Command;
use Sylius\Component\Core\Promotion\Action\Unit_Percentage_Discount_Promotion_Action_Command;
use Sylius\Component\Core\Promotion\Checker\Rule\Cart_Quantity_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Contains_Product_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Customer_Group_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Has_Taxon_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Item_Total_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Total_Of_Items_From_Taxon_Rule_Checker;
use Webmozart\Assert\Assert;
final class Managing_Promotions_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Shared_Storage_Interface $shared_storage, private Index_Page_Interface $index_page, private Index_Page_Coupon_Interface $index_coupon_page, private Create_Page_Interface $create_page, private Update_Page_Interface $update_page, private Current_Page_Resolver_Interface $current_page_resolver, private Notification_Checker_Interface $notification_checker, private Form_Element_Interface $form_element)
    {
    }
    #[When('I create a new promotion')]
    #[When('I want to create a new promotion')]
    public function i_want_to_create_a_new_promotion(): void
    {
        $this->create_page->open();
    }
    #[When('I want to browse promotions')]
    #[When('I browse promotions')]
    public function i_want_to_browse_promotions(): void
    {
        $this->index_page->open();
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->create_page->specify_code($code ?? '');
    }
    #[When('I name it :name')]
    #[When('I do not name it')]
    #[When('I remove its name')]
    public function i_name_it($name = null): void
    {
        $this->create_page->name_it($name ?? '');
    }
    #[When('I set its priority to :priority')]
    #[When('I remove its priority')]
    public function i_remove_its_priority(?int $priority = null): void
    {
        $this->form_element->set_priority($priority);
    }
    #[Then('the :promotionName promotion should appear in the registry')]
    #[Then('the :promotionName promotion should exist in the registry')]
    #[Then('this promotion should still be named :promotionName')]
    #[Then('promotion :promotionName should still exist in the registry')]
    public function the_promotion_should_appear_in_the_registry(string $promotion_name): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $promotion_name]));
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I specify its label as :label in :localeCode locale')]
    public function i_specify_its_label_in_locale_code(string $label, string $locale_code): void
    {
        $this->form_element->set_label($label, $locale_code);
    }
    #[When('I replace its label with a string exceeding the limit in :localeCode locale')]
    public function i_specify_its_label_with_a_string_exceeding_the_limit_in_locale(string $locale_code): void
    {
        $this->form_element->set_label(str_repeat('a', 256), $locale_code);
    }
    #[When('the :promotion promotion should have a label :label in :localeCode locale')]
    public function the_promotion_should_have_label_in_locale(Promotion_Interface $promotion, string $label, string $locale_code): void
    {
        $this->update_page->open(['id' => $promotion->get_id()]);
        $this->form_element->has_label($label, $locale_code);
    }
    #[When('I add the "Has at least one from taxons" rule configured with :firstTaxon taxon')]
    #[When('I add the "Has at least one from taxons" rule configured with :firstTaxon taxon and :secondTaxon taxon')]
    public function i_add_the_has_taxon_rule_configured_with(string ...$taxons): void
    {
        $this->form_element->add_rule(Has_Taxon_Rule_Checker::TYPE);
        $this->form_element->select_autocomplete_rule_options($taxons);
    }
    #[When('/^I add the "Total price of items from taxon" rule configured with "([^"]+)" taxon and "(?:€|£|\$)([^"]+)" amount for ("[^"]+" channel)$/')]
    public function i_add_the_rule_configured_with(string $taxon_name, string $amount, Channel_Interface $channel): void
    {
        $this->form_element->add_rule(Total_Of_Items_From_Taxon_Rule_Checker::TYPE);
        $this->form_element->select_autocomplete_rule_options([$taxon_name], $channel->get_code());
        $this->form_element->fill_rule_option_for_channel($channel->get_code(), 'Amount', $amount);
    }
    #[When('/^I add the "Item total" rule configured with "(?:€|£|\$)([^"]+)" amount for ("[^"]+" channel) and "(?:€|£|\$)([^"]+)" amount for ("[^"]+" channel)$/')]
    public function i_add_the_item_total_rule_configured_with_two_channel(string $first_amount, Channel_Interface $first_channel, string $second_amount, Channel_Interface $second_channel): void
    {
        $this->form_element->add_rule(Item_Total_Rule_Checker::TYPE);
        $this->form_element->fill_rule_option_for_channel($first_channel->get_code(), 'Amount', $first_amount);
        $this->form_element->fill_rule_option_for_channel($second_channel->get_code(), 'Amount', $second_amount);
    }
    #[When('/^I add the "Order fixed discount" action configured with amount of "(?:€|£|\$)([^"]+)" for ("[^"]+" channel)$/')]
    public function i_add_the_order_fixed_discount_action_configured_with_amount_for_channel(string $amount, Channel_Interface $channel): void
    {
        $this->form_element->add_action(Fixed_Discount_Promotion_Action_Command::TYPE);
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Amount', $amount);
    }
    #[When('/^I add the "Item fixed discount" action configured with amount of "(?:€|£|\$)([^"]+)" for ("[^"]+" channel)$/')]
    public function i_add_the_item_fixed_discount_action_configured_with_amount_for_channel(string $amount, Channel_Interface $channel): void
    {
        $this->form_element->add_action(Unit_Fixed_Discount_Promotion_Action_Command::TYPE);
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Amount', $amount);
    }
    #[When('/^it is(?:| also) configured with amount of "(?:€|£|\$)([^"]+)" for ("[^"]+" channel)$/')]
    public function it_is_configured_with_amount_for_channel(string $amount, Channel_Interface $channel): void
    {
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Amount', $amount);
    }
    #[When('/^I specify that on ("[^"]+" channel) this action should be applied to items with price greater than "(?:€|£|\$)([^"]+)"$/')]
    public function i_add_a_min_price_filter_range_for_channel(Channel_Interface $channel, string $minimum): void
    {
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Min', $minimum);
    }
    #[When('/^I specify that on ("[^"]+" channel) this action should be applied to items with price lesser than "(?:€|£|\$)([^"]+)"$/')]
    public function i_add_a_max_price_filter_range_for_channel(Channel_Interface $channel, string $maximum): void
    {
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Max', $maximum);
    }
    #[When('/^I specify that on ("[^"]+" channel) this action should be applied to items with price between "(?:€|£|\$)([^"]+)" and "(?:€|£|\$)([^"]+)"$/')]
    public function i_add_a_min_max_price_filter_range_for_channel(Channel_Interface $channel, $minimum, $maximum): void
    {
        $this->i_add_a_min_price_filter_range_for_channel($channel, $minimum);
        $this->i_add_a_max_price_filter_range_for_channel($channel, $maximum);
    }
    #[When('I specify that this action should be applied to items from :taxonName category for :channel channel')]
    public function i_specify_that_this_action_should_be_applied_to_items_from_category(string $taxon_name, Channel_Interface $channel): void
    {
        $this->form_element->select_autocomplete_action_filter_options([$taxon_name], $channel->get_code(), 'taxons');
    }
    #[When('/^I add the "Item percentage discount" action configured with a percentage value of "(?:|-)([^"]+)%" for ("[^"]+" channel)$/')]
    public function i_add_the_item_percentage_discount_action_configured_with_a_percentage_value_for_channel(string $percentage, Channel_Interface $channel): void
    {
        $this->form_element->add_action(Unit_Percentage_Discount_Promotion_Action_Command::TYPE);
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Percentage', $percentage);
    }
    #[When('/^I add the "Order percentage discount" action configured with a percentage value of "(?:|-)([^"]+)%" for ("[^"]+" channel)$/')]
    public function i_add_the_order_percentage_discount_action_configured_with_a_percentage_value_for_channel(string $percentage, Channel_Interface $channel): void
    {
        $this->form_element->add_action(Percentage_Discount_Promotion_Action_Command::TYPE);
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Percentage', $percentage);
    }
    #[When('I add the "Order percentage discount" action configured without a percentage value for :channel channel')]
    public function i_add_the_order_percentage_discount_action_configured_without_a_percentage_value_for_channel(Channel_Interface $channel): void
    {
        $this->form_element->add_action(Percentage_Discount_Promotion_Action_Command::TYPE);
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Percentage', '');
    }
    #[When('I add the "Item percentage discount" action configured without a percentage value for :channel channel')]
    public function i_add_the_item_percentage_discount_action_configured_without_a_percentage_value_for_channel(Channel_Interface $channel): void
    {
        $this->form_element->add_action(Unit_Percentage_Discount_Promotion_Action_Command::TYPE);
        $this->form_element->fill_action_option_for_channel($channel->get_code(), 'Percentage', '');
    }
    #[When('/^I add the "Order percentage discount" action configured with a percentage value of "(?:|-)([^"]+)%"$/')]
    #[When('I add the "Order percentage discount" action configured without a percentage value')]
    public function i_add_the_order_percentage_discount_action_configured_with_a_percentage_value($percentage = null): void
    {
        $this->form_element->add_action(Percentage_Discount_Promotion_Action_Command::TYPE);
        $this->form_element->fill_action_option('Percentage', $percentage ?? '');
    }
    /**
     * @WhenI add the "Item percentage discount" action configured without a percentage value
     */
    #[When('/^I add the "Item percentage discount" action configured with a percentage value of "(?:|-)([^"]+)%"$/')]
    public function i_add_the_item_percentage_discount_action_configured_with_a_percentage_value($percentage = null): void
    {
        $this->form_element->add_action(Unit_Percentage_Discount_Promotion_Action_Command::TYPE);
        $this->form_element->fill_action_option('Percentage', $percentage ?? '');
    }
    #[When('I add the "Customer group" rule for :customerGroupName group')]
    public function i_add_the_customer_group_rule_configured_for_group(string $customer_group_name): void
    {
        $this->form_element->add_rule(Customer_Group_Rule_Checker::TYPE);
        $this->form_element->select_rule_option('Customer group', $customer_group_name);
    }
    #[When('I check (also) the :promotionName promotion')]
    public function i_check_the_promotion(string $promotion_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $promotion_name]);
    }
    #[When('I remove its last rule')]
    public function i_remove_its_last_rule(): void
    {
        $this->form_element->remove_last_rule();
    }
    #[When('I remove its last action')]
    public function i_remove_its_last_action(): void
    {
        $this->form_element->remove_last_action();
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[When('I archive the :promotionName promotion')]
    public function i_archive_the_promotion(string $promotion_name): void
    {
        $actions = $this->index_page->get_actions_for_resource(['name' => $promotion_name]);
        $actions->press_button('Archive');
    }
    #[When('I restore the :promotionName promotion')]
    public function i_restore_the_promotion(string $promotion_name): void
    {
        $actions = $this->index_page->get_actions_for_resource(['name' => $promotion_name]);
        $actions->press_button('Restore');
    }
    #[When('I filter archival promotions')]
    public function i_filter_archival_promotions(): void
    {
        $this->index_page->choose_archival('Yes');
        $this->index_page->filter();
    }
    #[Then('I should see a single promotion in the list')]
    #[Then('there should be :amount promotions')]
    public function there_should_be_promotion(int $amount = 1): void
    {
        Assert::same($this->index_page->count_items(), $amount);
    }
    #[Then('/^(this promotion) should be coupon based$/')]
    public function this_promotion_should_be_coupon_based(Promotion_Interface $promotion): void
    {
        Assert::true($this->index_page->is_coupon_based_for($promotion));
    }
    #[Then('/^I should be able to manage coupons for (this promotion)$/')]
    public function i_should_be_able_to_manage_coupons_for_this_promotion(Promotion_Interface $promotion): void
    {
        Assert::true($this->index_page->is_able_to_manage_coupons_for($promotion));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        $this->assert_field_validation_message($element, sprintf('Please enter promotion %s.', $element));
    }
    #[Then('I should be notified that a :element value should be a numeric value')]
    public function i_should_be_notified_that_a_minimal_value_should_be_numeric(string $element): void
    {
        $this->assert_field_validation_message($element, 'Please enter a valid money amount.');
    }
    #[Then('I should be notified that promotion with this code already exists')]
    public function i_should_be_notified_that_promotion_with_this_code_already_exists(): void
    {
        Assert::same($this->form_element->get_validation_message('code'), 'The promotion with given code already exists.');
    }
    #[Then('promotion with :element :name should not be added')]
    public function promotion_with_element_value_should_not_be_added($element, $name): void
    {
        $this->index_page->open();
        Assert::false($this->index_page->is_single_resource_on_page([$element => $name]));
    }
    #[Then('there should still be only one promotion with :element :value')]
    public function there_should_still_be_only_one_promotion_with($element, $value): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page([$element => $value]));
    }
    #[When('I set its usage limit to :usageLimit')]
    public function i_set_its_usage_limit_to(int $usage_limit): void
    {
        $this->form_element->set_usage_limit($usage_limit);
    }
    #[Then('the :promotion promotion should be available to be used only :usageLimit times')]
    public function the_promotion_should_be_available_to_use_only_times(Promotion_Interface $promotion, int $usage_limit): void
    {
        $this->i_want_to_modify_a_promotion($promotion);
        Assert::true($this->update_page->has_resource_values(['usage_limit' => $usage_limit]));
    }
    #[When('I set it as exclusive')]
    public function i_set_it_as_exclusive(): void
    {
        $this->form_element->make_exclusive();
    }
    #[When('I set it as not applies to discounted by catalog promotion items')]
    public function i_set_it_as_not_applies_to_discounted_by_catalog_promotion_items(): void
    {
        $this->form_element->make_not_applies_to_discounted_item();
    }
    #[Then('the :promotion promotion should be exclusive')]
    public function the_promotion_should_be_exclusive(Promotion_Interface $promotion): void
    {
        $this->assert_if_field_is_true($promotion, 'exclusive');
    }
    #[Then('the :promotion promotion should not applies to discounted items')]
    public function the_promotion_should_not_applies_to_discounted_items(Promotion_Interface $promotion): void
    {
        $this->assert_if_field_is_false($promotion, 'applies_to_discounted');
    }
    #[When('I make it coupon based')]
    public function i_make_it_coupon_based(): void
    {
        $this->form_element->make_coupon_based();
    }
    #[Then('the :promotion promotion should be coupon based')]
    public function the_promotion_should_be_coupon_based(Promotion_Interface $promotion): void
    {
        $this->assert_if_field_is_true($promotion, 'coupon_based');
    }
    #[When('I make it applicable for the :channelName channel')]
    public function i_make_it_applicable_for_the_channel(string $channel_name): void
    {
        $this->form_element->check_channel($channel_name);
    }
    #[Then('the :promotion promotion should be applicable for the :channelName channel')]
    public function the_promotion_should_be_applicable_for_the_channel(Promotion_Interface $promotion, string $channel_name): void
    {
        $this->i_want_to_modify_a_promotion($promotion);
        Assert::true($this->update_page->check_channels_state($channel_name));
    }
    #[When('I want to modify a :promotion promotion')]
    #[When('/^I want to modify (this promotion)$/')]
    #[When('I modify a :promotion promotion')]
    public function i_want_to_modify_a_promotion(Promotion_Interface $promotion): void
    {
        $this->update_page->open(['id' => $promotion->get_id()]);
    }
    #[When('/^I edit (this promotion) percentage action to have "([^"]+)%"$/')]
    public function i_edit_promotion_to_have_discount(Promotion_Interface $promotion, string $amount): void
    {
        $this->update_page->open(['id' => $promotion->get_id()]);
        $this->update_page->specify_order_percentage_discount_action_value($amount);
        $this->update_page->save_changes();
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->update_page->is_code_disabled());
    }
    #[When('/^I delete a ("([^"]+)" promotion)$/')]
    #[When('/^I try to delete a ("([^"]+)" promotion)$/')]
    public function i_delete_promotion(Promotion_Interface $promotion): void
    {
        $this->shared_storage->set('promotion', $promotion);
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['name' => $promotion->get_name()]);
    }
    #[Then('/^(this promotion) should no longer exist in the promotion registry$/')]
    public function promotion_should_not_exist_in_the_registry(Promotion_Interface $promotion): void
    {
        $this->index_page->open();
        Assert::false($this->index_page->is_single_resource_on_page(['code' => $promotion->get_code()]));
    }
    #[Then('I should be notified that it is in use and cannot be deleted')]
    public function i_should_be_notified_of_failure(): void
    {
        $this->notification_checker->check_notification('Cannot delete, the Cart promotion is in use.', Notification_Type::failure());
    }
    #[When('I make it available from :startsDate to :endsDate')]
    public function i_make_it_available_from_to(\DateTimeInterface $starts_date, \DateTimeInterface $ends_date): void
    {
        $this->form_element->set_starts_at($starts_date);
        $this->form_element->set_ends_at($ends_date);
    }
    #[Then('the :promotion promotion should be available from :startsDate to :endsDate')]
    public function the_promotion_should_be_available_from_to(Promotion_Interface $promotion, \DateTimeInterface $starts_date, \DateTimeInterface $ends_date): void
    {
        $this->i_want_to_modify_a_promotion($promotion);
        Assert::true($this->update_page->has_starts_at($starts_date));
        Assert::true($this->update_page->has_ends_at($ends_date));
    }
    #[Then('I should be notified that promotion cannot end before it starts')]
    public function i_should_be_notified_that_promotion_cannot_end_before_its_even_starts(): void
    {
        Assert::same($this->form_element->get_validation_message('ends_at_date'), 'End date cannot be set prior start date.');
    }
    #[Then('I should be notified that this value should not be blank')]
    public function i_should_be_notified_that_this_value_should_not_be_blank(): void
    {
        Assert::same($this->form_element->get_validation_message_for_action(), 'This value should not be blank.');
    }
    #[Then('I should be notified that a percentage discount value must be between 0% and 100%')]
    #[Then('I should be notified that a percentage discount value must be at least 0%')]
    #[Then('I should be notified that the maximum value of a percentage discount is 100%')]
    public function i_should_be_notified_that_percentage_discount_should_be_between(): void
    {
        Assert::same($this->form_element->get_validation_message_for_action(), 'The percentage discount must be between 0% and 100%.');
    }
    #[Then('the promotion :promotion should be used :usage time(s)')]
    #[Then('the promotion :promotion should not be used')]
    public function the_promotion_should_be_used_time(Promotion_Interface $promotion, int $usage = 0): void
    {
        Assert::same($usage, $this->index_page->get_usage_number($promotion), 'Promotion should be used %s times, but is %2$s.');
    }
    #[When('I add the "Contains product" rule configured with the :productName product')]
    public function i_add_the_rule_configured_with_the_product(string $product_name): void
    {
        $this->form_element->add_rule(Contains_Product_Rule_Checker::TYPE);
        $this->form_element->select_autocomplete_rule_options([$product_name]);
    }
    #[When('I specify that this action should be applied to the :productName product for :channel channel')]
    public function i_specify_that_this_action_should_be_applied_to_the_product(string $product_name, Channel_Interface $channel): void
    {
        $this->form_element->select_autocomplete_action_filter_options([$product_name], $channel->get_code(), 'products');
    }
    #[Then('I should see :count promotions on the list')]
    public function i_should_see_promotions_on_the_list(int $count): void
    {
        $actual_count = $this->index_page->count_items();
        Assert::same($count, $actual_count, 'There should be %s promotion, but there\'s %2$s.');
    }
    #[Then('the first promotion on the list should have :field :value')]
    public function the_first_promotion_on_the_list_should_have(string $field, string $value): void
    {
        $fields = $this->index_page->get_column_fields($field);
        $actual_value = reset($fields);
        Assert::same($actual_value, $value, sprintf('Expected first promotion\'s %s to be "%s", but it is "%s".', $field, $value, $actual_value));
    }
    #[Then('the last promotion on the list should have :field :value')]
    public function the_last_promotion_on_the_list_should_have(string $field, string $value): void
    {
        $fields = $this->index_page->get_column_fields($field);
        $actual_value = end($fields);
        Assert::same($actual_value, $value, sprintf('Expected last promotion\'s %s to be "%s", but it is "%s".', $field, $value, $actual_value));
    }
    #[Given('the :promotion promotion should have priority :priority')]
    public function the_promotions_should_have_priority(Promotion_Interface $promotion, int $priority): void
    {
        $this->i_want_to_modify_a_promotion($promotion);
        Assert::same($this->form_element->get_priority(), $priority);
    }
    #[When('I want to manage this promotion coupons')]
    public function i_want_to_manage_this_promotion_s_coupons(): void
    {
        $this->update_page->manage_coupons();
    }
    #[Then('I should not be able to access coupons management page')]
    public function i_should_not_be_able_to_access_coupons_management_page(): void
    {
        Assert::false($this->update_page->is_coupon_management_available());
    }
    #[Then('/^I should be on (this promotion)\'s coupons management page$/')]
    public function i_should_be_on_this_promotion_s_coupons_management_page(Promotion_Interface $promotion): void
    {
        Assert::true($this->index_coupon_page->is_open(['promotionId' => $promotion->get_id()]));
    }
    #[Then('I should be able to modify a :promotion promotion')]
    public function i_should_be_able_to_modify_a_promotion(Promotion_Interface $promotion): void
    {
        $this->i_want_to_modify_a_promotion($promotion);
        $this->update_page->save_changes();
    }
    #[Then('the :promotion promotion should have :ruleName rule configured')]
    public function the_promotion_should_have_rule_configured(Promotion_Interface $promotion, string $rule_name): void
    {
        $this->i_want_to_modify_a_promotion($promotion);
        $this->update_page->save_changes();
        Assert::true($this->update_page->has_rule($rule_name));
    }
    #[Then('the :promotion promotion should not have any rule configured')]
    public function the_promotion_should_not_have_any_rule_configured(Promotion_Interface $promotion): void
    {
        $this->i_want_to_modify_a_promotion($promotion);
        Assert::false($this->update_page->has_any_rule());
    }
    #[When('/^I filter promotions by coupon code equal "([^"]+)"/')]
    public function i_filter_promotions_by_coupon_code_equal(string $value): void
    {
        $this->index_page->specify_filter_type('coupon_code', 'equal');
        $this->index_page->specify_filter_value('coupon_code', $value);
        $this->index_page->filter();
    }
    #[When('I add a new rule')]
    public function i_add_a_new_rule(): void
    {
        $this->form_element->add_rule(Cart_Quantity_Rule_Checker::TYPE);
    }
    #[When('I add a new action')]
    public function i_add_a_new_action(): void
    {
        $this->form_element->add_action(Fixed_Discount_Promotion_Action_Command::TYPE);
    }
    #[When('/^I remove the discount (amount|percentage) for ("[^"]+" channel)$/')]
    public function i_remove_the_discount_for_channel(string $field, Channel_Interface $channel): void
    {
        $this->update_page->remove_action_field_value($channel->get_code(), $field);
    }
    #[When('I remove the rule amount for :channel channel')]
    public function i_remove_the_rule_amount_for_channel(Channel_Interface $channel): void
    {
        $this->update_page->remove_rule_amount($channel->get_code());
    }
    #[Then('I should see the rule configuration form')]
    public function i_should_see_the_rule_configuration_form(): void
    {
        Assert::true($this->form_element->check_if_rule_configuration_form_is_visible(), 'Cart promotion rule configuration form is not visible.');
    }
    #[Then('I should not see the rule configuration form')]
    public function i_should_not_see_the_rule_configuration_form(): void
    {
        Assert::false($this->form_element->check_if_rule_configuration_form_is_visible(), 'Cart promotion rule configuration form is visible.');
    }
    #[Then('it should have :amount of order percentage discount')]
    public function it_should_have_of_order_percentage_discount(string $amount): void
    {
        Assert::same($this->update_page->get_order_percentage_discount_action_value(), $amount);
    }
    #[Then('it should have :amount of item percentage discount configured for :channel channel')]
    public function it_should_have_of_item_percentage_discount(string $amount, Channel_Interface $channel): void
    {
        Assert::same($this->update_page->get_item_percentage_discount_action_value($channel->get_code()), $amount);
    }
    #[Then('I should see the action configuration form')]
    public function i_should_see_the_action_configuration_form(): void
    {
        Assert::true($this->form_element->check_if_action_configuration_form_is_visible(), 'Cart promotion action configuration form is not visible.');
    }
    #[Then('I should not see the action configuration form')]
    public function i_should_not_see_the_action_configuration_form(): void
    {
        Assert::false($this->form_element->check_if_action_configuration_form_is_visible(), 'Cart promotion action configuration form is visible.');
    }
    #[Then('/^I should see that the rule for ("[^"]+" channel) has (\d+) validation errors?$/')]
    public function i_should_see_that_the_rule_for_channel_has_count_validation_errors(Channel_Interface $channel, int $count): void
    {
        Assert::same($this->update_page->get_rule_validation_errors_count($channel->get_code()), $count);
    }
    #[Then('/^I should see that the action for ("[^"]+" channel) has (\d+) validation errors?$/')]
    public function i_should_see_that_the_action_for_channel_has_count_validation_errors(Channel_Interface $channel, int $count): void
    {
        Assert::same($this->update_page->get_action_validation_errors_count($channel->get_code()), $count);
    }
    #[Then('I should be notified that :promotion promotion has been updated')]
    public function i_should_be_notified_that_promotions_have_been_updated(Promotion_Interface $promotion): void
    {
        $this->notification_checker->check_notification(sprintf('Some rules of the promotions with codes %s have been updated.', $promotion->get_code()), Notification_Type::info());
    }
    #[Then('I should be notified that promotion label in :localeCode locale is too long')]
    public function i_should_be_notified_that_promotion_label_is_too_long(string $locale_code): void
    {
        Assert::same($this->form_element->get_validation_message_for_translation('label', $locale_code), 'This value is too long. It should have 255 characters or less.');
    }
    #[Then('I should see the promotion :promotionName in the list')]
    public function i_should_see_the_promotion_in_the_list(string $promotion_name): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $promotion_name]));
    }
    #[Then('I should not see the promotion :promotionName in the list')]
    public function i_should_not_see_the_promotion_in_the_list(string $promotion_name): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['name' => $promotion_name]));
    }
    #[Then('I should be viewing non archival promotions')]
    public function i_should_be_viewing_non_archival_promotions(): void
    {
        Assert::false($this->index_page->is_archival_filter_enabled());
    }
    #[Then('the :promotion promotion should be successfully created')]
    public function the_promotion_should_be_successfully_created(Promotion_Interface $promotion): void
    {
        $this->update_page->verify(['id' => $promotion->get_id()]);
    }
    private function assert_field_validation_message(string $element, string $expected_message): void
    {
        Assert::same($this->form_element->get_validation_message($element), $expected_message);
    }
    private function assert_if_field_is_true(Promotion_Interface $promotion, string $field): void
    {
        $this->i_want_to_modify_a_promotion($promotion);
        Assert::true($this->update_page->has_resource_values([$field => 1]));
    }
    private function assert_if_field_is_false(Promotion_Interface $promotion, string $field): void
    {
        $this->i_want_to_modify_a_promotion($promotion);
        Assert::false($this->update_page->has_resource_values([$field => 1]));
    }
    protected function resolve_current_page(): Sylius_Page_Interface
    {
        return $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
    }
}
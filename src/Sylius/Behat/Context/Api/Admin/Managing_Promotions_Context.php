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
namespace Sylius\Behat\Context\Api\Admin;

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Promotion_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Core\Promotion\Action\Fixed_Discount_Promotion_Action_Command;
use Sylius\Component\Core\Promotion\Action\Percentage_Discount_Promotion_Action_Command;
use Sylius\Component\Core\Promotion\Action\Shipping_Percentage_Discount_Promotion_Action_Command;
use Sylius\Component\Core\Promotion\Action\Unit_Fixed_Discount_Promotion_Action_Command;
use Sylius\Component\Core\Promotion\Action\Unit_Percentage_Discount_Promotion_Action_Command;
use Sylius\Component\Core\Promotion\Checker\Rule\Contains_Product_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Customer_Group_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Has_Taxon_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Item_Total_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Total_Of_Items_From_Taxon_Rule_Checker;
use Sylius\Component\Customer\Model\Customer_Group_Interface;
use Symfony\Component\Http_Foundation\Request;
use Webmozart\Assert\Assert;
final readonly class Managing_Promotions_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter)
    {
    }
    #[When('I want to browse promotions')]
    #[When('I browse promotions')]
    public function i_want_to_browse_promotions(): void
    {
        $this->client->index(Resources::PROMOTIONS);
    }
    #[When('I want to create a new promotion')]
    public function i_want_to_create_a_new_promotion(): void
    {
        $this->client->build_create_request(Resources::PROMOTIONS);
    }
    #[When('I want to modify a :promotion promotion')]
    #[When('/^I want to modify (this promotion)$/')]
    #[When('I modify a :promotion promotion')]
    public function i_want_to_modify_a_promotion(Promotion_Interface $promotion): void
    {
        $this->client->build_update_request(Resources::PROMOTIONS, $promotion->get_code());
    }
    #[When('I archive the :promotion promotion')]
    public function i_archive_the_promotion(Promotion_Interface $promotion): void
    {
        $this->client->custom_item_action(Resources::PROMOTIONS, $promotion->get_code(), Request::METHOD_PATCH, 'archive');
        $this->client->index(Resources::PROMOTIONS);
    }
    #[When('I restore the :promotion promotion')]
    public function i_restore_the_promotion(Promotion_Interface $promotion): void
    {
        $this->client->custom_item_action(Resources::PROMOTIONS, $promotion->get_code(), Request::METHOD_PATCH, 'restore');
    }
    #[When('I specify its :field as :value')]
    #[When('I do not specify its :field')]
    #[When('I :field it :value')]
    public function i_specify_its_as(string $field, ?string $value = null): void
    {
        if (null !== $value) {
            $this->client->add_request_data($field, $value);
        }
    }
    #[When('I set it as not applies to discounted by catalog promotion items')]
    public function i_set_it_as_not_applies_to_discounted_by_catalog_promotion_items(): void
    {
        $this->client->update_request_data(['appliesToDiscounted' => false]);
    }
    #[When('I set its usage limit to :usageLimit')]
    public function i_set_its_usage_limit_to(int $usage_limit): void
    {
        $this->client->add_request_data('usageLimit', $usage_limit);
    }
    #[When('I set it as exclusive')]
    public function i_set_it_as_exclusive(): void
    {
        $this->client->add_request_data('exclusive', true);
    }
    #[When('I make it coupon based')]
    public function i_make_it_coupon_based(): void
    {
        $this->client->add_request_data('couponBased', true);
    }
    #[When('I set its priority to :priority')]
    #[When('I remove its priority')]
    public function i_remove_its_priority(?int $priority = null): void
    {
        $this->client->add_request_data('priority', $priority);
    }
    #[When('I do not name it')]
    #[When('I remove its name')]
    public function i_name_it(string $name = ''): void
    {
        $this->client->add_request_data('name', $name);
    }
    #[When('I make it applicable for the :channel channel')]
    public function i_make_it_applicable_for_the_channel(Channel_Interface $channel): void
    {
        $this->client->add_request_data('channels', [$this->iri_converter->get_iri_from_resource($channel)]);
    }
    #[When('I make it available from :startsDate to :endsDate')]
    public function i_make_it_available_from_to(\DateTimeInterface $starts_date, \DateTimeInterface $ends_date): void
    {
        $this->client->update_request_data(['startsAt' => $starts_date->format('Y-m-d H:i:s'), 'endsAt' => $ends_date->format('Y-m-d H:i:s')]);
    }
    #[When('I specify its label as :label in :localeCode locale')]
    public function i_specify_its_label_in_locale_code(string $label, string $locale_code): void
    {
        $data['translations'][$locale_code]['label'] = $label;
        $this->client->update_request_data($data);
    }
    #[When('I replace its label with a string exceeding the limit in :localeCode locale')]
    public function i_specify_its_label_with_a_string_exceeding_the_limit_in_locale(string $locale_code): void
    {
        $this->i_specify_its_label_in_locale_code(str_repeat('a', 256), $locale_code);
    }
    #[When('/^I add the "([^"]+)" action configured with amount of "(?:€|£|\$)([^"]+)" for ("[^"]+" channel)$/')]
    public function i_add_the_action_configured_with_amount_for_channel(string $action_type, int $amount, Channel_Interface $channel): void
    {
        $action_type_mapping = ['Order fixed discount' => Fixed_Discount_Promotion_Action_Command::TYPE, 'Item fixed discount' => Unit_Fixed_Discount_Promotion_Action_Command::TYPE];
        $this->add_to_request_action($action_type_mapping[$action_type], [$channel->get_code() => ['amount' => $amount]]);
    }
    #[When('/^I add the "Item percentage discount" action configured with a percentage value of ("[^"]+") for ("[^"]+" channel)$/')]
    public function i_add_the_action_configured_with_a_percentage_value_for_channel(float $percentage, Channel_Interface $channel): void
    {
        $this->add_to_request_action(Unit_Percentage_Discount_Promotion_Action_Command::TYPE, [$channel->get_code() => ['percentage' => $percentage]]);
    }
    #[When('I add the "Item percentage discount" action configured without a percentage value for :channel channel')]
    public function i_add_the_action_configured_without_a_percentage_value_for_channel(Channel_Interface $channel): void
    {
        $this->add_to_request_action(Unit_Percentage_Discount_Promotion_Action_Command::TYPE, [$channel->get_code() => ['percentage' => null]]);
    }
    #[When('/^I add the "([^"]+)" action configured with a percentage value of ("[^"]+")$/')]
    #[When('I add the :actionType action configured without a percentage value')]
    public function i_add_the_action_configured_with_a_percentage_value(string $action_type, ?float $percentage = null): void
    {
        $action_type_mapping = ['Order percentage discount' => Percentage_Discount_Promotion_Action_Command::TYPE, 'Shipping percentage discount' => Shipping_Percentage_Discount_Promotion_Action_Command::TYPE];
        $this->add_to_request_action($action_type_mapping[$action_type], ['percentage' => $percentage]);
    }
    #[When('/^it is(?:| also) configured with amount of "(?:€|£|\$)([^"]+)" for ("[^"]+" channel)$/')]
    public function it_is_configured_with_amount_for_channel(float $amount, Channel_Interface $channel): void
    {
        $actions = $this->get_actions();
        $actions[0]['configuration'][$channel->get_code()]['amount'] = $amount;
        $this->client->add_request_data('actions', $actions);
    }
    #[When('/^I edit (this promotion) percentage action to have ("[^"]+")$/')]
    public function i_edit_promotion_to_have_discount(Promotion_Interface $promotion, float $percentage): void
    {
        $actions = $this->get_actions();
        $actions[0]['configuration']['percentage'] = $percentage;
        $this->client->add_request_data('actions', $actions);
    }
    #[When('/^I specify that on ("[^"]+" channel) this action should be applied to items with price greater than "(?:€|£|\$)([^"]+)"$/')]
    public function i_add_a_min_price_filter_range_for_channel(Channel_Interface $channel, int|string $minimum): void
    {
        $actions = $this->get_actions();
        $actions[0]['configuration'][$channel->get_code()]['filters']['price_range_filter']['min'] = $minimum;
        $this->client->add_request_data('actions', $actions);
    }
    #[When('/^I specify that on ("[^"]+" channel) this action should be applied to items with price lesser than "(?:€|£|\$)([^"]+)"$/')]
    public function i_add_a_max_price_filter_range_for_channel(Channel_Interface $channel, int|string $maximum): void
    {
        $actions = $this->get_actions();
        $actions[0]['configuration'][$channel->get_code()]['filters']['price_range_filter']['max'] = $maximum;
        $this->client->add_request_data('actions', $actions);
    }
    #[When('/^I specify that on ("[^"]+" channel) this action should be applied to items with price between "(?:€|£|\$)([^"]+)" and "(?:€|£|\$)([^"]+)"$/')]
    public function i_add_a_min_max_price_filter_range_for_channel(Channel_Interface $channel, int $minimum, int $maximum): void
    {
        $this->i_add_a_min_price_filter_range_for_channel($channel, $minimum);
        $this->i_add_a_max_price_filter_range_for_channel($channel, $maximum);
    }
    #[When('I specify that this action should be applied to items from :taxon category for :channel channel')]
    public function i_specify_that_this_action_should_be_applied_to_items_from_category(Taxon_Interface $taxon, Channel_Interface $channel): void
    {
        $actions = $this->get_actions();
        $actions[0]['configuration'][$channel->get_code()]['filters']['taxons_filter']['taxons'] = [$taxon->get_code()];
        $this->client->add_request_data('actions', $actions);
    }
    #[When('I specify that this action should be applied to the :product product for :channel channel')]
    public function i_specify_that_this_action_should_be_applied_to_the_product(Product_Interface $product, Channel_Interface $channel): void
    {
        $actions = $this->get_actions();
        $actions[0]['configuration'][$channel->get_code()]['filters']['products_filter']['products'] = [$product->get_code()];
        $this->client->add_request_data('actions', $actions);
    }
    #[When('/^I add the "Has at least one from taxons" rule configured with ("[^"]+" taxon)$/')]
    #[When('/^I add the "Has at least one from taxons" rule configured with ("[^"]+" taxon) and ("[^"]+" taxon)$/')]
    public function i_add_the_has_taxon_rule_configured_with(Taxon_Interface ...$taxons): void
    {
        $this->add_to_request_rule(Has_Taxon_Rule_Checker::TYPE, ['taxons' => array_map(fn(Taxon_Interface $taxon): string => $taxon->get_code(), $taxons)]);
    }
    #[When('/^I add the "Total price of items from taxon" rule configured with ("[^"]+" taxon) and ("[^"]+") amount for ("[^"]+" channel)$/')]
    public function i_add_the_rule_configured_with(Taxon_Interface $taxon, int $amount, Channel_Interface $channel): void
    {
        $this->add_to_request_rule(Total_Of_Items_From_Taxon_Rule_Checker::TYPE, [$channel->get_code() => ['amount' => $amount, 'taxon' => $taxon->get_code()]]);
    }
    #[When('/^I add the "Item total" rule configured with ("[^"]+") amount for ("[^"]+" channel) and ("[^"]+") amount for ("[^"]+" channel)$/')]
    public function i_add_the_item_total_rule_configured_with_two_channel(int $first_amount, Channel_Interface $first_channel, int $second_amount, Channel_Interface $second_channel): void
    {
        $this->add_to_request_rule(Item_Total_Rule_Checker::TYPE, [$first_channel->get_code() => ['amount' => $first_amount], $second_channel->get_code() => ['amount' => $second_amount]]);
    }
    #[When('I add the "Contains product" rule configured with the :product product')]
    public function i_add_the_rule_configured_with_the_product(Product_Interface $product): void
    {
        $this->add_to_request_rule(Contains_Product_Rule_Checker::TYPE, ['product_code' => $product->get_code()]);
    }
    #[When('I add the "Customer group" rule for :customerGroup group')]
    public function i_add_the_customer_group_rule_configured_for_group(Customer_Group_Interface $customer_group): void
    {
        $this->add_to_request_rule(Customer_Group_Rule_Checker::TYPE, ['group_code' => $customer_group->get_code()]);
    }
    #[When('I filter promotions by coupon code equal :value')]
    public function i_filter_promotions_by_coupon_code_equal(string $value): void
    {
        $this->client->add_filter('coupons.code', $value);
        $this->client->filter();
    }
    #[When('I filter archival promotions')]
    public function i_filter_archival_promotions(): void
    {
        $this->client->add_filter('exists[archivedAt]', true);
        $this->client->filter();
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[Then('I should see a single promotion in the list')]
    #[Then('there should be :amount promotions')]
    public function there_should_be_promotion(int $amount = 1): void
    {
        Assert::same(count($this->response_checker->get_collection($this->client->get_last_response())), $amount);
    }
    #[Then('the :promotionName promotion should appear in the registry')]
    #[Then('the :promotionName promotion should exist in the registry')]
    #[Then('promotion :promotionName should still exist in the registry')]
    #[Then('this promotion should still be named :promotionName')]
    public function the_promotion_should_appear_in_the_registry(string $promotion_name): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::PROMOTIONS), 'name', $promotion_name), sprintf('Promotion with name %s does not exist', $promotion_name));
    }
    #[Then('I should see the promotion :promotionName in the list')]
    public function i_should_see_the_promotion_in_the_list(string $promotion_name): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'name', $promotion_name), sprintf('Promotion with name %s does not exist', $promotion_name));
    }
    #[Then('I should not see the promotion :promotionName in the list')]
    public function i_should_not_see_the_promotion_in_the_list(string $promotion_name): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->get_last_response(), 'name', $promotion_name), sprintf('Promotion with name %s does not exist', $promotion_name));
    }
    #[Then('/^(this promotion) should be coupon based$/')]
    public function this_promotion_should_be_coupon_based(Promotion_Interface $promotion): void
    {
        $returned_promotion = current($this->response_checker->get_collection_items_with_value($this->client->get_last_response(), 'name', $promotion->get_name()));
        Assert::true($returned_promotion['couponBased'], sprintf('The promotion %s isn\'t coupon based', $promotion->get_name()));
    }
    #[Then('/^I should be able to manage coupons for (this promotion)$/')]
    public function i_should_be_able_to_manage_coupons_for_this_promotion(Promotion_Interface $promotion): void
    {
        $returned_promotion = current($this->response_checker->get_collection_items_with_value($this->client->get_last_response(), 'name', $promotion->get_name()));
        Assert::key_exists($returned_promotion, 'coupons');
    }
    #[When('/^I delete a ("([^"]+)" promotion)$/')]
    #[When('/^I try to delete a ("([^"]+)" promotion)$/')]
    public function i_delete_promotion(Promotion_Interface $promotion): void
    {
        $this->client->delete(Resources::PROMOTIONS, $promotion->get_code());
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Promotion still exists, but it should not');
    }
    #[Then('/^(this promotion) should no longer exist in the promotion registry$/')]
    public function promotion_should_not_exist_in_the_registry(Promotion_Interface $promotion): void
    {
        $response = $this->client->index(Resources::PROMOTIONS);
        $promotion_name = (string) $promotion->get_name();
        Assert::false($this->response_checker->has_item_with_value($response, 'name', $promotion_name), sprintf('Promotion with name %s still exist', $promotion_name));
    }
    #[Then('the :promotionName promotion should be successfully created')]
    public function the_promotion_should_be_successfully_created(string $promotion_name): void
    {
        $this->i_should_be_notified_that_it_has_been_successfully_created();
        $this->the_promotion_should_appear_in_the_registry($promotion_name);
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()));
    }
    #[Then('the :promotion promotion should not applies to discounted items')]
    public function the_promotion_should_not_applies_to_discounted_items(Promotion_Interface $promotion): void
    {
        Assert::false($this->response_checker->get_value($this->client->show(Resources::PROMOTIONS, $promotion->get_code()), 'appliesToDiscounted'));
    }
    #[Then('the :promotion promotion should be available to be used only :usageLimit times')]
    public function the_promotion_should_be_available_to_use_only_times(Promotion_Interface $promotion, int $usage_limit): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::PROMOTIONS, $promotion->get_code()), 'usageLimit', $usage_limit));
    }
    #[Then('the :promotion promotion should be exclusive')]
    public function the_promotion_should_be_exclusive(Promotion_Interface $promotion): void
    {
        Assert::true($this->response_checker->get_value($this->client->show(Resources::PROMOTIONS, $promotion->get_code()), 'exclusive'));
    }
    #[Then('the :promotion promotion should be coupon based')]
    public function the_promotion_should_be_coupon_based(Promotion_Interface $promotion): void
    {
        Assert::true($this->response_checker->get_value($this->client->show(Resources::PROMOTIONS, $promotion->get_code()), 'couponBased'));
    }
    #[Then('the :promotion promotion should be applicable for the :channel channel')]
    public function the_promotion_should_be_applicable_for_the_channel(Promotion_Interface $promotion, Channel_Interface $channel): void
    {
        Assert::true($this->response_checker->has_value_in_collection($this->client->show(Resources::PROMOTIONS, $promotion->get_code()), 'channels', $this->iri_converter->get_iri_from_resource($channel)));
    }
    #[When('the :promotion promotion should have a label :label in :localeCode locale')]
    public function the_promotion_should_have_label_in_locale(Promotion_Interface $promotion, string $label, string $locale_code): void
    {
        $response = $this->client->show(Resources::PROMOTIONS, $promotion->get_code());
        Assert::true($this->response_checker->has_translation($response, $locale_code, 'label', $label));
    }
    #[Then('/^it should have ("[^"]+") of item percentage discount configured for ("[^"]+" channel)$/')]
    public function it_should_have_of_item_percentage_discount(float $percentage, Channel_Interface $channel): void
    {
        $actions = $this->response_checker->get_value($this->client->get_last_response(), 'actions');
        foreach ($actions as $action) {
            if ($action['type'] === 'unit_percentage_discount') {
                Assert::same((float) $action['configuration'][$channel->get_code()]['percentage'], $percentage);
            }
        }
    }
    #[Then('/^it should have ("[^"]+") of order percentage discount$/')]
    public function it_should_have_of_order_percentage_discount(float $percentage): void
    {
        $actions = $this->response_checker->get_value($this->client->get_last_response(), 'actions');
        Assert::same((float) $actions[0]['configuration']['percentage'], $percentage);
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->update_request_data(['code' => 'NEW_CODE']);
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'));
    }
    #[Then('the :promotion promotion should be available from :startsDate to :endsDate')]
    public function the_promotion_should_be_available_from_to(Promotion_Interface $promotion, \DateTimeInterface $starts_date, \DateTimeInterface $ends_date): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->index(Resources::PROMOTIONS), ['name' => $promotion->get_name(), 'startsAt' => $starts_date->format('Y-m-d H:i:s'), 'endsAt' => $ends_date->format('Y-m-d H:i:s')]));
    }
    #[Then('I should be able to modify a :promotion promotion')]
    public function i_should_be_able_to_modify_a_promotion(Promotion_Interface $promotion): void
    {
        $this->i_want_to_modify_a_promotion($promotion);
        $this->client->update_request_data(['name' => 'NEW_NAME']);
        Assert::true($this->response_checker->has_value($this->client->update(), 'name', 'NEW_NAME'));
    }
    #[Then('the :promotion promotion should have priority :priority')]
    public function the_promotions_should_have_priority(Promotion_Interface $promotion, int $priority): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->index(Resources::PROMOTIONS), ['name' => $promotion->get_name(), 'priority' => $priority]));
    }
    #[Then('I should be notified that it is in use and cannot be deleted')]
    public function i_should_be_notified_that_it_is_i_use_and_cannot_be_deleted(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot delete, the promotion is in use.');
    }
    #[Then('I should be notified that promotion with this code already exists')]
    public function i_should_be_notified_that_promotion_with_this_code_already_exists(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_creation_successful($response));
        Assert::same($this->response_checker->get_error($response), 'code: The promotion with given code already exists.');
    }
    #[Then('there should still be only one promotion with :element :value')]
    public function there_should_still_be_only_one_promotion_with(string $element, string $value): void
    {
        Assert::count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::PROMOTIONS), $element, $value), 1);
    }
    #[Then('promotion with :element :value should not be added')]
    public function promotion_with_element_value_should_not_be_added(string $element, string $value): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::PROMOTIONS), $element, $value));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s: Please enter promotion %s.', $element, $element));
    }
    #[Then('I should be notified that promotion cannot end before it starts')]
    public function i_should_be_notified_that_promotion_cannot_end_before_its_even_starts(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'endsAt: End date cannot be set prior start date.');
    }
    #[Then('I should be notified that promotion label in :localeCode locale is too long')]
    public function i_should_be_notified_that_promotion_label_is_too_long(string $locale_code): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('translations[%s].label: This value is too long. It should have 255 characters or less.', $locale_code));
    }
    #[Then('I should be notified that this value should not be blank')]
    public function i_should_be_notified_that_this_value_should_not_be_blank(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'This value should not be blank.');
    }
    #[Then('I should be notified that a percentage discount value must be between 0% and 100%')]
    #[Then('I should be notified that a percentage discount value must be at least 0%')]
    #[Then('I should be notified that the maximum value of a percentage discount is 100%')]
    public function i_should_be_notified_that_percentage_discount_should_be_between(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The percentage discount must be between 0% and 100%.');
    }
    #[Then('I should be notified that a minimum value should be a numeric value')]
    public function i_should_be_notified_that_a_minimal_value_should_be_numeric(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), '[min]: This value should be of type numeric.');
    }
    #[Then('I should be notified that a maximum value should be a numeric value')]
    public function i_should_be_notified_that_a_maximum_value_should_be_numeric(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), '[max]: This value should be of type numeric.');
    }
    #[Then('I should see :count promotions on the list')]
    #[Then('I should see a single promotion on the list')]
    public function i_should_see_promotion_in_the_list(int $count = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('/^the (first|last) promotion on the list should have ([^"]+) "([^"]+)"$/')]
    public function the_first_promotion_on_the_list_should_have(string $toggle_position, string $field, string $value): void
    {
        $items = $this->response_checker->get_value($this->client->get_last_response(), 'hydra:member');
        if ('first' === $toggle_position) {
            $item = reset($items);
        } else {
            $item = end($items);
        }
        Assert::same($item[$field], $value);
    }
    #[Then('the promotion :promotion should be used :usage time(s)')]
    #[Then('the promotion :promotion should not be used')]
    public function the_promotion_should_be_used_time(Promotion_Interface $promotion, int $usage = 0): void
    {
        $returned_promotion = current($this->response_checker->get_collection_items_with_value($this->client->get_last_response(), 'code', $promotion->get_code()));
        Assert::same($returned_promotion['used'], $usage, sprintf('The promotion %s has been used %s times', $promotion->get_name(), $returned_promotion['used']));
    }
    #[Then('I should be viewing non archival promotions')]
    public function i_should_be_viewing_non_archival_promotions(): void
    {
        $this->client->index(Resources::PROMOTIONS);
    }
    private function add_to_request_action(string $type, array $configuration): void
    {
        $data['actions'][] = ['type' => $type, 'configuration' => $configuration];
        $this->client->update_request_data($data);
    }
    private function get_actions(): array
    {
        return $this->client->get_content()['actions'];
    }
    private function add_to_request_rule(string $type, array $configuration): void
    {
        $data['rules'][] = ['type' => $type, 'configuration' => $configuration];
        $this->client->update_request_data($data);
    }
}
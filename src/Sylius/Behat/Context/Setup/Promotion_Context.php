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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Api_Bundle\Command\Checkout\Update_Cart;
use Sylius\Bundle\Core_Bundle\Fixture\Factory\Example_Factory_Interface;
use Sylius\Component\Core\Factory\Promotion_Action_Factory_Interface;
use Sylius\Component\Core\Factory\Promotion_Rule_Factory_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Promotion_Coupon_Interface;
use Sylius\Component\Core\Model\Promotion_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Core\Promotion\Checker\Rule\Contains_Product_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Customer_Group_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Has_Taxon_Rule_Checker;
use Sylius\Component\Core\Promotion\Checker\Rule\Total_Of_Items_From_Taxon_Rule_Checker;
use Sylius\Component\Customer\Model\Customer_Group_Interface;
use Sylius\Component\Promotion\Factory\Promotion_Coupon_Factory_Interface;
use Sylius\Component\Promotion\Generator\Promotion_Coupon_Generator_Instruction;
use Sylius\Component\Promotion\Generator\Promotion_Coupon_Generator_Interface;
use Sylius\Component\Promotion\Model\Promotion_Action_Interface;
use Sylius\Component\Promotion\Model\Promotion_Rule_Interface;
use Sylius\Component\Promotion\Repository\Promotion_Repository_Interface;
use Symfony\Component\Messenger\Message_Bus_Interface;
final readonly class Promotion_Context implements Context
{
    /**
     * @param PromotionActionFactoryInterface<PromotionActionInterface> $actionFactory
     * @param PromotionCouponFactoryInterface<PromotionCouponInterface> $couponFactory
     * @param PromotionRuleFactoryInterface<PromotionRuleInterface> $ruleFactory
     * @param PromotionRepositoryInterface<PromotionInterface> $promotionRepository
     */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Promotion_Action_Factory_Interface $action_factory, private Promotion_Coupon_Factory_Interface $coupon_factory, private Promotion_Rule_Factory_Interface $rule_factory, private Promotion_Repository_Interface $promotion_repository, private Promotion_Coupon_Generator_Interface $coupon_generator, private Object_Manager $object_manager, private Example_Factory_Interface $promotion_example_factory, private Message_Bus_Interface $command_bus)
    {
    }
    #[Given('there is (also) a promotion :name')]
    #[Given('there is a promotion :name that applies to discounted products')]
    #[Given('there is a promotion :name identified by :code code')]
    public function there_is_promotion(string $name, ?string $code = null): void
    {
        $this->create_promotion(name: $name, code: $code, startsAt: (new \DateTime('-3 day'))->format('Y-m-d'), endsAt: (new \DateTime('+3 day'))->format('Y-m-d'));
    }
    #[Given('I applied the coupon with code :couponCode')]
    public function i_applied_the_coupon_with_code(string $coupon_code): void
    {
        $this->command_bus->dispatch(new Update_Cart($this->shared_storage->get('cart_token'), couponCode: $coupon_code));
    }
    #[Given('/^there is a promotion "([^"]+)" with "Has at least one from taxons" rule (configured with "[^"]+" and "[^"]+")$/')]
    public function there_is_a_promotion_with_has_at_least_one_from_taxons_rule_configured_with(string $name, iterable $taxons): void
    {
        $taxon_codes = array_map(fn(Taxon_Interface $taxon) => $taxon->get_code(), iterator_to_array($taxons));
        $this->create_promotion(name: $name, rules: [['type' => Has_Taxon_Rule_Checker::TYPE, 'configuration' => ['taxons' => $taxon_codes]]], startsAt: (new \DateTime('-3 day'))->format('Y-m-d'), endsAt: (new \DateTime('+3 day'))->format('Y-m-d'));
    }
    #[Given('/^there is a promotion "([^"]+)" with "Total price of items from taxon" rule configured with ("[^"]+" taxon) and (?:€|£|\$)([^"]+) amount for ("[^"]+" channel)$/')]
    public function there_is_a_promotion_with_total_price_of_items_from_taxon_rule_configured_with_taxon_and_amount_for_channel(string $name, Taxon_Interface $taxon, int $amount, Channel_Interface $channel): void
    {
        $this->rule_factory->create_items_from_taxon_total($channel->get_code(), $taxon->get_code(), $amount);
        $this->create_promotion(name: $name, rules: [['type' => Total_Of_Items_From_Taxon_Rule_Checker::TYPE, 'configuration' => [$channel->get_code() => ['taxon' => $taxon->get_code(), 'amount' => $amount]]]], startsAt: (new \DateTime('-3 day'))->format('Y-m-d'), endsAt: (new \DateTime('+3 day'))->format('Y-m-d'));
    }
    #[Given('/^there is a promotion "([^"]+)" with "Contains product" rule with (products "[^"]+" and "[^"]+")$/')]
    public function there_is_a_promotion_with_contains_product_rule_configured_with_products(string $name, array $products): void
    {
        $rules = [];
        foreach ($products as $product) {
            $rules[] = ['type' => Contains_Product_Rule_Checker::TYPE, 'configuration' => ['product_code' => $product->get_code()]];
        }
        $this->create_promotion(name: $name, rules: $rules, startsAt: (new \DateTime('-3 day'))->format('Y-m-d'), endsAt: (new \DateTime('+3 day'))->format('Y-m-d'));
    }
    #[Given('/^there is a promotion "([^"]+)" with "Contains product" rule with (product "[^"]+")$/')]
    public function there_is_a_promotion_with_contains_product_rule_configured_with_product(string $name, Product_Interface $product): void
    {
        $this->there_is_a_promotion_with_contains_product_rule_configured_with_products($name, [$product]);
    }
    #[Given('/^there is a promotion "([^"]+)" with priority ([^"]+)$/')]
    public function there_is_a_promotion_with_priority(string $promotion_name, int $priority): void
    {
        $this->create_promotion(name: $promotion_name, priority: $priority, startsAt: (new \DateTime('-3 day'))->format('Y-m-d'), endsAt: (new \DateTime('+3 day'))->format('Y-m-d'));
    }
    #[Given('/^there is an exclusive promotion "([^"]+)"(?:| with priority (\d+))$/')]
    public function there_is_an_exclusive_promotion_with_priority(string $promotion_name, int $priority = 0): void
    {
        $this->create_promotion(name: $promotion_name, priority: $priority, exclusive: true, startsAt: (new \DateTime('-3 day'))->format('Y-m-d'), endsAt: (new \DateTime('+3 day'))->format('Y-m-d'));
    }
    #[Given('there is a promotion :promotionName limited to :usageLimit usages')]
    public function there_is_promotion_limited_to_usages(string $promotion_name, int $usage_limit): void
    {
        $this->create_promotion(name: $promotion_name, usageLimit: $usage_limit, startsAt: (new \DateTime('-3 day'))->format('Y-m-d'), endsAt: (new \DateTime('+3 day'))->format('Y-m-d'));
    }
    #[Given('the store has promotion :promotionName with coupon :couponCode')]
    #[Given('the store has a promotion :promotionName with a coupon :couponCode that is limited to :usageLimit usages')]
    public function there_is_promotion_with_coupon(string $promotion_name, string $coupon_code, ?int $usage_limit = null): void
    {
        $promotion = $this->create_promotion(name: $promotion_name, coupons: [['code' => $coupon_code, 'usage_limit' => $usage_limit]], couponBased: true, startsAt: (new \DateTime('-3 day'))->format('Y-m-d'), endsAt: (new \DateTime('+3 day'))->format('Y-m-d'));
        $this->shared_storage->set('coupon', $promotion->get_coupons()->first());
    }
    #[Given('there is a promotion :name that does not apply to discounted products')]
    public function there_is_a_promotion_that_does_not_apply_to_discounted_products(string $name): void
    {
        $this->create_promotion(name: $name, appliesToDiscounted: false, startsAt: (new \DateTime('-3 day'))->format('Y-m-d'), endsAt: (new \DateTime('+3 day'))->format('Y-m-d'));
    }
    #[Given('/^(this promotion) has "([^"]+)", "([^"]+)" and "([^"]+)" coupons/')]
    public function this_promotion_has_coupons(Promotion_Interface $promotion, string ...$coupon_codes): void
    {
        foreach ($coupon_codes as $coupon_code) {
            $coupon = $this->create_coupon($coupon_code);
            $promotion->add_coupon($coupon);
        }
        $promotion->set_coupon_based(true);
        $this->object_manager->flush();
    }
    #[Given('/^(this promotion) does not apply on discounted products$/')]
    public function this_promotion_does_not_apply_on_discounted_products(Promotion_Interface $promotion): void
    {
        $promotion->set_applies_to_discounted(false);
        $this->object_manager->flush();
    }
    #[Given('/^(this promotion) has already expired$/')]
    public function this_promotion_has_expired(Promotion_Interface $promotion): void
    {
        $promotion->set_ends_at(new \DateTime('1 day ago'));
        $this->object_manager->flush();
    }
    #[Given('/^(this promotion) is valid until tomorrow$/')]
    public function this_promotion_is_valid_until_tomorrow(Promotion_Interface $promotion): void
    {
        $promotion->set_ends_at(new \DateTime('tomorrow'));
        $this->object_manager->flush();
    }
    #[Given('/^(this promotion) started yesterday$/')]
    public function this_promotion_started_yesterday(Promotion_Interface $promotion): void
    {
        $promotion->set_starts_at(new \DateTime('1 day ago'));
        $this->object_manager->flush();
    }
    #[Given('/^(this promotion) starts tomorrow$/')]
    public function this_promotion_starts_tomorrow(Promotion_Interface $promotion): void
    {
        $promotion->set_starts_at(new \DateTime('tomorrow'));
        $this->object_manager->flush();
    }
    #[Given('the promotion :promotion is archived')]
    public function this_promotion_is_archived(Promotion_Interface $promotion): void
    {
        $promotion->set_archived_at(new \DateTime());
        $this->object_manager->flush();
    }
    #[Given('/^(this coupon) has already expired$/')]
    public function this_coupon_has_expired(Promotion_Coupon_Interface $coupon): void
    {
        $coupon->set_expires_at(new \DateTime('1 day ago'));
        $this->object_manager->flush();
    }
    #[Given('/^(this coupon) is valid until tomorrow$/')]
    public function this_coupon_is_valid_until_tomorrow(Promotion_Coupon_Interface $coupon): void
    {
        $coupon->set_expires_at(new \DateTime('tomorrow'));
        $this->object_manager->flush();
    }
    #[Given('/^(this coupon) is set as non reusable after cancelling the order in which it has been used$/')]
    public function this_is_set_as_non_reusable_after_cancelling_the_order_in_which_it_has_been_used(Promotion_Coupon_Interface $coupon): void
    {
        $coupon->set_reusable_from_cancelled_orders(false);
        $this->object_manager->flush();
    }
    #[Given('/^(this coupon) has already reached its usage limit$/')]
    public function this_coupon_has_reached_its_usage_limit(Promotion_Coupon_Interface $coupon): void
    {
        $coupon->set_used(42);
        $coupon->set_usage_limit(42);
        $this->object_manager->flush();
    }
    #[Given('/^(this coupon) can be used (\d+) times?$/')]
    #[Given('/^(this coupon) can be used once$/')]
    public function this_coupon_can_be_used_n_times(Promotion_Coupon_Interface $coupon, int $usage_limit = 1): void
    {
        $coupon->set_usage_limit($usage_limit);
        $this->object_manager->flush();
    }
    #[Given('/^(this coupon) can be used once per customer$/')]
    public function this_coupon_can_be_used_once_per_customer(Promotion_Coupon_Interface $coupon): void
    {
        $coupon->set_per_customer_usage_limit(1);
        $this->object_manager->flush();
    }
    #[Given('/^(this coupon) can be used twice per customer$/')]
    public function this_coupon_can_be_used_twice_per_customer(Promotion_Coupon_Interface $coupon): void
    {
        $coupon->set_per_customer_usage_limit(2);
        $this->object_manager->flush();
    }
    #[Given('/^(this promotion) has coupon "([^"]+)"$/')]
    public function this_promotion_has_coupon(Promotion_Interface $promotion, string $coupon_code): void
    {
        $coupon = $this->create_coupon($coupon_code);
        $promotion->add_coupon($coupon);
        $promotion->set_coupon_based(true);
        $this->shared_storage->set('coupon', $coupon);
        $this->object_manager->flush();
    }
    #[Given('/^(this coupon) can be used (\d+) times? per customer$/')]
    #[Given('/^(this coupon) has no per customer usage limit$/')]
    public function this_coupon_can_be_used_times_per_customer(Promotion_Coupon_Interface $coupon, ?int $usage_limit = null): void
    {
        $coupon->set_per_customer_usage_limit($usage_limit);
        $this->object_manager->flush();
    }
    #[Given('/^(this coupon) can be used (\d+) times per customer with overall usage limit of (\d+)$/')]
    public function this_coupon_can_be_used_times_per_customer_with_overall_usage_limit_of(Promotion_Coupon_Interface $coupon, int $per_customer_usage_limit, int $overall_usage_limit): void
    {
        $this->this_coupon_can_be_used_times_per_customer($coupon, $per_customer_usage_limit);
        $this->this_coupon_can_be_used_n_times($coupon, $overall_usage_limit);
    }
    #[Given('/^(this coupon) has been used (\d+) times?$/')]
    public function this_coupon_has_been_used_times(Promotion_Coupon_Interface $coupon, int $used): void
    {
        $coupon->set_used($used);
        $this->object_manager->flush();
    }
    #[Given('/^(this coupon) expires (on "[^"]+")$/')]
    public function this_coupon_expires_on(Promotion_Coupon_Interface $coupon, \DateTimeInterface $date): void
    {
        $coupon->set_expires_at($date);
        $this->object_manager->flush();
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") discount to every order$/')]
    public function it_gives_fixed_discount_to_every_order(Promotion_Interface $promotion, int $discount): void
    {
        $this->create_fixed_promotion($promotion, $discount);
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") discount to every order in the ("[^"]+" channel) and ("(?:€|£|\$)[^"]+") discount to every order in the ("[^"]+" channel)$/')]
    public function this_promotion_gives_discount_to_every_order_in_the_channel_and_discount_to_every_order_in_the_channel(Promotion_Interface $promotion, int $first_channel_discount, Channel_Interface $first_channel, int $second_channel_discount, Channel_Interface $second_channel): void
    {
        $action = $this->action_factory->create_fixed_discount($first_channel_discount, $first_channel->get_code());
        $action->set_configuration(array_merge($action->get_configuration(), [$second_channel->get_code() => ['amount' => $second_channel_discount]]));
        $promotion->add_channel($first_channel);
        $promotion->add_channel($second_channel);
        $promotion->add_action($action);
        $this->object_manager->flush();
    }
    #[Given('/^(this promotion) gives ("(?:€|£|\$)[^"]+") off on every product in the ("[^"]+" channel) and ("(?:€|£|\$)[^"]+") off in the ("[^"]+" channel)$/')]
    public function this_promotion_gives_fixed_discount_on_every_product_in_the_channel_and_in_the_channel(Promotion_Interface $promotion, int $first_amount, Channel_Interface $first_channel, int $second_amount, Channel_Interface $second_channel): void
    {
        $action = $this->action_factory->create_unit_fixed_discount($first_amount, $first_channel->get_code());
        $action->set_configuration(array_merge($action->get_configuration(), [$second_channel->get_code() => ['amount' => $second_amount]]));
        $promotion->add_channel($first_channel);
        $promotion->add_channel($second_channel);
        $promotion->add_action($action);
        $this->object_manager->flush();
    }
    #[Given('/^(this promotion) gives ("[^"]+%") off on every product in the ("[^"]+" channel) and ("[^"]+%") off in the ("[^"]+" channel)$/')]
    public function this_promotion_gives_percentage_discount_on_every_product_in_the_channel_and_in_the_channel(Promotion_Interface $promotion, float $first_percentage, Channel_Interface $first_channel, float $second_percentage, Channel_Interface $second_channel): void
    {
        $action = $this->action_factory->create_unit_percentage_discount($first_percentage, $first_channel->get_code());
        $action->set_configuration(array_merge($action->get_configuration(), [$second_channel->get_code() => ['percentage' => $second_percentage]]));
        $promotion->add_channel($first_channel);
        $promotion->add_channel($second_channel);
        $promotion->add_action($action);
        $this->object_manager->flush();
    }
    #[Given('/^([^"]+) gives ("[^"]+%") discount to every order$/')]
    public function it_gives_percentage_discount_to_every_order(Promotion_Interface $promotion, float $discount): void
    {
        $this->create_percentage_promotion($promotion, $discount);
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") discount to every order with quantity at least ([^"]+)$/')]
    public function it_gives_fixed_discount_to_every_order_with_quantity_at_least(Promotion_Interface $promotion, int $discount, int $quantity): void
    {
        $rule = $this->rule_factory->create_cart_quantity($quantity);
        $this->create_fixed_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") discount to every order with items total at least ("[^"]+")$/')]
    public function it_gives_fixed_discount_to_every_order_with_items_total_at_least(Promotion_Interface $promotion, int $discount, int $target_amount): void
    {
        $channel_code = $this->get_channel_code();
        $rule = $this->rule_factory->create_item_total($channel_code, $target_amount);
        $this->create_fixed_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("[^"]+%") discount to every order with items total at least ("[^"]+")$/')]
    public function it_gives_percentage_discount_to_every_order_with_items_total_at_least(Promotion_Interface $promotion, float $discount, int $target_amount): void
    {
        $channel_code = $this->get_channel_code();
        $rule = $this->rule_factory->create_item_total($channel_code, $target_amount);
        $this->create_percentage_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("[^"]+%") off on every product when the item total is at least ("(?:€|£|\$)[^"]+")$/')]
    public function it_gives_off_on_every_item_when_item_total_exceeds(Promotion_Interface $promotion, float $discount, int $target_amount): void
    {
        $channel_code = $this->get_channel_code();
        $rule = $this->rule_factory->create_item_total($channel_code, $target_amount);
        $this->create_unit_percentage_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("[^"]+%") discount on shipping to every order$/')]
    public function it_gives_percentage_discount_on_shipping_to_every_order(Promotion_Interface $promotion, float $discount): void
    {
        $action = $this->action_factory->create_shipping_percentage_discount($discount);
        $promotion->add_action($action);
        $this->object_manager->flush();
    }
    #[Given('/^([^"]+) gives free shipping to every order$/')]
    public function the_promotion_gives_free_shipping_to_every_order(Promotion_Interface $promotion): void
    {
        $this->it_gives_percentage_discount_on_shipping_to_every_order($promotion, 1);
    }
    #[Given('/^([^"]+) gives(?:| another) ("[^"]+%") off every product (classified as "[^"]+")$/')]
    public function it_gives_percentage_off_every_product_classified_as(Promotion_Interface $promotion, float $discount, Taxon_Interface $taxon): void
    {
        $this->create_unit_percentage_promotion($promotion, $discount, $this->get_taxon_filter_configuration([$taxon->get_code()]));
    }
    #[Given('/^([^"]+) gives(?:| another) ("(?:€|£|\$)[^"]+") off on every product (classified as "[^"]+")$/')]
    public function it_gives_fixed_off_every_product_classified_as(Promotion_Interface $promotion, int $discount, Taxon_Interface $taxon): void
    {
        $this->create_unit_fixed_promotion($promotion, $discount, $this->get_taxon_filter_configuration([$taxon->get_code()]));
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") off on every product with minimum price at ("(?:€|£|\$)[^"]+")$/')]
    public function this_promotion_gives_off_on_every_product_with_minimum_price_at(Promotion_Interface $promotion, int $discount, int $amount): void
    {
        $this->create_unit_fixed_promotion($promotion, $discount, $this->get_price_range_filter_configuration($amount));
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") off on every product with maximum price at ("(?:€|£|\$)[^"]+")$/')]
    public function this_promotion_gives_off_on_every_product_with_maximum_price_at(Promotion_Interface $promotion, int $discount, int $amount): void
    {
        $this->create_unit_fixed_promotion($promotion, $discount, $this->get_price_range_filter_configuration(maxAmount: $amount));
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") off on every product priced between ("(?:€|£|\$)[^"]+") and ("(?:€|£|\$)[^"]+")$/')]
    public function this_promotion_gives_off_on_every_product_priced_between(Promotion_Interface $promotion, int $discount, int $min_amount, int $max_amount): void
    {
        $this->create_unit_fixed_promotion($promotion, $discount, $this->get_price_range_filter_configuration($min_amount, $max_amount));
    }
    #[Given('/^([^"]+) gives ("[^"]+%") off on every product with minimum price at ("(?:€|£|\$)[^"]+")$/')]
    public function this_promotion_percentage_gives_off_on_every_product_with_minimum_price_at(Promotion_Interface $promotion, float $discount, int $amount): void
    {
        $this->create_unit_percentage_promotion($promotion, $discount, $this->get_price_range_filter_configuration($amount));
    }
    #[Given('/^([^"]+) gives ("[^"]+%") off on every product with maximum price at ("(?:€|£|\$)[^"]+")$/')]
    public function this_promotion_percentage_gives_off_on_every_product_with_maximum_price_at(Promotion_Interface $promotion, float $discount, int $amount): void
    {
        $this->create_unit_percentage_promotion($promotion, $discount, $this->get_price_range_filter_configuration(maxAmount: $amount));
    }
    #[Given('/^([^"]+) gives ("[^"]+%") off on every product priced between ("(?:€|£|\$)[^"]+") and ("(?:€|£|\$)[^"]+")$/')]
    public function this_promotion_percentage_gives_off_on_every_product_priced_between(Promotion_Interface $promotion, float $discount, int $min_amount, int $max_amount): void
    {
        $this->create_unit_percentage_promotion($promotion, $discount, $this->get_price_range_filter_configuration($min_amount, $max_amount));
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") off if order contains products (classified as "[^"]+")$/')]
    public function the_promotion_gives_off_if_order_contains_products_classified_as(Promotion_Interface $promotion, int $discount, Taxon_Interface $taxon): void
    {
        $rule = $this->rule_factory->create_has_taxon([$taxon->get_code()]);
        $this->create_fixed_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") off if order contains products (classified as "[^"]+" or "[^"]+")$/')]
    public function the_promotion_gives_off_if_order_contains_products_classified_as_or(Promotion_Interface $promotion, int $discount, iterable $taxons): void
    {
        $taxon_codes = array_map(fn(Taxon_Interface $taxon) => $taxon->get_code(), iterator_to_array($taxons));
        $rule = $this->rule_factory->create_has_taxon($taxon_codes);
        $this->create_fixed_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") off if order contains products (classified as "[^"]+") with a minimum value of ("(?:€|£|\$)[^"]+")$/')]
    public function the_promotion_gives_off_if_order_contains_products_classified_as_and_priced_at(Promotion_Interface $promotion, int $discount, Taxon_Interface $taxon, int $amount): void
    {
        $channel_code = $this->get_channel_code();
        $rule = $this->rule_factory->create_items_from_taxon_total($channel_code, $taxon->get_code(), $amount);
        $this->create_fixed_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") off customer\'s (\d)(?:st|nd|rd|th) order$/')]
    public function it_gives_fixed_off_customers_nth_order(Promotion_Interface $promotion, int $discount, int $nth): void
    {
        $rule = $this->rule_factory->create_nth_order($nth);
        $this->create_fixed_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("[^"]+%") off on the customer\'s (\d)(?:st|nd|rd|th) order$/')]
    public function it_gives_percentage_off_customers_nth_order(Promotion_Interface $promotion, float $discount, int $nth): void
    {
        $rule = $this->rule_factory->create_nth_order($nth);
        $this->create_percentage_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("[^"]+%") off on every product (classified as "[^"]+") and ("(?:€|£|\$)[^"]+") discount on every order$/')]
    public function it_gives_percentage_off_on_every_product_classified_as_and_amount_discount_on_order(Promotion_Interface $promotion, float $product_discount, Taxon_Interface $discount_taxon, int $order_discount): void
    {
        $this->create_unit_percentage_promotion($promotion, $product_discount, $this->get_taxon_filter_configuration([$discount_taxon->get_code()]));
        $this->create_fixed_promotion($promotion, $order_discount);
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") off on every product classified as "[^"]+" and a free shipping to every order with items total equal at least ("[^"]+")$/')]
    public function it_gives_off_on_every_product_classified_as_and_a_free_shipping_to_every_order_with_items_total_equal_at_least(Promotion_Interface $promotion, int $discount, int $target_amount): void
    {
        $free_shipping_action = $this->action_factory->create_shipping_percentage_discount(1);
        $promotion->add_action($free_shipping_action);
        $channel_code = $this->get_channel_code();
        $rule = $this->rule_factory->create_item_total($channel_code, $target_amount);
        $this->create_unit_fixed_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("[^"]+%") off on every product (classified as "[^"]+") and a ("(?:€|£|\$)[^"]+") discount to every order with items total equal at least ("(?:€|£|\$)[^"]+")$/')]
    public function it_gives_off_on_every_product_classified_as_and_a_fixed_discount_to_every_order_with_items_total_equal_at_least(Promotion_Interface $promotion, float $taxon_discount, Taxon_Interface $taxon, int $order_discount, int $target_amount): void
    {
        $channel_code = $this->get_channel_code();
        $order_discount_action = $this->action_factory->create_fixed_discount($order_discount, $channel_code);
        $promotion->add_action($order_discount_action);
        $rule = $this->rule_factory->create_item_total($channel_code, $target_amount);
        $this->create_unit_percentage_promotion($promotion, $taxon_discount, $this->get_taxon_filter_configuration([$taxon->get_code()]), $rule);
    }
    #[Given('/^([^"]+) gives ("[^"]+%") off on every product (classified as "[^"]+" or "[^"]+") if order contains any product (classified as "[^"]+" or "[^"]+")$/')]
    public function it_gives_off_on_every_product_classified_as_or_if_order_contains_any_product_classified_as_or(Promotion_Interface $promotion, float $discount, iterable $discount_taxons, iterable $target_taxons): void
    {
        $discount_taxons_codes = array_map(fn(Taxon_Interface $taxon) => $taxon->get_code(), iterator_to_array($discount_taxons));
        $target_taxons_codes = array_map(fn(Taxon_Interface $taxon) => $taxon->get_code(), iterator_to_array($target_taxons));
        $rule = $this->rule_factory->create_has_taxon($target_taxons_codes);
        $this->create_unit_percentage_promotion($promotion, $discount, $this->get_taxon_filter_configuration($discount_taxons_codes), $rule);
    }
    #[Given('/^([^"]+) gives ("[^"]+%") off on every product (classified as "[^"]+") if order contains any product (classified as "[^"]+")$/')]
    public function it_gives_off_on_every_product_classified_as_if_order_contains_any_product_classified_as(Promotion_Interface $promotion, float $discount, Taxon_Interface $discount_taxon, Taxon_Interface $target_taxon): void
    {
        $rule = $this->rule_factory->create_has_taxon([$target_taxon->get_code()]);
        $this->create_unit_percentage_promotion($promotion, $discount, $this->get_taxon_filter_configuration([$discount_taxon->get_code()]), $rule);
    }
    #[Given('/^(it) is coupon based promotion$/')]
    #[Given('/^(it) is a coupon based promotion$/')]
    public function it_is_coupon_based_promotion(Promotion_Interface $promotion): void
    {
        $promotion->set_coupon_based(true);
        $this->object_manager->flush();
    }
    #[Given('/^(the promotion) was disabled for the (channel "[^"]+")$/')]
    public function the_promotion_was_disabled_for_the_channel(Promotion_Interface $promotion, Channel_Interface $channel): void
    {
        $promotion->remove_channel($channel);
        $this->object_manager->flush();
    }
    #[Given('/^the (coupon "[^"]+") was used up to its usage limit$/')]
    public function the_coupon_was_used(Promotion_Coupon_Interface $coupon): void
    {
        $coupon->set_used($coupon->get_usage_limit());
        $this->object_manager->flush();
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") off if order contains (?:a|an) ("[^"]+" product)$/')]
    public function the_promotion_gives_off_if_order_contains_products(Promotion_Interface $promotion, int $discount, Product_Interface $product): void
    {
        $rule = $this->rule_factory->create_contains_product($product->get_code());
        $this->create_fixed_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("(?:€|£|\$)[^"]+") off on a ("[^"]*" product)$/')]
    public function it_gives_fixed_discount_off_on_a_product(Promotion_Interface $promotion, int $discount, Product_Interface $product): void
    {
        $this->create_unit_fixed_promotion($promotion, $discount, $this->get_products_filter_configuration([$product->get_code()]));
    }
    #[Given('/^([^"]+) gives ("[^"]+%") off on a ("[^"]*" product)$/')]
    public function it_gives_percentage_discount_off_on_a_product(Promotion_Interface $promotion, float $percentage, Product_Interface $product): void
    {
        $this->create_unit_percentage_promotion($promotion, $percentage, $this->get_products_filter_configuration([$product->get_code()]));
    }
    #[Given('/^([^"]+) gives ("[^"]+%") off the order for customers from ("[^"]*" group)$/')]
    public function the_promotion_gives_off_the_order_for_customers_from_group(Promotion_Interface $promotion, float $discount, Customer_Group_Interface $customer_group): void
    {
        /** @var PromotionRuleInterface $rule */
        $rule = $this->rule_factory->create_new();
        $rule->set_type(Customer_Group_Rule_Checker::TYPE);
        $rule->set_configuration(['group_code' => $customer_group->get_code()]);
        $this->create_percentage_promotion($promotion, $discount, [], $rule);
    }
    #[Given('/^([^"]+) gives ("[^"]+%") discount on shipping to every order over ("(?:€|£|\$)[^"]+")$/')]
    public function it_gives_discount_on_shipping_to_every_order_over(Promotion_Interface $promotion, float $discount, int $item_total): void
    {
        $channel_code = $this->get_channel_code();
        $rule = $this->rule_factory->create_item_total($channel_code, $item_total);
        $action = $this->action_factory->create_shipping_percentage_discount($discount);
        $this->persist_promotion($promotion, $action, [], $rule);
    }
    #[Given('/^([^"]+) gives free shipping to every order over ("(?:€|£|\$)[^"]+")$/')]
    public function it_gives_free_shipping_to_every_order_over(Promotion_Interface $promotion, int $item_total): void
    {
        $this->it_gives_discount_on_shipping_to_every_order_over($promotion, 1, $item_total);
    }
    #[Given('/^I have generated (\d+) coupons for (this promotion) with code length (\d+) and prefix "([^"]+)"$/')]
    #[Given('/^I have generated (\d+) coupons for (this promotion) with code length (\d+), prefix "([^"]+)" and suffix "([^"]+)"$/')]
    public function i_have_generated_coupons_for_this_promotion_with_code_length_prefix_and_suffix(int $amount, Promotion_Interface $promotion, int $code_length, string $prefix, ?string $suffix = null): void
    {
        $this->generate_coupons($amount, $promotion, $code_length, $prefix, $suffix);
    }
    #[Given('/^I have generated (\d+) coupons for (this promotion) with code length (\d+) and suffix "([^"]+)"$/')]
    public function i_have_generated_coupons_for_this_promotion_with_code_length_and_suffix(int $amount, Promotion_Interface $promotion, int $code_length, string $suffix): void
    {
        $this->generate_coupons($amount, $promotion, $code_length, null, $suffix);
    }
    #[Given('/^(this promotion) is not available in any channel$/')]
    public function this_promotion_is_not_available_in_any_channel(Promotion_Interface $promotion): void
    {
        /** @var ChannelInterface $channel */
        foreach ($promotion->get_channels() as $channel) {
            $promotion->remove_channel($channel);
        }
        $this->object_manager->flush();
    }
    #[Given('/^(this promotion) has usage limit equal to (\d+)$/')]
    public function this_promotion_has_usage_limit_equal_to(Promotion_Interface $promotion, int $usage_limit): void
    {
        $promotion->set_usage_limit($usage_limit);
        $this->object_manager->flush();
    }
    #[Given('/^(this promotion) usage limit is already reached$/')]
    public function this_promotion_usage_limit_is_already_reached(Promotion_Interface $promotion): void
    {
        $promotion->set_used($promotion->get_usage_limit());
        $this->object_manager->flush();
    }
    #[Given('/^(this promotion) only applies to orders with a total of at least ("[^"]+") for ("[^"]+" channel) and ("[^"]+") for ("[^"]+" channel)$/')]
    public function this_promotion_only_applies_to_orders_with_total_of_at_least_for_and_for(Promotion_Interface $promotion, int $first_amount, Channel_Interface $first_channel, int $second_amount, Channel_Interface $second_channel): void
    {
        $promotion->add_rule($this->rule_factory->create_item_total($first_channel->get_code(), $first_amount));
        $promotion->add_rule($this->rule_factory->create_item_total($second_channel->get_code(), $second_amount));
        $this->object_manager->flush();
    }
    private function get_taxon_filter_configuration(array $taxon_codes): array
    {
        return ['filters' => ['taxons_filter' => ['taxons' => $taxon_codes]]];
    }
    private function get_products_filter_configuration(array $product_codes): array
    {
        return ['filters' => ['products_filter' => ['products' => $product_codes]]];
    }
    private function get_price_range_filter_configuration(?int $min_amount = null, ?int $max_amount = null): array
    {
        $configuration = [];
        if (null !== $min_amount) {
            $configuration['filters']['price_range_filter']['min'] = $min_amount;
        }
        if (null !== $max_amount) {
            $configuration['filters']['price_range_filter']['max'] = $max_amount;
        }
        return $configuration;
    }
    private function create_promotion(string $name, ?string $description = null, ?string $code = null, array $channels = [], ?array $rules = null, ?array $actions = null, array $coupons = [], ?int $priority = null, ?int $usage_limit = null, bool $coupon_based = false, bool $exclusive = false, bool $applies_to_discounted = true, ?string $starts_at = null, ?string $ends_at = null): Promotion_Interface
    {
        if (empty($channels) && $this->shared_storage->has('channel')) {
            $channels = [$this->shared_storage->get('channel')];
        }
        $code ??= String_Inflector::name_to_code($name);
        /** @var PromotionInterface $promotion */
        $promotion = $this->promotion_example_factory->create(['name' => $name, 'description' => $description, 'code' => $code, 'channels' => $channels, 'rules' => $rules, 'actions' => $actions, 'coupons' => $coupons, 'priority' => $priority, 'usage_limit' => $usage_limit, 'coupon_based' => $coupon_based, 'exclusive' => $exclusive, 'applies_to_discounted' => $applies_to_discounted, 'starts_at' => $starts_at, 'ends_at' => $ends_at]);
        $this->promotion_repository->add($promotion);
        $this->shared_storage->set('promotion', $promotion);
        return $promotion;
    }
    private function create_unit_fixed_promotion(Promotion_Interface $promotion, int $discount, array $configuration = [], ?Promotion_Rule_Interface $rule = null): void
    {
        $channel_code = $this->get_channel_code();
        $this->persist_promotion($promotion, $this->action_factory->create_unit_fixed_discount($discount, $channel_code), [$channel_code => $configuration], $rule);
    }
    private function create_unit_percentage_promotion(Promotion_Interface $promotion, float $percentage, array $configuration = [], ?Promotion_Rule_Interface $rule = null): void
    {
        $channel_code = $this->get_channel_code();
        $this->persist_promotion($promotion, $this->action_factory->create_unit_percentage_discount($percentage, $channel_code), [$channel_code => $configuration], $rule);
    }
    private function create_fixed_promotion(Promotion_Interface $promotion, int $discount, array $configuration = [], ?Promotion_Rule_Interface $rule = null, ?Channel_Interface $channel = null): void
    {
        $channel_code = null !== $channel ? $channel->get_code() : $this->shared_storage->get('channel')->get_code();
        $this->persist_promotion($promotion, $this->action_factory->create_fixed_discount($discount, $channel_code), $configuration, $rule);
    }
    private function create_percentage_promotion(Promotion_Interface $promotion, float $discount, array $configuration = [], ?Promotion_Rule_Interface $rule = null): void
    {
        $this->persist_promotion($promotion, $this->action_factory->create_percentage_discount($discount), $configuration, $rule);
    }
    private function persist_promotion(Promotion_Interface $promotion, Promotion_Action_Interface $action, array $configuration, ?Promotion_Rule_Interface $rule = null): void
    {
        $configuration = array_merge_recursive($action->get_configuration(), $configuration);
        $action->set_configuration($configuration);
        $promotion->add_action($action);
        if (null !== $rule) {
            $promotion->add_rule($rule);
        }
        $this->object_manager->flush();
    }
    private function create_coupon(string $coupon_code, ?int $usage_limit = null): Promotion_Coupon_Interface
    {
        /** @var PromotionCouponInterface $coupon */
        $coupon = $this->coupon_factory->create_new();
        $coupon->set_code($coupon_code);
        $coupon->set_usage_limit($usage_limit);
        return $coupon;
    }
    private function generate_coupons(int $amount, Promotion_Interface $promotion, int $code_length, ?string $prefix = null, ?string $suffix = null): void
    {
        $instruction = new Promotion_Coupon_Generator_Instruction(amount: $amount, prefix: $prefix, codeLength: $code_length, suffix: $suffix);
        $this->coupon_generator->generate($promotion, $instruction);
    }
    private function get_channel_code(): string
    {
        return $this->shared_storage->get('channel')->get_code();
    }
}
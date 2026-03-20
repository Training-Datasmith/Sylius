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
use Behat\Step\When;
use Doctrine\ORM\Entity_Manager_Interface;
use Sylius\Abstraction\State_Machine\State_Machine_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Core_Bundle\Catalog_Promotion\Calculator\Fixed_Discount_Price_Calculator;
use Sylius\Bundle\Core_Bundle\Catalog_Promotion\Calculator\Percentage_Discount_Price_Calculator;
use Sylius\Bundle\Core_Bundle\Catalog_Promotion\Checker\In_For_Product_Scope_Variant_Checker;
use Sylius\Bundle\Core_Bundle\Catalog_Promotion\Checker\In_For_Taxons_Scope_Variant_Checker;
use Sylius\Bundle\Core_Bundle\Catalog_Promotion\Checker\In_For_Variants_Scope_Variant_Checker;
use Sylius\Bundle\Core_Bundle\Fixture\Factory\Example_Factory_Interface;
use Sylius\Component\Channel\Repository\Channel_Repository_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Catalog_Promotion_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Promotion\Event\Catalog_Promotion_Created;
use Sylius\Component\Promotion\Event\Catalog_Promotion_Updated;
use Sylius\Component\Promotion\Model\Catalog_Promotion_Action_Interface;
use Sylius\Component\Promotion\Model\Catalog_Promotion_Scope_Interface;
use Sylius\Component\Promotion\Model\Catalog_Promotion_States;
use Sylius\Component\Promotion\Model\Catalog_Promotion_Transitions;
use Sylius\Resource\Factory\Factory_Interface;
use Symfony\Component\Messenger\Message_Bus_Interface;
final readonly class Catalog_Promotion_Context implements Context
{
    public function __construct(private Example_Factory_Interface $catalog_promotion_example_factory, private Factory_Interface $catalog_promotion_scope_factory, private Factory_Interface $catalog_promotion_action_factory, private Entity_Manager_Interface $entity_manager, private Channel_Repository_Interface $channel_repository, private State_Machine_Interface $state_machine, private Message_Bus_Interface $event_bus, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Given('there is a catalog promotion with :code code and :name name')]
    #[Given('there is also a catalog promotion with :code code and :name name')]
    public function there_is_a_catalog_promotion_with_code_and_name(string $code, string $name): void
    {
        $this->create_catalog_promotion($name, $code);
        $this->entity_manager->flush();
    }
    #[Given('/^(it) is enabled$/')]
    public function it_is_enabled(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $catalog_promotion->set_enabled(true);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('/^(this catalog promotion) is disabled$/')]
    public function this_catalog_promotion_is_disabled(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $catalog_promotion->set_enabled(false);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('there are catalog promotions named :firstName and :secondName')]
    #[Given('there is a catalog promotion named :name')]
    public function there_are_catalog_promotions_named(string ...$names): void
    {
        foreach ($names as $name) {
            $this->create_catalog_promotion($name);
        }
        $this->entity_manager->flush();
    }
    #[Given('the catalog promotion :catalogPromotion is available in :channel')]
    #[Given('/^(this catalog promotion) is(?:| also) available in the ("[^"]+" channel)$/')]
    public function the_catalog_promotion_is_available_in(Catalog_Promotion_Interface $catalog_promotion, Channel_Interface $channel): void
    {
        $catalog_promotion->add_channel($channel);
        $this->entity_manager->flush();
    }
    #[Given('/^(it) applies(?:| also) on ("[^"]+" variant)$/')]
    public function it_applies_on_variant(Catalog_Promotion_Interface $catalog_promotion, Product_Variant_Interface $variant): void
    {
        /** @var CatalogPromotionScopeInterface $catalogPromotionScope */
        $catalog_promotion_scope = $this->catalog_promotion_scope_factory->create_new();
        $catalog_promotion_scope->set_type(In_For_Variants_Scope_Variant_Checker::TYPE);
        $catalog_promotion_scope->set_configuration(['variants' => [$variant->get_code()]]);
        $catalog_promotion->add_scope($catalog_promotion_scope);
        $this->entity_manager->flush();
    }
    #[Given('/^(it) applies(?:| also) on ("[^"]+" product)$/')]
    public function it_applies_on_product(Catalog_Promotion_Interface $catalog_promotion, Product_Interface $product): void
    {
        /** @var CatalogPromotionScopeInterface $catalogPromotionScope */
        $catalog_promotion_scope = $this->catalog_promotion_scope_factory->create_new();
        $catalog_promotion_scope->set_type(In_For_Product_Scope_Variant_Checker::TYPE);
        $catalog_promotion_scope->set_configuration(['products' => [$product->get_code()]]);
        $catalog_promotion->add_scope($catalog_promotion_scope);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given(':catalogPromotion catalog promotion is exclusive')]
    public function catalog_promotion_is_exclusive(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $catalog_promotion->set_exclusive(true);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('/^(it) reduces price by ("[^"]+")$/')]
    public function it_will_reduce_price(Catalog_Promotion_Interface $catalog_promotion, float $discount): void
    {
        /** @var CatalogPromotionActionInterface $catalogPromotionAction */
        $catalog_promotion_action = $this->catalog_promotion_action_factory->create_new();
        $catalog_promotion_action->set_type(Percentage_Discount_Price_Calculator::TYPE);
        $catalog_promotion_action->set_configuration(['amount' => $discount]);
        $catalog_promotion->add_action($catalog_promotion_action);
        $this->entity_manager->flush();
    }
    #[Given('/^(it) reduces(?:| also) price by fixed ("[^"]+") in the ("[^"]+" channel)$/')]
    public function it_reduces_price_by_fixed_in_the_channel(Catalog_Promotion_Interface $catalog_promotion, int $discount, Channel_Interface $channel): void
    {
        /** @var CatalogPromotionActionInterface $catalogPromotionAction */
        $catalog_promotion_action = $this->catalog_promotion_action_factory->create_new();
        $catalog_promotion_action->set_type(Fixed_Discount_Price_Calculator::TYPE);
        $catalog_promotion_action->set_configuration([$channel->get_code() => ['amount' => $discount]]);
        $catalog_promotion->add_action($catalog_promotion_action);
        $this->entity_manager->flush();
    }
    #[Given('/^there is (?:a|another) catalog promotion "([^"]*)" that reduces price by ("[^"]+") and applies on ("[^"]+" variant) and ("[^"]+" variant)$/')]
    #[Given('/^there is (?:a|another) catalog promotion "([^"]*)" that reduces price by ("[^"]+") and applies on ("[^"]+" variant)$/')]
    public function there_is_a_catalog_promotion_that_reduces_price_by_and_applies_on(string $name, float $discount, Product_Variant_Interface ...$variants): void
    {
        $variant_codes = [];
        foreach ($variants as $variant) {
            $variant_codes[] = $variant->get_code();
        }
        $catalog_promotion = $this->create_catalog_promotion($name, null, [], [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => $variant_codes]]], [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]]);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Created($catalog_promotion->get_code()));
    }
    #[Given('/^there is a catalog promotion "([^"]*)" that reduces price by fixed ("[^"]+") in the ("[^"]+" channel) and applies on ("[^"]+" variant)$/')]
    public function there_is_a_catalog_promotion_that_reduces_price_by_fixed_in_the_channel_and_applies_on_variant(string $name, int $discount, Channel_Interface $channel, Product_Variant_Interface $variant): void
    {
        $catalog_promotion = $this->create_catalog_promotion($name, null, [], [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => [$variant->get_code()]]]], [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => [$channel->get_code() => ['amount' => $discount / 100]]]]);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Created($catalog_promotion->get_code()));
    }
    #[Given('/^there is a catalog promotion "([^"]*)" that reduces price by fixed ("[^"]+") in the ("[^"]+" channel) and applies on ("[^"]+" product)$/')]
    public function there_is_a_catalog_promotion_that_reduces_price_by_fixed_in_the_channel_and_applies_on_product(string $name, int $discount, Channel_Interface $channel, Product_Interface $product): void
    {
        $catalog_promotion = $this->create_catalog_promotion($name, null, [], [['type' => In_For_Product_Scope_Variant_Checker::TYPE, 'configuration' => ['products' => [$product->get_code()]]]], [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => [$channel->get_code() => ['amount' => $discount / 100]]]]);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Created($catalog_promotion->get_code()));
    }
    #[Given('/^there is a catalog promotion "([^"]*)" that reduces price by fixed ("[^"]+") in the ("[^"]+" channel) and applies on ("[^"]+" taxon)$/')]
    public function there_is_a_catalog_promotion_that_reduces_price_by_fixed_in_the_channel_and_applies_on_taxon(string $name, int $discount, Channel_Interface $channel, Taxon_Interface $taxon): void
    {
        $catalog_promotion = $this->create_catalog_promotion($name, null, [], [['type' => In_For_Taxons_Scope_Variant_Checker::TYPE, 'configuration' => ['taxons' => [$taxon->get_code()]]]], [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => [$channel->get_code() => ['amount' => $discount / 100]]]]);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Created($catalog_promotion->get_code()));
    }
    #[Given('/^there is (?:a|another) catalog promotion "([^"]*)" that reduces price by ("[^"]+") and applies on ("[^"]+" taxon) and ("[^"]+" taxon)$/')]
    #[Given('/^there is (?:a|another) catalog promotion "([^"]*)" that reduces price by ("[^"]+") and applies on ("[^"]+" taxon)$/')]
    public function there_is_a_catalog_promotion_that_reduces_price_by_and_applies_on_taxon(string $name, float $discount, Taxon_Interface $taxon): void
    {
        $catalog_promotion = $this->create_catalog_promotion($name, null, [], [['type' => In_For_Taxons_Scope_Variant_Checker::TYPE, 'configuration' => ['taxons' => [$taxon->get_code()]]]], [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]]);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Created($catalog_promotion->get_code()));
    }
    #[Given('/^there is (?:a|another) catalog promotion "([^"]*)" available in ("[^"]+" channel) that reduces price by ("[^"]+") and applies on ("[^"]+" variant)$/')]
    public function there_is_a_catalog_promotion_available_in_channel_that_reduces_price_by_and_applies_on_variant(string $name, Channel_Interface $channel, float $discount, Product_Variant_Interface $variant): void
    {
        $catalog_promotion = $this->create_catalog_promotion(name: $name, channels: [$channel->get_code()], scopes: [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => [$variant->get_code()]]]], actions: [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]]);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Created($catalog_promotion->get_code()));
    }
    #[Given('/^there is (?:a|another) catalog promotion "([^"]*)" between "([^"]+)" and "([^"]+)" available in ("[^"]+" channel) that reduces price by ("[^"]+") and applies on ("[^"]+" variant)$/')]
    public function there_is_a_catalog_promotion_between_available_in_channel_that_reduces_price_by_and_applies_on_variant(string $name, string $start_date, string $end_date, Channel_Interface $channel, float $discount, Product_Variant_Interface $variant): void
    {
        $this->create_catalog_promotion(name: $name, channels: [$channel->get_code()], scopes: [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => [$variant->get_code()]]]], actions: [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]], startDate: $start_date, endDate: $end_date);
        $this->entity_manager->flush();
    }
    #[Given('/^there is disabled catalog promotion "([^"]*)" between "([^"]+)" and "([^"]+)" available in ("[^"]+" channel) that reduces price by ("[^"]+") and applies on ("[^"]+" variant)$/')]
    public function there_is_disabled_catalog_promotion_available_in_channel_that_reduces_price_by_and_applies_on_variant(string $name, string $start_date, string $end_date, Channel_Interface $channel, float $discount, Product_Variant_Interface $variant): void
    {
        $this->create_catalog_promotion(name: $name, channels: [$channel->get_code()], scopes: [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => [$variant->get_code()]]]], actions: [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]], startDate: $start_date, endDate: $end_date, enabled: false);
        $this->entity_manager->flush();
    }
    #[Given('/^there is another catalog promotion "([^"]*)" available in ("[^"]+" channel) and ("[^"]+" channel) that reduces price by ("[^"]+") and applies on ("[^"]+" variant)$/')]
    public function there_is_another_catalog_promotion_available_in_channels_that_reduces_price_by_and_applies_on_variant(string $name, Channel_Interface $first_channel, Channel_Interface $second_channel, float $discount, Product_Variant_Interface $variant): void
    {
        $this->create_catalog_promotion($name, null, [$first_channel->get_code(), $second_channel->get_code()], [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => [$variant->get_code()]]]], [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]]);
        $this->entity_manager->flush();
    }
    #[Given('/^there is (?:a|another) catalog promotion "([^"]*)" between "([^"]+)" and "([^"]+)" available in ("[^"]+" channel) that reduces price by ("[^"]+") and applies on ("[^"]+" taxon)$/')]
    public function there_is_a_catalog_promotion_between_available_in_channel_that_reduces_price_by_and_applies_on_taxon(string $name, string $start_date, string $end_date, Channel_Interface $channel, float $discount, Taxon_Interface $taxon): void
    {
        $this->create_catalog_promotion(name: $name, channels: [$channel->get_code()], scopes: [['type' => In_For_Taxons_Scope_Variant_Checker::TYPE, 'configuration' => ['taxons' => [$taxon->get_code()]]]], actions: [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]], startDate: $start_date, endDate: $end_date);
        $this->entity_manager->flush();
    }
    #[Given('/^there is disabled catalog promotion "([^"]*)" between "([^"]+)" and "([^"]+)" available in ("[^"]+" channel) that reduces price by ("[^"]+") and applies on ("[^"]+" taxon)$/')]
    public function there_is_disabled_catalog_promotion_between_available_in_channel_that_reduces_price_by_and_applies_on_taxon(string $name, string $start_date, string $end_date, Channel_Interface $channel, float $discount, Taxon_Interface $taxon): void
    {
        $this->create_catalog_promotion(name: $name, channels: [$channel->get_code()], scopes: [['type' => In_For_Taxons_Scope_Variant_Checker::TYPE, 'configuration' => ['taxons' => [$taxon->get_code()]]]], actions: [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]], startDate: $start_date, endDate: $end_date, enabled: false);
        $this->entity_manager->flush();
    }
    #[Given('/^there is(?: a| another) catalog promotion "([^"]*)" that reduces price by ("[^"]+") and applies on ("[^"]+" product)$/')]
    public function there_is_a_catalog_promotion_that_reduces_price_by_and_applies_on_product(string $name, float $discount, Product_Interface $product): void
    {
        $catalog_promotion = $this->create_catalog_promotion($name, null, [], [['type' => In_For_Product_Scope_Variant_Checker::TYPE, 'configuration' => ['products' => [$product->get_code()]]]], [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]]);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Created($catalog_promotion->get_code()));
    }
    #[Given('/^there is a catalog promotion "([^"]+)" with priority ([^"]+)$/')]
    public function there_is_a_catalog_promotion_with_priority(string $name, int $priority): void
    {
        $catalog_promotion = $this->create_catalog_promotion(name: $name, priority: $priority);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('/^there is a catalog promotion "([^"]+)" with priority ([^"]+) that reduces price by ("[^"]+") and applies on ("[^"]+" variant)$/')]
    public function there_is_a_catalog_promotion_with_priority_that_reduces_price_by_and_applies_on_variant(string $name, int $priority, float $discount, Product_Variant_Interface $variant): void
    {
        $catalog_promotion = $this->create_catalog_promotion($name, null, [], [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => [$variant->get_code()]]]], [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]], $priority);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('/^there is (?:an|another) exclusive catalog promotion "([^"]+)" with priority ([^"]+) that reduces price by ("[^"]+") and applies on ("[^"]+" variant)$/')]
    public function there_is_an_exclusive_catalog_promotion_with_priority_that_reduces_price_by_and_applies_on_variant(string $name, int $priority, float $discount, Product_Variant_Interface $variant): void
    {
        $catalog_promotion = $this->create_catalog_promotion($name, null, [], [['type' => In_For_Variants_Scope_Variant_Checker::TYPE, 'configuration' => ['variants' => [$variant->get_code()]]]], [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]], $priority, true);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('/^there is a catalog promotion "([^"]+)" with priority ([^"]+) that reduces price by fixed ("[^"]+") in the ("[^"]+" channel) and applies on ("[^"]+" product)$/')]
    public function there_is_a_catalog_promotion_with_priority_that_reduces_price_by_fixed_in_the_channel_and_applies_on_product(string $name, int $priority, int $discount, Channel_Interface $channel, Product_Interface $product): void
    {
        $catalog_promotion = $this->create_catalog_promotion($name, null, [], [['type' => In_For_Product_Scope_Variant_Checker::TYPE, 'configuration' => ['products' => [$product->get_code()]]]], [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => [$channel->get_code() => ['amount' => $discount / 100]]]], $priority);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('/^there is a catalog promotion "([^"]+)" with priority ([^"]+) that reduces price by fixed ("[^"]+") in the ("[^"]+" channel) and applies on ("[^"]+" taxon)$/')]
    public function there_is_a_catalog_promotion_with_priority_that_reduces_price_by_fixed_in_the_channel_and_applies_on_taxon(string $name, int $priority, int $discount, Channel_Interface $channel, Taxon_Interface $taxon): void
    {
        $catalog_promotion = $this->create_catalog_promotion($name, null, [], [['type' => In_For_Taxons_Scope_Variant_Checker::TYPE, 'configuration' => ['taxons' => [$taxon->get_code()]]]], [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => [$channel->get_code() => ['amount' => $discount / 100]]]], $priority);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[When('the :catalogPromotion catalog promotion is no longer available')]
    public function the_administrator_makes_this_catalog_promotion_unavailable_in_the_channel(Catalog_Promotion_Interface $catalog_promotion): void
    {
        foreach ($this->channel_repository->find_all() as $channel) {
            $catalog_promotion->remove_channel($channel);
        }
        $this->entity_manager->persist($catalog_promotion);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('the catalog promotion :catalogPromotion operates between :startDate and :endDate')]
    #[Given('/^(this catalog promotion) operates between "([^"]+)" and "([^"]+)"$/')]
    public function the_catalog_promotion_operates_between_dates(Catalog_Promotion_Interface $catalog_promotion, string $start_date, string $end_date): void
    {
        $catalog_promotion->set_start_date(new \DateTime($start_date));
        $catalog_promotion->set_end_date(new \DateTime($end_date));
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('the catalog promotion :catalogPromotion starts at :startDate')]
    public function the_catalog_promotion_starts_at(Catalog_Promotion_Interface $catalog_promotion, string $start_date): void
    {
        $catalog_promotion->set_start_date(new \DateTime($start_date));
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('the catalog promotion :catalogPromotion ended :endDate')]
    public function the_catalog_promotion_ended_at(Catalog_Promotion_Interface $catalog_promotion, string $end_date): void
    {
        $catalog_promotion->set_end_date(new \DateTime($end_date));
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('the end date of catalog promotion :catalogPromotion was changed to :endDate')]
    public function the_end_date_of_catalog_promotion_was_changed_to(Catalog_Promotion_Interface $catalog_promotion, string $end_date): void
    {
        $catalog_promotion->set_end_date(new \DateTime($end_date));
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('/^(its) priority is ([^"]+)$/')]
    public function the_catalog_promotion_priority_is(Catalog_Promotion_Interface $catalog_promotion, int $priority): void
    {
        $catalog_promotion->set_priority($priority);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('/^the ("[^"]+" catalog promotion) is active$/')]
    #[Given('/^(this catalog promotion) is active$/')]
    public function the_catalog_promotion_is_active(Catalog_Promotion_Interface $catalog_promotion): void
    {
        if (Catalog_Promotion_States::STATE_ACTIVE === $catalog_promotion->get_state()) {
            return;
        }
        $this->state_machine->apply($catalog_promotion, Catalog_Promotion_Transitions::GRAPH, Catalog_Promotion_Transitions::TRANSITION_PROCESS);
        $this->state_machine->apply($catalog_promotion, Catalog_Promotion_Transitions::GRAPH, Catalog_Promotion_Transitions::TRANSITION_ACTIVATE);
        $this->entity_manager->flush();
    }
    #[Given('the catalog promotion :catalogPromotion is currently being processed')]
    public function the_catalog_promotion_is_currently_being_processed(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $this->state_machine->apply($catalog_promotion, Catalog_Promotion_Transitions::GRAPH, Catalog_Promotion_Transitions::TRANSITION_PROCESS);
        $this->entity_manager->flush();
    }
    #[Given('the :catalogPromotion catalog promotion is enabled')]
    public function the_catalog_promotion_is_enabled(Catalog_Promotion_Interface $catalog_promotion): void
    {
        $catalog_promotion->set_enabled(true);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('there is disabled catalog promotion named :name')]
    public function there_is_catalog_promotions_named(string $name): void
    {
        $this->create_catalog_promotion(name: $name, enabled: false);
        $this->entity_manager->flush();
    }
    #[Given('/^there is a catalog promotion "([^"]+)" with priority ([^"]+) that reduces price by ("[^"]+") and applies on ("[^"]+" product)$/')]
    public function there_is_a_catalog_promotion_with_priority_that_reduces_price_by_and_applies_on_product(string $name, int $priority, float $discount, Product_Interface $product): void
    {
        $catalog_promotion = $this->create_catalog_promotion(name: $name, scopes: [['type' => In_For_Product_Scope_Variant_Checker::TYPE, 'configuration' => ['products' => [$product->get_code()]]]], actions: [['type' => Percentage_Discount_Price_Calculator::TYPE, 'configuration' => ['amount' => $discount]]], priority: $priority);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    #[Given('/^there is disabled catalog promotion "([^"]+)" with priority ([^"]+) that reduces price by fixed ("[^"]+") in the ("[^"]+" channel) and applies on ("[^"]+" product)$/')]
    public function there_is_disabled_catalog_promotion_with_priority_that_reduces_price_by_fixed_in_the_channel_and_applies_on_product(string $name, int $priority, int $discount, Channel_Interface $channel, Product_Interface $product): void
    {
        $catalog_promotion = $this->create_catalog_promotion(name: $name, channels: [$channel], scopes: [['type' => In_For_Product_Scope_Variant_Checker::TYPE, 'configuration' => ['products' => [$product->get_code()]]]], actions: [['type' => Fixed_Discount_Price_Calculator::TYPE, 'configuration' => [$channel->get_code() => ['amount' => $discount / 100]]]], priority: $priority, enabled: false);
        $this->entity_manager->flush();
        $this->event_bus->dispatch(new Catalog_Promotion_Updated($catalog_promotion->get_code()));
    }
    private function create_catalog_promotion(string $name, ?string $code = null, array $channels = [], array $scopes = [], array $actions = [], ?int $priority = null, bool $exclusive = false, ?string $start_date = null, ?string $end_date = null, bool $enabled = true): Catalog_Promotion_Interface
    {
        if (empty($channels) && $this->shared_storage->has('channel')) {
            $channels = [$this->shared_storage->get('channel')];
        }
        $code ??= String_Inflector::name_to_code($name);
        /** @var CatalogPromotionInterface $catalogPromotion */
        $catalog_promotion = $this->catalog_promotion_example_factory->create(['name' => $name, 'code' => $code, 'start_date' => $start_date, 'end_date' => $end_date, 'enabled' => $enabled, 'channels' => $channels, 'actions' => $actions, 'scopes' => $scopes, 'description' => $name . ' description', 'priority' => $priority, 'exclusive' => $exclusive]);
        $this->entity_manager->persist($catalog_promotion);
        $this->shared_storage->set('catalog_promotion', $catalog_promotion);
        return $catalog_promotion;
    }
}
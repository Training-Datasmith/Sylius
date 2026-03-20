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
use Sylius\Bundle\Core_Bundle\Fixture\Factory\Shipping_Method_Example_Factory;
use Sylius\Component\Addressing\Model\Scope;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Scope as CoreScope;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Sylius\Component\Core\Repository\Shipping_Method_Repository_Interface;
use Sylius\Component\Core\Shipping\Checker\Rule\Order_Total_Greater_Than_Or_Equal_Rule_Checker;
use Sylius\Component\Core\Shipping\Checker\Rule\Order_Total_Less_Than_Or_Equal_Rule_Checker;
use Sylius\Component\Shipping\Calculator\Default_Calculators;
use Sylius\Component\Shipping\Checker\Rule\Total_Weight_Greater_Than_Or_Equal_Rule_Checker;
use Sylius\Component\Shipping\Checker\Rule\Total_Weight_Less_Than_Or_Equal_Rule_Checker;
use Sylius\Component\Shipping\Model\Shipping_Category_Interface;
use Sylius\Component\Shipping\Model\Shipping_Method_Rule_Interface;
use Sylius\Component\Shipping\Model\Shipping_Method_Translation_Interface;
use Sylius\Component\Taxation\Model\Tax_Category_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Shipping_Context implements Context
{
    /**
     * @param ShippingMethodRepositoryInterface<ShippingMethodInterface> $shippingMethodRepository
     * @param RepositoryInterface<ZoneInterface> $zoneRepository
     * @param FactoryInterface<ShippingMethodRuleInterface> $shippingMethodRuleFactory
     */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Shipping_Method_Repository_Interface $shipping_method_repository, private Repository_Interface $zone_repository, private Shipping_Method_Example_Factory $shipping_method_example_factory, private Factory_Interface $shipping_method_rule_factory, private Object_Manager $shipping_method_manager)
    {
    }
    #[Given('the store ships everything for Free within the :zone zone')]
    public function store_ships_everything_for_free(Zone_Interface $zone): void
    {
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => 'Free', 'enabled' => true, 'zone' => $zone, 'calculator' => ['type' => Default_Calculators::FLAT_RATE, 'configuration' => $this->get_configuration_by_channels([$this->shared_storage->get('channel')])]]));
    }
    #[Given('the store ships everywhere with :shippingMethodName')]
    #[Given('the store ships everywhere for :shippingMethodName')]
    public function the_store_ships_everywhere_with(string $shipping_method_name): void
    {
        /** @var ZoneInterface $zone */
        foreach ($this->zone_repository->find_by(['scope' => [Core_Scope::SHIPPING, Scope::ALL]]) as $zone) {
            $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => ucfirst($shipping_method_name), 'code' => strtoupper($shipping_method_name) . '-' . $zone->get_code(), 'enabled' => true, 'zone' => $zone, 'calculator' => ['type' => Default_Calculators::FLAT_RATE, 'configuration' => $this->get_configuration_by_channels([$this->shared_storage->get('channel')])]]));
        }
    }
    #[Given('/^the store ships everywhere for free for (all channels)$/')]
    public function the_store_ships_everywhere_for_free_for_all_channels(array $channels): void
    {
        foreach ($this->zone_repository->find_by(['scope' => [Core_Scope::SHIPPING, Scope::ALL]]) as $zone) {
            $configuration = $this->get_configuration_by_channels($channels);
            $shipping_method = $this->shipping_method_example_factory->create(['name' => 'Free', 'enabled' => true, 'zone' => $zone, 'calculator' => ['type' => Default_Calculators::FLAT_RATE, 'configuration' => $configuration], 'channels' => $channels]);
            $this->save_shipping_method($shipping_method);
        }
    }
    #[Given('the store (also )allows shipping with :name')]
    public function the_store_allows_shipping_method_with_name(string $name): void
    {
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => $name, 'enabled' => true]));
    }
    #[Given('the store (also )allows shipping with :name identified by :code')]
    public function the_store_allows_shipping_method_with_name_and_code(string $name, string $code): void
    {
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => $name, 'zone' => $this->get_shipping_zone(), 'enabled' => true, 'code' => $code]));
    }
    #[Given('the store (also )allows shipping with :name at position :position')]
    public function the_store_allows_shipping_method_with_name_and_position(string $name, int $position): void
    {
        $shipping_method = $this->shipping_method_example_factory->create(['name' => $name, 'enabled' => true, 'zone' => $this->get_shipping_zone()]);
        $shipping_method->set_position($position);
        $this->save_shipping_method($shipping_method);
    }
    #[Given('/^the store(?:| also) allows shipping with "([^"]+)" at position (\d+) with ("[^"]+") fee$/')]
    public function the_store_allows_shipping_method_with_name_and_position_and_fee(string $name, int $position, int $fee): void
    {
        $channel = $this->shared_storage->get('channel');
        $configuration = $this->get_configuration_by_channels([$channel], $fee);
        $shipping_method = $this->shipping_method_example_factory->create(['name' => $name, 'enabled' => true, 'zone' => $this->get_shipping_zone(), 'calculator' => ['type' => Default_Calculators::FLAT_RATE, 'configuration' => $configuration], 'channels' => [$channel]]);
        $shipping_method->set_position($position);
        $this->save_shipping_method($shipping_method);
    }
    #[Given('/^(this shipping method) is named "([^"]+)" in the ("[^"]+" locale)$/')]
    public function this_shipping_method_is_named_in_locale(Shipping_Method_Interface $shipping_method, string $name, string $locale): void
    {
        $translations = $shipping_method->get_translations();
        /** @var ShippingMethodTranslationInterface $translation */
        foreach ($translations as $translation) {
            if ($translation->get_locale() === $locale) {
                $translation->set_name($name);
                return;
            }
        }
    }
    #[Given('the store allows shipping with :firstName and :secondName')]
    #[Given('the store allows shipping with :firstName, :secondName and :thirdName')]
    public function the_store_allows_shipping_with_and(string ...$names): void
    {
        foreach ($names as $name) {
            $this->the_store_allows_shipping_method_with_name($name);
        }
    }
    #[Given('/^the store has "([^"]+)" shipping method with ("[^"]+") fee within the ("[^"]+" zone)$/')]
    #[Given('/^the store has "([^"]+)" shipping method with ("[^"]+") fee for the (rest of the world)$/')]
    public function store_has_shipping_method_with_fee_and_zone(string $shipping_method_name, int $fee, Zone_Interface $zone): void
    {
        $channel = $this->shared_storage->get('channel');
        $configuration = $this->get_configuration_by_channels([$channel], $fee);
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => $shipping_method_name, 'enabled' => true, 'zone' => $zone, 'calculator' => ['type' => Default_Calculators::FLAT_RATE, 'configuration' => $configuration], 'channels' => [$this->shared_storage->get('channel')]]));
    }
    #[Given('/^the store has "([^"]+)" shipping method with ("[^"]+") fee$/')]
    public function store_has_shipping_method_with_fee(string $shipping_method_name, int $fee): void
    {
        $channel = $this->shared_storage->get('channel');
        $configuration = $this->get_configuration_by_channels([$channel], $fee);
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => $shipping_method_name, 'enabled' => true, 'zone' => $this->get_shipping_zone(), 'calculator' => ['type' => Default_Calculators::FLAT_RATE, 'configuration' => $configuration], 'channels' => [$this->shared_storage->get('channel')]]));
    }
    #[Given('/^the store has "([^"]+)" shipping method with ("[^"]+") fee per shipment for ("[^"]+" channel) and ("[^"]+") for ("[^"]+" channel)$/')]
    public function store_has_shipping_method_with_fee_per_shipment_for_channels(string $shipping_method_name, int $first_fee, Channel_Interface $first_channel, int $second_fee, Channel_Interface $second_channel): void
    {
        $configuration = [];
        $configuration[$first_channel->get_code()] = ['amount' => $first_fee];
        $configuration[$second_channel->get_code()] = ['amount' => $second_fee];
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => $shipping_method_name, 'enabled' => true, 'zone' => $this->get_shipping_zone(), 'calculator' => ['type' => Default_Calculators::FLAT_RATE, 'configuration' => $configuration], 'channels' => [$first_channel, $second_channel]]));
    }
    #[Given('/^the store has "([^"]+)" shipping method with ("[^"]+") fee per shipment for ("[^"]+" channel)$/')]
    public function store_has_shipping_method_with_fee_per_shipment_for_channel(string $shipping_method_name, int $fee, Channel_Interface $channel): void
    {
        $configuration = [$channel->get_code() => ['amount' => $fee]];
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => $shipping_method_name, 'enabled' => true, 'zone' => $this->get_shipping_zone(), 'calculator' => ['type' => Default_Calculators::FLAT_RATE, 'configuration' => $configuration], 'channels' => [$channel]]));
    }
    #[Given('/^the store has "([^"]+)" shipping method with ("[^"]+") fee per unit for ("[^"]+" channel)$/')]
    #[Given('/^the store has "([^"]+)" shipping method with ("[^"]+") fee per unit for ("[^"]+" channel) and ("[^"]+") for ("[^"]+" channel)$/')]
    public function store_has_shipping_method_with_fee_per_unit_for_channels(string $shipping_method_name, int $first_fee, Channel_Interface $first_channel, ?int $second_fee = null, ?Channel_Interface $second_channel = null): void
    {
        $configuration = [];
        $channels = [];
        $configuration[$first_channel->get_code()] = ['amount' => $first_fee];
        $channels[] = $first_channel;
        if (null !== $second_fee) {
            $configuration[$second_channel->get_code()] = ['amount' => $second_fee];
            $channels[] = $second_channel;
        }
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => $shipping_method_name, 'enabled' => true, 'zone' => $this->get_shipping_zone(), 'calculator' => ['type' => Default_Calculators::PER_UNIT_RATE, 'configuration' => $configuration], 'channels' => $channels]));
    }
    #[Given('/^the store has disabled "([^"]+)" shipping method with ("[^"]+") fee$/')]
    public function store_has_disabled_shipping_method_with_fee(string $shipping_method_name, int $fee): void
    {
        $channel = $this->shared_storage->get('channel');
        $configuration = $this->get_configuration_by_channels([$channel], $fee);
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => $shipping_method_name, 'enabled' => false, 'zone' => $this->get_shipping_zone(), 'calculator' => ['type' => Default_Calculators::FLAT_RATE, 'configuration' => $configuration], 'channels' => [$this->shared_storage->get('channel')]]));
    }
    #[Given('/^the store has an archival "([^"]+)" shipping method with ("[^"]+") fee$/')]
    public function the_store_has_archival_shipping_method_with_fee(string $shipping_method_name, int $fee): void
    {
        $channel = $this->shared_storage->get('channel');
        $configuration = $this->get_configuration_by_channels([$channel], $fee);
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => $shipping_method_name, 'enabled' => true, 'zone' => $this->get_shipping_zone(), 'calculator' => ['type' => Default_Calculators::FLAT_RATE, 'configuration' => $configuration], 'channels' => [$this->shared_storage->get('channel')], 'archived_at' => new \DateTime()]));
    }
    #[Given('/^the store has "([^"]+)" shipping method with ("[^"]+") fee per unit$/')]
    public function the_store_has_shipping_method_with_fee_per_unit(string $shipping_method_name, int $fee): void
    {
        $channel = $this->shared_storage->get('channel');
        $configuration = $this->get_configuration_by_channels([$channel], $fee);
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => $shipping_method_name, 'enabled' => true, 'zone' => $this->get_shipping_zone(), 'calculator' => ['type' => Default_Calculators::PER_UNIT_RATE, 'configuration' => $configuration], 'channels' => [$this->shared_storage->get('channel')]]));
    }
    #[Given('/^the store has "([^"]+)" shipping method with ("[^"]+") fee not assigned to any channel$/')]
    public function store_has_shipping_method_with_fee_not_assigned_to_any_channel(string $shipping_method_name, int $fee): void
    {
        $channel = $this->shared_storage->get('channel');
        $configuration = $this->get_configuration_by_channels([$channel], $fee);
        $this->save_shipping_method($this->shipping_method_example_factory->create(['name' => $shipping_method_name, 'enabled' => true, 'zone' => $this->get_shipping_zone(), 'calculator' => ['type' => Default_Calculators::FLAT_RATE, 'configuration' => $configuration], 'channels' => []]));
    }
    #[Given('/^(shipping method "[^"]+") belongs to ("[^"]+" tax category)$/')]
    public function shipping_method_belongs_to_tax_category(Shipping_Method_Interface $shipping_method, Tax_Category_Interface $tax_category): void
    {
        $shipping_method->set_tax_category($tax_category);
        $this->shipping_method_manager->flush();
    }
    #[Given('the shipping method :shippingMethod is enabled')]
    public function the_shipping_method_is_enabled(Shipping_Method_Interface $shipping_method): void
    {
        $shipping_method->enable();
        $this->shipping_method_manager->flush();
    }
    #[Given('the shipping method :shippingMethod is disabled')]
    public function the_shipping_method_is_disabled(Shipping_Method_Interface $shipping_method): void
    {
        $shipping_method->disable();
        $this->shipping_method_manager->flush();
    }
    #[Given('/^(this shipping method) requires at least one unit matches to ("([^"]+)" shipping category)$/')]
    public function this_shipping_method_requires_at_least_one_unit_match_to_shipping_category(Shipping_Method_Interface $shipping_method, Shipping_Category_Interface $shipping_category): void
    {
        $shipping_method->set_category($shipping_category);
        $shipping_method->set_category_requirement(Shipping_Method_Interface::CATEGORY_REQUIREMENT_MATCH_ANY);
        $this->shipping_method_manager->flush();
    }
    #[Given('/^(this shipping method) requires that all units match to ("([^"]+)" shipping category)$/')]
    public function this_shipping_method_requires_that_all_units_match_to_shipping_category(Shipping_Method_Interface $shipping_method, Shipping_Category_Interface $shipping_category): void
    {
        $shipping_method->set_category($shipping_category);
        $shipping_method->set_category_requirement(Shipping_Method_Interface::CATEGORY_REQUIREMENT_MATCH_ALL);
        $this->shipping_method_manager->flush();
    }
    #[Given('/^(this shipping method) requires that no units match to ("([^"]+)" shipping category)$/')]
    public function this_shipping_method_requires_that_no_units_match_to_shipping_category(Shipping_Method_Interface $shipping_method, Shipping_Category_Interface $shipping_category): void
    {
        $shipping_method->set_category($shipping_category);
        $shipping_method->set_category_requirement(Shipping_Method_Interface::CATEGORY_REQUIREMENT_MATCH_NONE);
        $this->shipping_method_manager->flush();
    }
    #[Given('/^the (shipping method "[^"]+") is archival$/')]
    public function the_shipping_method_is_archival(Shipping_Method_Interface $shipping_method): void
    {
        $shipping_method->set_archived_at(new \DateTime());
        $this->shipping_method_manager->flush();
    }
    #[Given('/^the shipping fee for ("[^"]+" shipping method) has been changed to ("[^"]+")$/')]
    public function the_shipping_fee_for_shipping_method_has_been_changed_to(Shipping_Method_Interface $shipping_method, int $fee): void
    {
        $channel = $this->shared_storage->get('channel');
        $configuration = $this->get_configuration_by_channels([$channel], $fee);
        $shipping_method->set_configuration($configuration);
        $this->shipping_method_manager->flush();
    }
    #[Given('/^(this shipping method) is only available for orders over or equal to ("[^"]+")$/')]
    public function this_shipping_method_is_only_available_for_orders_over_or_equal_to(Shipping_Method_Interface $shipping_method, int $amount): void
    {
        $rule = $this->create_shipping_method_rule(Order_Total_Greater_Than_Or_Equal_Rule_Checker::TYPE, $this->get_configuration_by_channels([$this->shared_storage->get('channel')], $amount));
        $this->add_rule_to_shipping_method($rule, $shipping_method);
    }
    #[Given('/^(this shipping method) is only available for orders under or equal to ("[^"]+")$/')]
    public function this_shipping_method_is_only_available_for_orders_under_or_equal_to(Shipping_Method_Interface $shipping_method, int $amount): void
    {
        $rule = $this->create_shipping_method_rule(Order_Total_Less_Than_Or_Equal_Rule_Checker::TYPE, $this->get_configuration_by_channels([$this->shared_storage->get('channel')], $amount));
        $this->add_rule_to_shipping_method($rule, $shipping_method);
    }
    #[Given('/^(this shipping method) is only available for orders with a total weight greater or equal to (\d+\.\d+)$/')]
    public function this_shipping_method_is_only_available_for_orders_with_a_total_weight_greater_or_equal_to(Shipping_Method_Interface $shipping_method, float $weight): void
    {
        $rule = $this->create_shipping_method_rule(Total_Weight_Greater_Than_Or_Equal_Rule_Checker::TYPE, ['weight' => $weight]);
        $this->add_rule_to_shipping_method($rule, $shipping_method);
    }
    #[Given('/^(this shipping method) is only available for orders with a total weight less or equal to (\d+\.\d+)$/')]
    public function this_shipping_method_is_only_available_for_orders_with_a_total_weight_less_or_equal_to(Shipping_Method_Interface $shipping_method, float $weight): void
    {
        $rule = $this->create_shipping_method_rule(Total_Weight_Less_Than_Or_Equal_Rule_Checker::TYPE, ['weight' => $weight]);
        $this->add_rule_to_shipping_method($rule, $shipping_method);
    }
    #[Given('/^(this shipping method) has been disabled$/')]
    #[Given('/^(this shipping method) has been disabled for ("[^"]+" channel)$/')]
    public function this_shipping_method_has_been_disabled(Shipping_Method_Interface $shipping_method, ?Channel_Interface $channel = null): void
    {
        /** @var ShippingMethodInterface $shippingMethod */
        $shipping_method = $this->shipping_method_repository->find_one_by(['code' => $shipping_method->get_code()]);
        if (null === $channel) {
            $shipping_method->disable();
        } else {
            $shipping_method->remove_channel($channel);
        }
        $this->shipping_method_manager->flush();
    }
    #[Given('/^(this shipping method) has changed zone to ("[^"]+" zone)$/')]
    public function this_shipping_method_has_changed_zone(Shipping_Method_Interface $shipping_method, Zone_Interface $zone): void
    {
        /** @var ShippingMethodInterface $shippingMethod */
        $shipping_method = $this->shipping_method_repository->find_one_by(['code' => $shipping_method->get_code()]);
        $shipping_method->set_zone($zone);
        $this->shipping_method_manager->flush();
    }
    private function get_configuration_by_channels(array $channels, int $amount = 0): array
    {
        $configuration = [];
        /** @var ChannelInterface $channel */
        foreach ($channels as $channel) {
            $configuration[$channel->get_code()] = ['amount' => $amount];
        }
        return $configuration;
    }
    private function save_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $this->shipping_method_repository->add($shipping_method);
        $this->shared_storage->set('shipping_method', $shipping_method);
    }
    private function get_shipping_zone(): Zone_Interface
    {
        if ($this->shared_storage->has('shipping_zone')) {
            return $this->shared_storage->get('shipping_zone');
        }
        return $this->shared_storage->get('zone');
    }
    private function add_rule_to_shipping_method(Shipping_Method_Rule_Interface $rule, Shipping_Method_Interface $shipping_method): void
    {
        $shipping_method->add_rule($rule);
        $this->shipping_method_manager->flush();
    }
    private function create_shipping_method_rule(string $type, array $configuration): Shipping_Method_Rule_Interface
    {
        /** @var ShippingMethodRuleInterface $rule */
        $rule = $this->shipping_method_rule_factory->create_new();
        $rule->set_type($type);
        $rule->set_configuration($configuration);
        return $rule;
    }
}
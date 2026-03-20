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
use Sylius\Component\Addressing\Factory\Zone_Factory_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Component\Addressing\Model\Scope;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Addressing\Model\Zone_Member_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
use Sylius\Resource\Model\Code_Aware_Interface;
use Symfony\Component\Intl\Countries;
final readonly class Zone_Context implements Context
{
    /**
     * @param RepositoryInterface<ZoneInterface> $zoneRepository
     * @param ZoneFactoryInterface<ZoneInterface> $zoneFactory
     * @param FactoryInterface<ZoneMemberInterface> $zoneMemberFactory
     */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Repository_Interface $zone_repository, private Object_Manager $object_manager, private Zone_Factory_Interface $zone_factory, private Factory_Interface $zone_member_factory)
    {
    }
    #[Given('there is a zone "The Rest of the World" containing all other countries')]
    public function there_is_a_zone_the_rest_of_the_world_containing_all_other_countries(): void
    {
        $rest_of_world_countries = Countries::get_names('en');
        unset($rest_of_world_countries['US']);
        $zone = $this->zone_factory->create_with_members(array_keys($rest_of_world_countries));
        $zone->set_type(Zone_Interface::TYPE_COUNTRY);
        $zone->set_code('RoW');
        $zone->set_name('The Rest of the World');
        $this->zone_repository->add($zone);
    }
    #[Given('default tax zone is :zone')]
    public function default_tax_zone_is(Zone_Interface $zone): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->shared_storage->get('channel');
        $channel->set_default_tax_zone($zone);
        $this->object_manager->flush();
    }
    #[Given('the store does not have any zones defined')]
    public function the_store_does_not_have_any_zones_defined(): void
    {
        $zones = $this->zone_repository->find_all();
        foreach ($zones as $zone) {
            $this->zone_repository->remove($zone);
        }
    }
    #[Given('the store has (also) a zone :zoneName')]
    #[Given('the store has a zone :zoneName with code :code')]
    #[Given('the store also has a zone :zoneName with code :code')]
    #[Given('the store has a zone :zoneName with code :code and priority :priority')]
    public function the_store_has_a_zone_with_code(string $zone_name, ?string $code = null, ?int $priority = 0): void
    {
        $zone = $this->create_zone($zone_name, $code, Scope::ALL);
        $zone->set_priority($priority);
        $this->save_zone($zone, 'zone');
    }
    #[Given('the store has zones :firstName, :secondName and :thirdName')]
    public function the_store_has_zones(string ...$names): void
    {
        foreach ($names as $name) {
            $this->the_store_has_a_zone_with_code($name);
        }
    }
    #[Given('the store has a :scope zone :zoneName with code :code')]
    public function the_store_has_a_scoped_zone_with_code(?string $scope, string $zone_name, ?string $code): void
    {
        $this->save_zone($this->create_zone($zone_name, $code, $scope), $scope . '_zone');
    }
    #[Given('/^(it)(?:| also) has the ("([^"]+)" country) member$/')]
    #[Given('/^(this zone)(?:| also) has the ("([^"]+)" country) member$/')]
    public function it_has_the_country_member_and_the_country_member(Zone_Interface $zone, Country_Interface $country): void
    {
        $zone->set_type(Zone_Interface::TYPE_COUNTRY);
        $zone->add_member($this->create_zone_member($country));
        $this->object_manager->flush();
    }
    #[Given('/^(the "([^"]*)" (?:country|province|zone) member) has been removed from (this zone)$/')]
    public function the_zone_member_has_been_removed(Zone_Member_Interface $zone_member, string $zone_member_name, Zone_Interface $zone): void
    {
        $zone->remove_member($zone_member);
        $this->object_manager->flush();
    }
    #[Given('/^(it)(?:| also) has the ("([^"]+)", "([^"]+)" and "([^"]+)" country) members$/')]
    public function it_has_country_members(Zone_Interface $zone, array $countries): void
    {
        $zone->set_type(Zone_Interface::TYPE_COUNTRY);
        foreach ($countries as $country) {
            $zone->add_member($this->create_zone_member($country));
        }
        $this->object_manager->flush();
    }
    #[Given('/^(it) has the ("[^"]+" province) member$/')]
    #[Given('/^(it) also has the ("[^"]+" province) member$/')]
    public function it_has_the_province_member_and_the_province_member(Zone_Interface $zone, Province_Interface $province): void
    {
        $zone->set_type(Zone_Interface::TYPE_PROVINCE);
        $zone->add_member($this->create_zone_member($province));
        $this->object_manager->flush();
    }
    #[Given('/^(it) has the (zone named "([^"]+)")$/')]
    #[Given('/^(it) also has the (zone named "([^"]+)")$/')]
    public function it_has_the_zone_member_and_the_zone_member(Zone_Interface $parent_zone, Zone_Interface $child_zone): void
    {
        $parent_zone->set_type(Zone_Interface::TYPE_ZONE);
        $parent_zone->add_member($this->create_zone_member($child_zone));
        $this->object_manager->flush();
    }
    /**
     * @return ZoneMemberInterface
     */
    private function create_zone_member(Code_Aware_Interface $zone_member)
    {
        $code = $zone_member->get_code();
        /** @var ZoneMemberInterface $zoneMember */
        $zone_member = $this->zone_member_factory->create_new();
        $zone_member->set_code($code);
        return $zone_member;
    }
    private function create_zone(string $name, ?string $code = null, ?string $scope = Scope::ALL): Zone_Interface
    {
        $zone = $this->zone_factory->create_typed(Zone_Interface::TYPE_ZONE);
        $zone->set_code($code ?? String_Inflector::name_to_code($name));
        $zone->set_name($name);
        $zone->set_scope($scope);
        return $zone;
    }
    private function save_zone(\Sylius\Component\Addressing\Model\Zone_Interface $zone, string $key): void
    {
        $this->shared_storage->set($key, $zone);
        $this->zone_repository->add($zone);
    }
}
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
namespace Sylius\Bundle\Addressing_Bundle\Repository;

use Doctrine\ORM\Query_Builder;
use Sylius\Bundle\Resource_Bundle\Doctrine\ORM\Entity_Repository;
use Sylius\Component\Addressing\Model\Address_Interface;
use Sylius\Component\Addressing\Model\Scope;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Addressing\Repository\Zone_Repository_Interface;
/**
 * @implements ZoneRepositoryInterface<ZoneInterface>
 */
class Zone_Repository extends Entity_Repository implements Zone_Repository_Interface
{
    public function find_one_by_address_and_type(Address_Interface $address, string $type, ?string $scope = null): ?Zone_Interface
    {
        $query_builder = $this->create_by_address_query_builder($address, $scope);
        $query_builder->and_where($query_builder->expr()->eq('o.type', ':type'))->set_parameter('type', $type)->set_max_results(1)->add_order_by('o.priority', 'DESC');
        return $query_builder->get_query()->get_one_or_null_result();
    }
    /** @return ZoneInterface[] */
    public function find_by_address(Address_Interface $address, ?string $scope = null): array
    {
        return $this->create_by_address_query_builder($address, $scope)->get_query()->get_result();
    }
    public function create_by_address_query_builder(Address_Interface $address, ?string $scope = null): Query_Builder
    {
        $query_builder = $this->create_query_builder('o')->select('o', 'members')->left_join('o.members', 'members');
        if (null !== $scope) {
            $query_builder->and_where($query_builder->expr()->in('o.scope', ':scopes'))->set_parameter('scopes', array_unique([$scope, Scope::ALL]));
        }
        $or_conditions = [];
        if ($address->get_country_code() !== null) {
            $or_conditions[] = $query_builder->expr()->and_x($query_builder->expr()->eq('o.type', ':country'), $query_builder->expr()->eq('members.code', ':countryCode'));
            $query_builder->set_parameter('country', Zone_Interface::TYPE_COUNTRY);
            $query_builder->set_parameter('countryCode', $address->get_country_code());
        }
        if ($address->get_province_code() !== null) {
            $or_conditions[] = $query_builder->expr()->and_x($query_builder->expr()->eq('o.type', ':province'), $query_builder->expr()->eq('members.code', ':provinceCode'));
            $query_builder->set_parameter('province', Zone_Interface::TYPE_PROVINCE);
            $query_builder->set_parameter('provinceCode', $address->get_province_code());
        }
        if ($or_conditions !== []) {
            $query_builder->and_where($query_builder->expr()->or_x(...$or_conditions));
        }
        return $query_builder;
    }
    /**
     * @param array<ZoneInterface> $members
     *
     * @return array<ZoneInterface>
     */
    public function find_by_members(array $members, ?string $scope = null): array
    {
        $zones_codes = array_map(fn(Zone_Interface $zone): string => $zone->get_code(), $members);
        $query_builder = $this->create_query_builder('o')->select('o', 'members')->left_join('o.members', 'members');
        if (null !== $scope) {
            $query_builder->and_where($query_builder->expr()->in('o.scope', ':scopes'))->set_parameter('scopes', array_unique([$scope, Scope::ALL]));
        }
        $query_builder->and_where('o.type = :type')->and_where($query_builder->expr()->in('members.code', ':zones'))->set_parameter('type', Zone_Interface::TYPE_ZONE)->set_parameter('zones', $zones_codes);
        return $query_builder->get_query()->get_result();
    }
}
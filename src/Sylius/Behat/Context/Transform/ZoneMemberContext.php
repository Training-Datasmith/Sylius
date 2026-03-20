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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Component\Addressing\Converter\Country_Name_Converter_Interface;
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Addressing\Model\Zone_Member_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Zone_Member_Context implements Context
{
    public function __construct(private Country_Name_Converter_Interface $country_name_converter, private Repository_Interface $province_repository, private Repository_Interface $zone_repository, private Repository_Interface $zone_member_repository)
    {
    }
    #[Transform('the :name country member')]
    public function get_country_type_zone_member_by_name(string $name)
    {
        $country_code = $this->country_name_converter->convert_to_code($name);
        return $this->get_zone_member_by_code($country_code);
    }
    #[Transform('/^"([^"]+)", "([^"]+)" and "([^"]+)" country members$/')]
    #[Transform('/^"([^"]+)" and "([^"]+)" country members$/')]
    public function get_country_type_zone_members_by_names(string ...$names): array
    {
        $codes = $names;
        array_walk($codes, fn(string &$item): string => $item = $this->country_name_converter->convert_to_code($item));
        return $this->get_zone_members_by_codes($codes);
    }
    #[Transform('the :name province member')]
    public function get_province_type_zone_member_by_name($name)
    {
        $province_code = $this->get_province_by_name($name)->get_code();
        return $this->get_zone_member_by_code($province_code);
    }
    #[Transform('the :name zone member')]
    public function get_zone_type_zone_member_by_name($name)
    {
        $zone_code = $this->get_zone_by_name($name)->get_code();
        return $this->get_zone_member_by_code($zone_code);
    }
    /**
     *
     * @return ZoneMemberInterface
     * @throws \InvalidArgumentException
     */
    private function get_zone_member_by_code(string $code)
    {
        $zone_member = $this->zone_member_repository->find_one_by(['code' => $code]);
        Assert::not_null($zone_member, sprintf('Zone member with code %s does not exist.', $code));
        return $zone_member;
    }
    private function get_zone_members_by_codes(array $codes): array
    {
        return $this->zone_member_repository->find_by(['code' => $codes]);
    }
    /**
     *
     * @return ProvinceInterface
     * @throws \InvalidArgumentException
     */
    private function get_province_by_name(string $name)
    {
        $province = $this->province_repository->find_one_by(['name' => $name]);
        Assert::not_null($province, sprintf('Province with name %s does not exist.', $name));
        return $province;
    }
    /**
     *
     * @return ZoneInterface
     * @throws \InvalidArgumentException
     */
    private function get_zone_by_name(string $name)
    {
        $zone = $this->zone_repository->find_one_by(['name' => $name]);
        Assert::not_null($zone, sprintf('Zone with name %s does not exist.', $name));
        return $zone;
    }
}
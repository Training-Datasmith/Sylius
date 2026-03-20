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
namespace Symfony\Component\Dependency_Injection\Loader\Configurator;

use Sylius\Bundle\Addressing_Bundle\Twig\Country_Name_Extension;
use Sylius\Bundle\Addressing_Bundle\Twig\Province_Naming_Extension;
use Sylius\Bundle\Addressing_Bundle\Validator\Constraints\Province_Address_Constraint_Validator;
use Sylius\Bundle\Addressing_Bundle\Validator\Constraints\Unique_Province_Collection_Validator;
use Sylius\Bundle\Addressing_Bundle\Validator\Constraints\Zone_Cannot_Contain_Itself_Validator;
use Sylius\Bundle\Addressing_Bundle\Validator\Constraints\Zone_Member_Group_Validator;
use Sylius\Component\Addressing\Comparator\Address_Comparator;
use Sylius\Component\Addressing\Comparator\Address_Comparator_Interface;
use Sylius\Component\Addressing\Converter\Country_Name_Converter;
use Sylius\Component\Addressing\Converter\Country_Name_Converter_Interface;
use Sylius\Component\Addressing\Factory\Zone_Factory;
use Sylius\Component\Addressing\Factory\Zone_Factory_Interface;
use Sylius\Component\Addressing\Matcher\Zone_Matcher;
use Sylius\Component\Addressing\Matcher\Zone_Matcher_Interface;
use Sylius\Component\Addressing\Provider\Province_Naming_Provider;
use Sylius\Component\Addressing\Provider\Province_Naming_Provider_Interface;
return static function (Container_Configurator $container): void {
    $container->import('services/*.php');
    $services = $container->services();
    $services->set('sylius.custom_factory.zone', Zone_Factory::class)->decorate('sylius.factory.zone', null, 256)->args([service('sylius.custom_factory.zone.inner'), service('sylius.factory.zone_member')]);
    $services->alias(Zone_Factory_Interface::class, 'sylius.custom_factory.zone');
    $services->set('sylius.provider.province_naming', Province_Naming_Provider::class)->args([service('sylius.repository.province')])->lazy();
    $services->alias(Province_Naming_Provider_Interface::class, 'sylius.provider.province_naming');
    $services->set('sylius.matcher.zone', Zone_Matcher::class)->args([service('sylius.repository.zone')])->public();
    $services->alias(Zone_Matcher_Interface::class, 'sylius.matcher.zone')->public();
    $services->set('sylius.converter.country_name', Country_Name_Converter::class);
    $services->alias(Country_Name_Converter_Interface::class, 'sylius.converter.country_name');
    $services->set('sylius.comparator.address', Address_Comparator::class);
    $services->alias(Address_Comparator_Interface::class, 'sylius.comparator.address');
    $services->set('sylius.twig.extension.country_name', Country_Name_Extension::class)->tag('twig.extension');
    $services->set('sylius.twig.extension.province_naming', Province_Naming_Extension::class)->args([service('sylius.provider.province_naming')])->tag('twig.extension');
    $services->set('sylius.validator.valid_province_address', Province_Address_Constraint_Validator::class)->args([service('sylius.repository.country'), service('sylius.repository.province')])->tag('validator.constraint_validator', ['alias' => 'sylius_province_address_validator']);
    $services->set('sylius.validator.zone_cannot_contain_itself', Zone_Cannot_Contain_Itself_Validator::class)->tag('validator.constraint_validator', ['alias' => 'sylius_zone_cannot_contain_itself_validator']);
    $services->set('sylius.validator.unique_province_collection', Unique_Province_Collection_Validator::class)->tag('validator.constraint_validator', ['alias' => 'sylius_unique_province_collection_validator']);
    $services->set('sylius.validator.zone_member_group', Zone_Member_Group_Validator::class)->args(['%sylius.addressing.zone_member.validation_groups%'])->tag('validator.constraint_validator', ['alias' => 'sylius_zone_member_group']);
};
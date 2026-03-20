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
namespace Sylius\Bundle\Addressing_Bundle\Dependency_Injection;

use Sylius\Bundle\Addressing_Bundle\Doctrine\ORM\Address_Repository;
use Sylius\Bundle\Addressing_Bundle\Doctrine\ORM\Country_Repository;
use Sylius\Bundle\Addressing_Bundle\Doctrine\ORM\Province_Repository;
use Sylius\Bundle\Addressing_Bundle\Doctrine\ORM\Zone_Member_Repository;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Address_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Country_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Province_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Zone_Member_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Zone_Type;
use Sylius\Bundle\Addressing_Bundle\Repository\Zone_Repository;
use Sylius\Bundle\Resource_Bundle\Controller\Resource_Controller;
use Sylius\Bundle\Resource_Bundle\Sylius_Resource_Bundle;
use Sylius\Component\Addressing\Model\Address;
use Sylius\Component\Addressing\Model\Address_Interface;
use Sylius\Component\Addressing\Model\Address_Log_Entry;
use Sylius\Component\Addressing\Model\Country;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Model\Province;
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Component\Addressing\Model\Zone;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Addressing\Model\Zone_Member;
use Sylius\Component\Addressing\Model\Zone_Member_Interface;
use Sylius\Resource\Factory\Factory;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Config\Definition\Builder\Tree_Builder;
use Symfony\Component\Config\Definition\Configuration_Interface;
final class Configuration implements Configuration_Interface
{
    public function get_config_tree_builder(): Tree_Builder
    {
        $tree_builder = new Tree_Builder('sylius_addressing');
        /** @var ArrayNodeDefinition $rootNode */
        $root_node = $tree_builder->get_root_node();
        $root_node->add_defaults_if_not_set()->children()->scalar_node('driver')->default_value(Sylius_Resource_Bundle::DRIVER_DOCTRINE_ORM)->end()->scalar_node('provider')->default_value('sylius.province_name_provider')->end()->array_node('zone_member')->add_defaults_if_not_set()->children()->array_node('validation_groups')->use_attribute_as_key('name')->variable_prototype()->end()->end()->end()->end()->end();
        $this->add_resources_section($root_node);
        $this->add_scopes_section($root_node);
        return $tree_builder;
    }
    private function add_resources_section(Array_Node_Definition $node): void
    {
        $node->children()->array_node('resources')->add_defaults_if_not_set()->children()->array_node('address')->add_defaults_if_not_set()->children()->array_node('classes')->add_defaults_if_not_set()->children()->scalar_node('model')->default_value(Address::class)->cannot_be_empty()->end()->scalar_node('interface')->default_value(Address_Interface::class)->cannot_be_empty()->end()->scalar_node('controller')->default_value(Resource_Controller::class)->cannot_be_empty()->end()->scalar_node('repository')->default_value(Address_Repository::class)->cannot_be_empty()->end()->scalar_node('factory')->default_value(Factory::class)->end()->scalar_node('form')->default_value(Address_Type::class)->cannot_be_empty()->end()->end()->end()->end()->end()->array_node('address_log_entry')->add_defaults_if_not_set()->children()->array_node('classes')->add_defaults_if_not_set()->children()->scalar_node('model')->default_value(Address_Log_Entry::class)->cannot_be_empty()->end()->scalar_node('controller')->default_value(Resource_Controller::class)->cannot_be_empty()->end()->scalar_node('repository')->cannot_be_empty()->end()->scalar_node('factory')->default_value(Factory::class)->end()->end()->end()->end()->end()->array_node('country')->add_defaults_if_not_set()->children()->array_node('classes')->add_defaults_if_not_set()->children()->scalar_node('model')->default_value(Country::class)->cannot_be_empty()->end()->scalar_node('interface')->default_value(Country_Interface::class)->cannot_be_empty()->end()->scalar_node('controller')->default_value(Resource_Controller::class)->cannot_be_empty()->end()->scalar_node('repository')->default_value(Country_Repository::class)->cannot_be_empty()->end()->scalar_node('factory')->default_value(Factory::class)->end()->scalar_node('form')->default_value(Country_Type::class)->cannot_be_empty()->end()->end()->end()->end()->end()->array_node('province')->add_defaults_if_not_set()->children()->array_node('classes')->add_defaults_if_not_set()->children()->scalar_node('model')->default_value(Province::class)->cannot_be_empty()->end()->scalar_node('interface')->default_value(Province_Interface::class)->cannot_be_empty()->end()->scalar_node('controller')->default_value(Resource_Controller::class)->cannot_be_empty()->end()->scalar_node('repository')->default_value(Province_Repository::class)->cannot_be_empty()->end()->scalar_node('factory')->default_value(Factory::class)->end()->scalar_node('form')->default_value(Province_Type::class)->cannot_be_empty()->end()->end()->end()->end()->end()->array_node('zone')->add_defaults_if_not_set()->children()->array_node('classes')->add_defaults_if_not_set()->children()->scalar_node('model')->default_value(Zone::class)->cannot_be_empty()->end()->scalar_node('interface')->default_value(Zone_Interface::class)->cannot_be_empty()->end()->scalar_node('controller')->default_value(Resource_Controller::class)->cannot_be_empty()->end()->scalar_node('repository')->default_value(Zone_Repository::class)->cannot_be_empty()->end()->scalar_node('factory')->default_value(Factory::class)->end()->scalar_node('form')->default_value(Zone_Type::class)->cannot_be_empty()->end()->end()->end()->end()->end()->array_node('zone_member')->add_defaults_if_not_set()->children()->array_node('classes')->add_defaults_if_not_set()->children()->scalar_node('model')->default_value(Zone_Member::class)->cannot_be_empty()->end()->scalar_node('interface')->default_value(Zone_Member_Interface::class)->cannot_be_empty()->end()->scalar_node('controller')->default_value(Resource_Controller::class)->cannot_be_empty()->end()->scalar_node('repository')->default_value(Zone_Member_Repository::class)->cannot_be_empty()->end()->scalar_node('factory')->default_value(Factory::class)->end()->scalar_node('form')->default_value(Zone_Member_Type::class)->cannot_be_empty()->end()->end()->end()->end()->end()->end()->end()->end();
    }
    private function add_scopes_section(Array_Node_Definition $node): void
    {
        $node->children()->array_node('scopes')->use_attribute_as_key('name')->scalar_prototype()->end()->end()->end();
    }
}
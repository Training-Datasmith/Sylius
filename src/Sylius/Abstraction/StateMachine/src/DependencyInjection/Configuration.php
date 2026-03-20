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
namespace Sylius\Abstraction\State_Machine\Dependency_Injection;

use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Config\Definition\Builder\Tree_Builder;
use Symfony\Component\Config\Definition\Configuration_Interface;
final class Configuration implements Configuration_Interface
{
    public function get_config_tree_builder(): Tree_Builder
    {
        $tree_builder = new Tree_Builder('sylius_state_machine_abstraction');
        /** @var ArrayNodeDefinition $rootNode */
        $root_node = $tree_builder->get_root_node();
        $root_node->add_defaults_if_not_set()->children()->scalar_node('default_adapter')->default_value('symfony_workflow')->end()->array_node('graphs_to_adapters_mapping')->use_attribute_as_key('graph_name')->scalar_prototype()->end()->end()->end();
        return $tree_builder;
    }
}
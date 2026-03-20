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
namespace Sylius\Bundle\Admin_Bundle\Dependency_Injection;

use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Config\Definition\Builder\Tree_Builder;
use Symfony\Component\Config\Definition\Configuration_Interface;
final class Configuration implements Configuration_Interface
{
    public function get_config_tree_builder(): Tree_Builder
    {
        $tree_builder = new Tree_Builder('sylius_admin');
        /** @var ArrayNodeDefinition $rootNode */
        $root_node = $tree_builder->get_root_node();
        $root_node->children()->array_node('notifications')->add_defaults_if_not_set()->children()->boolean_node('enabled')->default_true()->end()->boolean_node('hub_enabled')->default_true()->end()->integer_node('frequency')->default_value(60)->end()->end()->end()->array_node('twig')->add_defaults_if_not_set()->children()->array_node('payment_method')->add_defaults_if_not_set()->children()->array_node('excluded_gateways')->scalar_prototype()->end()->default_value([])->end()->end()->end()->end()->end()->end();
        return $tree_builder;
    }
}
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

use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Extension\Extension;
use Symfony\Component\Dependency_Injection\Loader\Xml_File_Loader;
final class Sylius_State_Machine_Abstraction_Extension extends Extension
{
    public function load(array $configs, Container_Builder $container): void
    {
        $config = $this->process_configuration($this->get_configuration([], $container), $configs);
        $loader = new Xml_File_Loader($container, new File_Locator(dirname(__DIR__, 2) . '/config/'));
        $loader->load('services.xml');
        if ($container->has_parameter('kernel.bundles')) {
            $bundles = $container->get_parameter('kernel.bundles');
            if (array_key_exists('winzouStateMachineBundle', $bundles)) {
                $loader->load('services/integrations/winzou.xml');
            }
        }
        $container->set_parameter('sylius_abstraction.state_machine.default_adapter', $config['default_adapter']);
        $container->set_parameter('sylius_abstraction.state_machine.graphs_to_adapters_mapping', $config['graphs_to_adapters_mapping']);
    }
}
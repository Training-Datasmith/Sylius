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

use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Extension\Abstract_Resource_Extension;
use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
final class Sylius_Addressing_Extension extends Abstract_Resource_Extension
{
    public function load(array $configs, Container_Builder $container): void
    {
        $config = $this->process_configuration($this->get_configuration([], $container), $configs);
        $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../Resources/config'));
        $this->register_resources('sylius', $config['driver'], $config['resources'], $container);
        $loader->load('services.php');
        $container->set_parameter('sylius.scope.zone', $config['scopes']);
        $container->set_parameter('sylius.addressing.zone_member.validation_groups', $config['zone_member']['validation_groups']);
    }
}
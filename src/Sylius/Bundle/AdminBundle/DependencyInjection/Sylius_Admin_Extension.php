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

use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
use Symfony\Component\Http_Kernel\Dependency_Injection\Extension;
final class Sylius_Admin_Extension extends Extension
{
    public function load(array $configs, Container_Builder $container): void
    {
        $config = $this->process_configuration($this->get_configuration([], $container), $configs);
        $loader = new Php_File_Loader($container, new File_Locator(__DIR__ . '/../Resources/config'));
        $container->set_parameter('sylius.admin.notification.enabled', $config['notifications']['enabled']);
        $container->set_parameter('sylius.admin.notification.hub_enabled', $config['notifications']['hub_enabled']);
        $container->set_parameter('sylius.admin.notification.frequency', $config['notifications']['frequency']);
        $container->set_parameter('sylius.admin.shop_enabled', false);
        $container->set_parameter('sylius.admin.twig.payment_method.excluded_gateways', $config['twig']['payment_method']['excluded_gateways']);
        if ($container->has_parameter('kernel.bundles')) {
            $bundles = $container->get_parameter('kernel.bundles');
            if (array_key_exists('SyliusShopBundle', $bundles)) {
                $loader->load('services/integrations/shop.php');
                $container->set_parameter('sylius.admin.shop_enabled', true);
            }
        }
        $loader->load('services.php');
    }
}
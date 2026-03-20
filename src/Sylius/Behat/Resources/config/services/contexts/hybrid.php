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
use Sylius\Behat\Context\Hybrid\Setup\Cart_Context;
use Sylius\Behat\Context\Hybrid\Setup\Security_Context;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.behat.context.hybrid.shop.composite_cart', Cart_Context::class)->args([service('sylius.behat.context.api.shop.cart'), service('sylius.behat.context.ui.shop.cart')]);
    $services->set('sylius.behat.context.hybrid.shop.composite_customer', Security_Context::class)->args([service('sylius.behat.context.setup.shop_security'), service('sylius.behat.context.setup.shop_api_security'), service('sylius.behat.shared_storage')]);
};
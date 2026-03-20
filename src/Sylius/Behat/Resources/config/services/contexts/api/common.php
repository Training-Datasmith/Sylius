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
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Common\Response_Context;
use Sylius\Behat\Context\Api\Common\Save_Context;
use Sylius\Behat\Context\Api\Debug_Context;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.behat.context.api.admin.save', Save_Context::class)->args([service('sylius.behat.api_platform_client.admin')]);
    $services->set('sylius.behat.context.api.shop.save', Save_Context::class)->args([service('sylius.behat.api_platform_client.shop')]);
    $services->set('sylius.behat.context.api.admin.response', Response_Context::class)->args([service(Response_Checker_Interface::class), service('sylius.behat.api_platform_client.admin')]);
    $services->set('sylius.behat.context.api.shop.response', Response_Context::class)->args([service(Response_Checker_Interface::class), service('sylius.behat.api_platform_client.shop')]);
    $services->set('sylius.behat.context.api.debug', Debug_Context::class)->args([service(Response_Checker_Interface::class)]);
};
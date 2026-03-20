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
use Sylius\Behat\Client\Api_Platform_Client;
use Sylius\Behat\Client\Api_Platform_Security_Client;
use Sylius\Behat\Client\Content_Type_Guide;
use Sylius\Behat\Client\Request_Factory;
use Sylius\Behat\Client\Response_Checker;
use Sylius\Behat\Client\Response_Checker_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.behat.api_platform_client', Api_Platform_Client::class)->abstract()->args([service('test.client'), service('sylius.behat.shared_storage'), service('sylius.behat.request_factory'), service(Response_Checker_Interface::class), '%sylius.api.authorization_header%']);
    $services->set('sylius.behat.api_platform_client.shop', Api_Platform_Client::class)->parent('sylius.behat.api_platform_client')->args(['shop']);
    $services->set('sylius.behat.api_platform_client.admin', Api_Platform_Client::class)->parent('sylius.behat.api_platform_client')->args(['admin']);
    $services->set(Response_Checker_Interface::class, Response_Checker::class);
    $services->set('sylius.behat.client.admin_api_platform_security_client', Api_Platform_Security_Client::class)->args([service('test.client'), service('sylius.behat.shared_storage'), '%sylius.security.api_route%', 'admin/administrators/token']);
    $services->set('sylius.behat.client.shop_api_platform_security_client', Api_Platform_Security_Client::class)->args([service('test.client'), service('sylius.behat.shared_storage'), '%sylius.security.api_route%', 'shop/customers/token']);
    $services->set('sylius.behat.content_type_guide', Content_Type_Guide::class);
    $services->set('sylius.behat.request_factory', Request_Factory::class)->args([service('sylius.behat.content_type_guide'), '%sylius.security.api_route%']);
};
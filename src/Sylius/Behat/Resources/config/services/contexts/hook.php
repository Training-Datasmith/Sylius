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
use Sylius\Behat\Context\Hook\Bad_Gateway_Context;
use Sylius\Behat\Context\Hook\Cache_Context;
use Sylius\Behat\Context\Hook\Calendar_Context;
use Sylius\Behat\Context\Hook\Doctrine_Orm_Context;
use Sylius\Behat\Context\Hook\Guest_Cart_Context;
use Sylius\Behat\Context\Hook\Mailer_Context;
use Sylius\Behat\Context\Hook\Session_Context;
use Sylius\Behat\Context\Hook\Test_Theme_Context;
use Sylius\Bundle\Theme_Bundle\Configuration\Test\Test_Theme_Configuration_Manager_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.behat.context.hook.calendar', Calendar_Context::class)->args(['%sylius.behat.clock.date_file%']);
    $services->set('sylius.behat.context.hook.doctrine_orm', Doctrine_Orm_Context::class)->args([service('doctrine.orm.entity_manager')]);
    $services->set('sylius.behat.context.hook.session', Session_Context::class)->args([service('request_stack'), service('session.factory')->null_on_invalid()]);
    $services->set('sylius.behat.context.hook.test_theme', Test_Theme_Context::class)->args([service(Test_Theme_Configuration_Manager_Interface::class)]);
    $services->set('sylius.behat.context.hook.mailer', Mailer_Context::class)->args([service('test.mailer_pool')]);
    $services->set('sylius.behat.context.hook.cache', Cache_Context::class)->args([service('cache.app')]);
    $services->set('sylius.behat.context.hook.guest_cart', Guest_Cart_Context::class)->args(['%sylius.behat.guest_cart_token_file%']);
    $services->set('sylius.behat.context.hook.bad_gateway', Bad_Gateway_Context::class);
};
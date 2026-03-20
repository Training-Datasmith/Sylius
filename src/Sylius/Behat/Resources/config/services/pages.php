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
use Friends_Of_Behat\Page_Object_Extension\Page\Page;
use Friends_Of_Behat\Page_Object_Extension\Page\Symfony_Page;
use Sylius\Behat\Page\Error_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $container->import('pages/admin.php');
    $container->import('pages/shop.php');
    $container->import('pages/test_plugin.php');
    $parameters->set('sylius.behat.page.error.class', Error_Page::class);
    $services->set('sylius.behat.page', Page::class)->abstract()->args([service('behat.mink.default_session'), service('behat.mink.parameters')]);
    $services->set('sylius.behat.symfony_page', Symfony_Page::class)->abstract()->parent('sylius.behat.page')->args([service('router')]);
    $services->set('sylius.behat.page.error', '%sylius.behat.page.error.class%')->parent('sylius.behat.page');
};
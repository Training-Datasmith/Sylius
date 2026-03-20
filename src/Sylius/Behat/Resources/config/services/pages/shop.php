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
use Sylius\Behat\Page\Shop\Cart\Summary_Page;
use Sylius\Behat\Page\Shop\Home_Page;
use Sylius\Behat\Page\Shop\Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $container->import('shop/**/*.php');
    $parameters->set('sylius.behat.page.shop.cart_summary.class', Summary_Page::class);
    $parameters->set('sylius.behat.page.shop.home.class', Home_Page::class);
    $services->set('sylius.behat.page.shop.page', Page::class)->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.cart_summary', '%sylius.behat.page.shop.cart_summary.class%')->parent('sylius.behat.page.shop.page');
    $services->set('sylius.behat.page.shop.home', '%sylius.behat.page.shop.home.class%')->parent('sylius.behat.symfony_page');
};
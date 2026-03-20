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
use Sylius\Behat\Page\Shop\Order\Show_Page;
use Sylius\Behat\Page\Shop\Order\Thank_You_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.shop.order.thank_you.class', Thank_You_Page::class);
    $parameters->set('sylius.behat.page.shop.order.show.class', Show_Page::class);
    $services->set('sylius.behat.page.shop.order.thank_you', '%sylius.behat.page.shop.order.thank_you.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.order.show', '%sylius.behat.page.shop.order.show.class%')->parent('sylius.behat.symfony_page');
};
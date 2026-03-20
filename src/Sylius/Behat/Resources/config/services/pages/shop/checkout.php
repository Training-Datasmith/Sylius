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
use Sylius\Behat\Page\Shop\Checkout\Address_Page;
use Sylius\Behat\Page\Shop\Checkout\Complete_Page;
use Sylius\Behat\Page\Shop\Checkout\Select_Payment_Page;
use Sylius\Behat\Page\Shop\Checkout\Select_Shipping_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.shop.checkout.address.class', Address_Page::class);
    $parameters->set('sylius.behat.page.shop.checkout.select_payment.class', Select_Payment_Page::class);
    $parameters->set('sylius.behat.page.shop.checkout.select_shipping.class', Select_Shipping_Page::class);
    $parameters->set('sylius.behat.page.shop.checkout.complete.class', Complete_Page::class);
    $services->set('sylius.behat.page.shop.checkout.address', '%sylius.behat.page.shop.checkout.address.class%')->parent('sylius.behat.page.shop.page')->args([service('sylius.factory.address'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.page.shop.checkout.select_payment', '%sylius.behat.page.shop.checkout.select_payment.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.checkout.select_shipping', '%sylius.behat.page.shop.checkout.select_shipping.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.checkout.complete', '%sylius.behat.page.shop.checkout.complete.class%')->parent('sylius.behat.symfony_page')->args([service('sylius.behat.table_accessor')]);
};
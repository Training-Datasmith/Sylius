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
use Sylius\Behat\Page\Shop\Payment_Request\Payment_Method_Notify_Page;
use Sylius\Behat\Page\Shop\Payment_Request\Payment_Request_Notify_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.shop.payment_request.payment_method_notify.class', Payment_Method_Notify_Page::class);
    $parameters->set('sylius.behat.page.shop.payment_request.payment_request_notify.class', Payment_Request_Notify_Page::class);
    $services->set('sylius.behat.page.shop.payment_request.payment_method_notify', '%sylius.behat.page.shop.payment_request.payment_method_notify.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.payment_request.payment_request_notify', '%sylius.behat.page.shop.payment_request.payment_request_notify.class%')->parent('sylius.behat.symfony_page');
};
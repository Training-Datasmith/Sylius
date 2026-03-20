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
use Sylius\Behat\Page\Admin\Payment\Index_Page as PaymentIndexPage;
use Sylius\Behat\Page\Admin\Payment\Payment_Request\Index_Page;
use Sylius\Behat\Page\Admin\Payment\Payment_Request\Show_Page;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.payment.index.class', Payment_Index_Page::class);
    $parameters->set('sylius.behat.page.admin.payment.payment_request.index.class', Index_Page::class);
    $parameters->set('sylius.behat.page.admin.payment.payment_request.show.class', Show_Page::class);
    $services->defaults()->public();
    $services->set('sylius.behat.page.admin.payment.index', '%sylius.behat.page.admin.payment.index.class%')->private()->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_payment_index']);
    $services->set('sylius.behat.page.admin.payment.payment_request.index', '%sylius.behat.page.admin.payment.payment_request.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_payment_request_index', service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.page.admin.payment.payment_request.show', '%sylius.behat.page.admin.payment.payment_request.show.class%')->parent('sylius.behat.symfony_page');
};
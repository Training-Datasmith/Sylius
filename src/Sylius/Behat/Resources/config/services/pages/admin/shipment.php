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
use Sylius\Behat\Page\Admin\Shipment\Index_Page;
use Sylius\Behat\Page\Admin\Shipment\Show_Page;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.shipment.index.class', Index_Page::class);
    $parameters->set('sylius.behat.page.admin.shipment.show.class', Show_Page::class);
    $services->set('sylius.behat.page.admin.shipment.index', '%sylius.behat.page.admin.shipment.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_shipment_index', service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.page.admin.shipment.show', '%sylius.behat.page.admin.shipment.show.class%')->parent('sylius.behat.symfony_page');
};
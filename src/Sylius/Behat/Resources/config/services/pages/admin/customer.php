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
use Sylius\Behat\Page\Admin\Customer\Index_Page;
use Sylius\Behat\Page\Admin\Customer\Show_Page;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.customer.create.class', '%sylius.behat.page.admin.crud.create.class%');
    $parameters->set('sylius.behat.page.admin.customer.index.class', Index_Page::class);
    $parameters->set('sylius.behat.page.admin.customer.order_index.class', '%sylius.behat.page.admin.crud.index.class%');
    $parameters->set('sylius.behat.page.admin.customer.update.class', '%sylius.behat.page.admin.crud.update.class%');
    $parameters->set('sylius.behat.page.admin.customer.show.class', Show_Page::class);
    $services->set('sylius.behat.page.admin.customer.create', '%sylius.behat.page.admin.customer.create.class%')->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_customer_create']);
    $services->set('sylius.behat.page.admin.customer.index', '%sylius.behat.page.admin.customer.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_customer_index', service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.page.admin.customer.order_index', '%sylius.behat.page.admin.customer.order_index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_customer_order_index']);
    $services->set('sylius.behat.page.admin.customer.update', '%sylius.behat.page.admin.customer.update.class%')->parent('sylius.behat.page.admin.crud.update')->args(['sylius_admin_customer_update']);
    $services->set('sylius.behat.page.admin.customer.show', '%sylius.behat.page.admin.customer.show.class%')->parent('sylius.behat.symfony_page');
};
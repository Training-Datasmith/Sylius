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
use Sylius\Behat\Page\Admin\Order\History_Page;
use Sylius\Behat\Page\Admin\Order\Index_Page;
use Sylius\Behat\Page\Admin\Order\Show_Page;
use Sylius\Behat\Page\Admin\Order\Update_Page;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.order.index.class', Index_Page::class);
    $parameters->set('sylius.behat.page.admin.order.show.class', Show_Page::class);
    $parameters->set('sylius.behat.page.admin.order.update.class', Update_Page::class);
    $parameters->set('sylius.behat.page.admin.order.history.class', History_Page::class);
    $services->set('sylius.behat.page.admin.order.index', '%sylius.behat.page.admin.order.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_order_index', service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.page.admin.order.show', '%sylius.behat.page.admin.order.show.class%')->parent('sylius.behat.symfony_page')->args([service('sylius.behat.table_accessor')]);
    $services->set('sylius.behat.page.admin.order.update', '%sylius.behat.page.admin.order.update.class%')->parent('sylius.behat.page.admin.crud.update')->args(['sylius_admin_order_update']);
    $services->set('sylius.behat.page.admin.order.history', '%sylius.behat.page.admin.order.history.class%')->parent('sylius.behat.symfony_page');
};
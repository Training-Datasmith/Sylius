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
use Sylius\Behat\Page\Admin\Channel\Create_Page;
use Sylius\Behat\Page\Admin\Channel\Index_Page;
use Sylius\Behat\Page\Admin\Channel\Update_Page;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.channel.create.class', Create_Page::class);
    $parameters->set('sylius.behat.page.admin.channel.update.class', Update_Page::class);
    $parameters->set('sylius.behat.page.admin.channel.index.class', Index_Page::class);
    $services->set('sylius.behat.page.admin.channel.create', '%sylius.behat.page.admin.channel.create.class%')->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_channel_create', service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.page.admin.channel.index', '%sylius.behat.page.admin.channel.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_channel_index']);
    $services->set('sylius.behat.page.admin.channel.update', '%sylius.behat.page.admin.channel.update.class%')->parent('sylius.behat.page.admin.crud.update')->args(['sylius_admin_channel_update', service(Autocomplete_Helper_Interface::class)]);
};
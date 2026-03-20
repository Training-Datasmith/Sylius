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
use Sylius\Behat\Page\Admin\Administrator\Create_Page;
use Sylius\Behat\Page\Admin\Administrator\Update_Page;
use Sylius\Behat\Page\Admin\Crud\Index_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.behat.page.admin.administrator.create', Create_Page::class)->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_admin_user_create', service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.page.admin.administrator.index', Index_Page::class)->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_admin_user_index']);
    $services->set('sylius.behat.page.admin.administrator.update', Update_Page::class)->parent('sylius.behat.page.admin.crud.update')->args(['sylius_admin_admin_user_update']);
};
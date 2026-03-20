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
use Sylius\Behat\Page\Admin\Crud\Create_Page;
use Sylius\Behat\Page\Admin\Crud\Index_Page;
use Sylius\Behat\Page\Admin\Crud\Update_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $container->import('admin/**/*.php');
    $parameters->set('sylius.behat.page.admin.crud.index.class', Index_Page::class);
    $parameters->set('sylius.behat.page.admin.crud.create.class', Create_Page::class);
    $parameters->set('sylius.behat.page.admin.crud.update.class', Update_Page::class);
    $services->set('sylius.behat.page.admin.crud.index', '%sylius.behat.page.admin.crud.index.class%')->abstract()->parent('sylius.behat.symfony_page')->args([service('sylius.behat.table_accessor')]);
    $services->set('sylius.behat.page.admin.crud.create', '%sylius.behat.page.admin.crud.create.class%')->abstract()->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.admin.crud.update', '%sylius.behat.page.admin.crud.update.class%')->abstract()->parent('sylius.behat.symfony_page');
};
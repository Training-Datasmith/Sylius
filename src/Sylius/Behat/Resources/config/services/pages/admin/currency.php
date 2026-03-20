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
use Sylius\Behat\Page\Admin\Currency\Index_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.currency.create.class', '%sylius.behat.page.admin.crud.create.class%');
    $parameters->set('sylius.behat.page.admin.currency.index.class', Index_Page::class);
    $services->set('sylius.behat.page.admin.currency.create', '%sylius.behat.page.admin.currency.create.class%')->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_currency_create']);
    $services->set('sylius.behat.page.admin.currency.index', '%sylius.behat.page.admin.currency.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_currency_index']);
};
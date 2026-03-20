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
use Sylius\Behat\Page\Admin\Locale\Index_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.locale.create.class', '%sylius.behat.page.admin.crud.create.class%');
    $parameters->set('sylius.behat.page.admin.locale.index.class', Index_Page::class);
    $services->set('sylius.behat.page.admin.locale.create', '%sylius.behat.page.admin.locale.create.class%')->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_locale_create']);
    $services->set('sylius.behat.page.admin.locale.index', '%sylius.behat.page.admin.locale.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_locale_index']);
};
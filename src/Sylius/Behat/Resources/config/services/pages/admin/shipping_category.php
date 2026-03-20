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
use Sylius\Behat\Page\Admin\Shipping_Category\Create_Page;
use Sylius\Behat\Page\Admin\Shipping_Category\Update_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.shipping_category.create.class', Create_Page::class);
    $parameters->set('sylius.behat.page.admin.shipping_category.index.class', '%sylius.behat.page.admin.crud.index.class%');
    $parameters->set('sylius.behat.page.admin.shipping_category.update.class', Update_Page::class);
    $services->set('sylius.behat.page.admin.shipping_category.create', '%sylius.behat.page.admin.shipping_category.create.class%')->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_shipping_category_create']);
    $services->set('sylius.behat.page.admin.shipping_category.index', '%sylius.behat.page.admin.shipping_category.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_shipping_category_index']);
    $services->set('sylius.behat.page.admin.shipping_category.update', '%sylius.behat.page.admin.shipping_category.update.class%')->parent('sylius.behat.page.admin.crud.update')->args(['sylius_admin_shipping_category_update']);
};
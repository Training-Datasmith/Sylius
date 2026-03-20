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
use Sylius\Behat\Page\Admin\Product_Variant\Create_Page;
use Sylius\Behat\Page\Admin\Product_Variant\Generate_Page;
use Sylius\Behat\Page\Admin\Product_Variant\Index_Page;
use Sylius\Behat\Page\Admin\Product_Variant\Update_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.product_variant.create.class', Create_Page::class);
    $parameters->set('sylius.behat.page.admin.product_variant.generate.class', Generate_Page::class);
    $parameters->set('sylius.behat.page.admin.product_variant.index.class', Index_Page::class);
    $parameters->set('sylius.behat.page.admin.product_variant.update.class', Update_Page::class);
    $services->set('sylius.behat.page.admin.product_variant.create', '%sylius.behat.page.admin.product_variant.create.class%')->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_product_variant_create']);
    $services->set('sylius.behat.page.admin.product_variant.generate', '%sylius.behat.page.admin.product_variant.generate.class%')->parent('sylius.behat.symfony_page')->args(['product_variant']);
    $services->set('sylius.behat.page.admin.product_variant.index', '%sylius.behat.page.admin.product_variant.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_product_variant_index']);
    $services->set('sylius.behat.page.admin.product_variant.update', '%sylius.behat.page.admin.product_variant.update.class%')->parent('sylius.behat.page.admin.crud.update')->args(['sylius_admin_product_variant_update']);
};
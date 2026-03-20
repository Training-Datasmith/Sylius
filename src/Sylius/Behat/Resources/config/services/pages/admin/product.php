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
use Sylius\Behat\Page\Admin\Product\Create_Configurable_Product_Page;
use Sylius\Behat\Page\Admin\Product\Create_Simple_Product_Page;
use Sylius\Behat\Page\Admin\Product\Index_Page;
use Sylius\Behat\Page\Admin\Product\Index_Per_Taxon_Page;
use Sylius\Behat\Page\Admin\Product\Show_Page;
use Sylius\Behat\Page\Admin\Product\Update_Configurable_Product_Page;
use Sylius\Behat\Page\Admin\Product\Update_Simple_Product_Page;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.product.create_configurable.class', Create_Configurable_Product_Page::class);
    $parameters->set('sylius.behat.page.admin.product.create_simple.class', Create_Simple_Product_Page::class);
    $parameters->set('sylius.behat.page.admin.product.index.class', Index_Page::class);
    $parameters->set('sylius.behat.page.admin.product.index_per_taxon.class', Index_Per_Taxon_Page::class);
    $parameters->set('sylius.behat.page.admin.product.show.class', Show_Page::class);
    $parameters->set('sylius.behat.page.admin.product.update_simple.class', Update_Simple_Product_Page::class);
    $parameters->set('sylius.behat.page.admin.product.update_configurable.class', Update_Configurable_Product_Page::class);
    $services->set('sylius.behat.page.admin.product.create_configurable', '%sylius.behat.page.admin.product.create_configurable.class%')->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_product_create', service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.page.admin.product.create_simple', '%sylius.behat.page.admin.product.create_simple.class%')->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_product_create', service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.page.admin.product.index', '%sylius.behat.page.admin.product.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_product_index', service('sylius.behat.checker.image_existence'), service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.page.admin.product.index_per_taxon', '%sylius.behat.page.admin.product.index_per_taxon.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_product_taxon_index']);
    $services->set('sylius.behat.page.admin.product.update_configurable', '%sylius.behat.page.admin.product.update_configurable.class%')->parent('sylius.behat.page.admin.crud.update')->args(['sylius_admin_product_update', service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.page.admin.product.update_simple', '%sylius.behat.page.admin.product.update_simple.class%')->parent('sylius.behat.page.admin.crud.update')->args(['sylius_admin_product_update', service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.page.admin.product.show_page', '%sylius.behat.page.admin.product.show.class%')->parent('sylius.behat.symfony_page');
};
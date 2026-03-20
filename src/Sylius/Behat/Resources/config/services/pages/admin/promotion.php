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
use Sylius\Behat\Page\Admin\Promotion\Create_Page;
use Sylius\Behat\Page\Admin\Promotion\Index_Page;
use Sylius\Behat\Page\Admin\Promotion\Update_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.promotion.create.class', Create_Page::class);
    $parameters->set('sylius.behat.page.admin.promotion.update.class', Update_Page::class);
    $parameters->set('sylius.behat.page.admin.promotion.index.class', Index_Page::class);
    $services->set('sylius.behat.page.admin.promotion.create', '%sylius.behat.page.admin.promotion.create.class%')->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_promotion_create']);
    $services->set('sylius.behat.page.admin.promotion.update', '%sylius.behat.page.admin.promotion.update.class%')->parent('sylius.behat.page.admin.crud.update')->args(['sylius_admin_promotion_update']);
    $services->set('sylius.behat.page.admin.promotion.index', '%sylius.behat.page.admin.promotion.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_promotion_index']);
};
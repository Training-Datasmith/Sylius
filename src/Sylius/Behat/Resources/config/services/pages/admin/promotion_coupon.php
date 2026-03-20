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
use Sylius\Behat\Page\Admin\Promotion_Coupon\Generate_Page;
use Sylius\Behat\Page\Admin\Promotion_Coupon\Index_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.promotion_coupon.create.class', '%sylius.behat.page.admin.crud.create.class%');
    $parameters->set('sylius.behat.page.admin.promotion_coupon.generate.class', Generate_Page::class);
    $parameters->set('sylius.behat.page.admin.promotion_coupon.index.class', Index_Page::class);
    $parameters->set('sylius.behat.page.admin.promotion_coupon.update.class', '%sylius.behat.page.admin.crud.update.class%');
    $services->set('sylius.behat.page.admin.promotion_coupon.create', '%sylius.behat.page.admin.promotion_coupon.create.class%')->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_promotion_coupon_create']);
    $services->set('sylius.behat.page.admin.promotion_coupon.generate', '%sylius.behat.page.admin.promotion_coupon.generate.class%')->parent('sylius.behat.page.admin.crud.create')->args(['sylius_admin_promotion_coupon_generate']);
    $services->set('sylius.behat.page.admin.promotion_coupon.index', '%sylius.behat.page.admin.promotion_coupon.index.class%')->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_promotion_coupon_index']);
    $services->set('sylius.behat.page.admin.promotion_coupon.update', '%sylius.behat.page.admin.promotion_coupon.update.class%')->parent('sylius.behat.page.admin.crud.update')->args(['sylius_admin_promotion_coupon_update']);
};
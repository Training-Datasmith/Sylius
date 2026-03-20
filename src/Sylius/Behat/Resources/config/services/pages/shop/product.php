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
use Sylius\Behat\Page\Shop\Product\Index_Page;
use Sylius\Behat\Page\Shop\Product\Show_Page;
use Sylius\Behat\Page\Shop\Product_Review\Create_Page as ReviewCreatePage;
use Sylius\Behat\Page\Shop\Product_Review\Index_Page as ReviewIndexPage;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.shop.product.show.class', Show_Page::class);
    $parameters->set('sylius.behat.page.shop.product.index.class', Index_Page::class);
    $parameters->set('sylius.behat.page.shop.product_reviews.create.class', Review_Create_Page::class);
    $parameters->set('sylius.behat.page.shop.product_reviews.index.class', Review_Index_Page::class);
    $services->set('sylius.behat.page.shop.product.show', '%sylius.behat.page.shop.product.show.class%')->parent('sylius.behat.symfony_page')->args([service('sylius.behat.page.shop.cart_summary')]);
    $services->set('sylius.behat.page.shop.product.index', '%sylius.behat.page.shop.product.index.class%')->parent('sylius.behat.page.shop.page');
    $services->set('sylius.behat.page.shop.product_reviews.create', '%sylius.behat.page.shop.product_reviews.create.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.product_reviews.index', '%sylius.behat.page.shop.product_reviews.index.class%')->parent('sylius.behat.symfony_page');
};
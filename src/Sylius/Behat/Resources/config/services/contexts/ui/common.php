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
use Sylius\Behat\Context\Ui\Browser_Context;
use Sylius\Behat\Context\Ui\Channel_Context;
use Sylius\Behat\Context\Ui\Customer_Context;
use Sylius\Behat\Context\Ui\Email_Context;
use Sylius\Behat\Context\Ui\Save_Context;
use Sylius\Behat\Context\Ui\Theme_Context;
use Sylius\Behat\Context\Ui\User_Context;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.behat.context.ui.browser', Browser_Context::class)->args([service('sylius.behat.element.browser')]);
    $services->set('sylius.behat.context.ui.channel', Channel_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.channel_context_setter'), service('sylius.repository.channel'), service('sylius.behat.page.admin.channel.create'), service('sylius.behat.page.shop.home'), service('sylius.behat.page.test_plugin.main')]);
    $services->set('sylius.behat.context.ui.customer', Customer_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.page.admin.customer.show')]);
    $services->set('sylius.behat.context.ui.theme', Theme_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.page.admin.channel.index'), service('sylius.behat.page.admin.channel.update'), service('sylius.behat.page.shop.home')]);
    $services->set('sylius.behat.context.ui.user', User_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.shop_user'), service('sylius.behat.page.admin.customer.show'), service('sylius.behat.page.shop.home')]);
    $services->set('sylius.behat.context.ui.email', Email_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.email_checker'), service('translator')]);
    $services->set('sylius.behat.context.ui.save', Save_Context::class)->args([service('sylius.behat.element.save')]);
};
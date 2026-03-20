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
use Sylius\Behat\Context\Cli\Cancel_Unpaid_Orders_Context;
use Sylius\Behat\Context\Cli\Change_Admin_Password_Context;
use Sylius\Behat\Context\Cli\Installer_Context;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.behat.context.cli.installer', Installer_Context::class)->args([service('kernel'), service('doctrine.orm.entity_manager'), service('sylius.checker.installer.command_directory'), service('sylius.setup.installer.currency'), service('sylius.setup.installer.locale'), service('sylius.setup.installer.channel'), service('sylius.factory.admin_user'), service('sylius.repository.admin_user'), service('validator'), '%sylius_core.public_dir%']);
    $services->set('sylius.behat.context.cli.cancel_unpaid_orders', Cancel_Unpaid_Orders_Context::class)->args([service('kernel'), service('sylius.repository.order')]);
    $services->set('sylius.behat.context.cli.change_admin_password', Change_Admin_Password_Context::class)->args([service('kernel'), service('sylius.repository.admin_user'), service('security.user_password_hasher'), service('sylius.behat.shared_storage')]);
};
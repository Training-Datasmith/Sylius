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
use Sylius\Behat\Page\Admin\Account\Login_Page;
use Sylius\Behat\Page\Admin\Account\Request_Password_Reset_Page;
use Sylius\Behat\Page\Admin\Account\Reset_Password_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.admin.reset_password', Reset_Password_Page::class);
    $services->set('sylius.behat.page.admin.login', Login_Page::class)->parent('sylius.behat.symfony_page')->args([service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.page.admin.request_password_reset', Request_Password_Reset_Page::class)->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.admin.reset_password', '%sylius.behat.page.admin.reset_password%')->parent('sylius.behat.symfony_page')->args([service('sylius.behat.shared_storage')]);
};
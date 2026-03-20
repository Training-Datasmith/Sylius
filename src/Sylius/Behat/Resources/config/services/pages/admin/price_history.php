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
use Sylius\Behat\Page\Admin\Channel_Pricing_Log_Entry\Index_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.behat.page.admin.channel_pricing_log_entry.index', Index_Page::class)->public()->parent('sylius.behat.page.admin.crud.index')->args(['sylius_admin_channel_pricing_log_entry_index']);
};
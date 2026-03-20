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
namespace Symfony\Component\Dependency_Injection\Loader\Configurator;

use Sylius\Bundle\Addressing_Bundle\Event_Listener\Zone_Member_Integrity_Listener;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.listener.zone_member_integrity', Zone_Member_Integrity_Listener::class)->args([service('request_stack'), service('sylius.checker.zone_deletion'), service('sylius.checker.country_provinces_deletion')])->tag('kernel.event_listener', ['event' => 'sylius.zone.pre_delete', 'method' => 'protectFromRemovingZone'])->tag('kernel.event_listener', ['event' => 'sylius.country.pre_update', 'method' => 'protectFromRemovingProvinceWithinCountry']);
};
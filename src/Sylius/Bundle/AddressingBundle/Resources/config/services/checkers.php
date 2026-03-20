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

use Sylius\Component\Addressing\Checker\Country_Provinces_Deletion_Checker;
use Sylius\Component\Addressing\Checker\Country_Provinces_Deletion_Checker_Interface;
use Sylius\Component\Addressing\Checker\Zone_Deletion_Checker;
use Sylius\Component\Addressing\Checker\Zone_Deletion_Checker_Interface;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.checker.zone_deletion', Zone_Deletion_Checker::class)->args([service('sylius.repository.zone_member')]);
    $services->alias(Zone_Deletion_Checker_Interface::class, 'sylius.checker.zone_deletion');
    $services->set('sylius.checker.country_provinces_deletion', Country_Provinces_Deletion_Checker::class)->args([service('sylius.repository.zone_member'), service('sylius.repository.province')]);
    $services->alias(Country_Provinces_Deletion_Checker_Interface::class, 'sylius.checker.country_provinces_deletion');
};
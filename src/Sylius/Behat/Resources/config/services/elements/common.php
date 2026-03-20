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
use Friends_Of_Behat\Page_Object_Extension\Element\Element;
use Sylius\Behat\Element\Browser_Element;
use Sylius\Behat\Element\Save_Element;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.behat.element', Element::class)->abstract()->args([service('behat.mink.default_session'), service('behat.mink.parameters')]);
    $services->set('sylius.behat.element.browser', Browser_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.save', Save_Element::class)->parent('sylius.behat.element');
};
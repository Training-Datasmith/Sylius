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
use Sylius\Behat\Element\Admin\Product_Attribute\Filter_Element;
use Sylius\Behat\Element\Admin\Product_Attribute\Form_Element;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.behat.element.admin.product_attribute.form', Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.element.admin.product_attribute.filter', Filter_Element::class)->parent('sylius.behat.element');
};
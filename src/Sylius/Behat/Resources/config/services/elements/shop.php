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
use Sylius\Behat\Element\Shop\Account\Register_Element;
use Sylius\Behat\Element\Shop\Cart_Widget_Element;
use Sylius\Behat\Element\Shop\Cart_Widget_Element_Interface;
use Sylius\Behat\Element\Shop\Checkout_Subtotal_Element;
use Sylius\Behat\Element\Shop\Checkout_Subtotal_Element_Interface;
use Sylius\Behat\Element\Shop\Menu_Element;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.behat.element.shop.account.register', Register_Element::class)->parent('sylius.behat.element')->args([service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.element.shop.menu', Menu_Element::class)->parent('sylius.behat.element');
    $services->set(Cart_Widget_Element_Interface::class, Cart_Widget_Element::class)->parent('sylius.behat.element');
    $services->set(Checkout_Subtotal_Element_Interface::class, Checkout_Subtotal_Element::class)->parent('sylius.behat.element');
};
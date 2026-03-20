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
use Sylius\Behat\Element\Admin\Product\Associations_Form_Element;
use Sylius\Behat\Element\Admin\Product\Attributes_Form_Element;
use Sylius\Behat\Element\Admin\Product\Channel_Pricings_Form_Element;
use Sylius\Behat\Element\Admin\Product\Media_Form_Element;
use Sylius\Behat\Element\Admin\Product\Taxonomy_Form_Element;
use Sylius\Behat\Element\Admin\Product\Translations_Form_Element;
use Sylius\Behat\Element\Admin\Product_Association_Type\Form_Element;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.behat.element.admin.product_association_type.form', Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.element.admin.product.association_form', Associations_Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.element.admin.product.attributes_form', Attributes_Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.element.admin.product.channel_pricing_form', Channel_Pricings_Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.element.admin.product.media_form', Media_Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.element.admin.product.taxonomy_form', Taxonomy_Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.element.admin.product.translations_form', Translations_Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
};
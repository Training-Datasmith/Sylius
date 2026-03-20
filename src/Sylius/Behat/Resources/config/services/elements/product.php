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
use Sylius\Behat\Element\Product\Index_Page\Vertical_Menu_Element;
use Sylius\Behat\Element\Product\Show_Page\Associations_Element;
use Sylius\Behat\Element\Product\Show_Page\Attributes_Element;
use Sylius\Behat\Element\Product\Show_Page\Details_Element;
use Sylius\Behat\Element\Product\Show_Page\Lowest_Price_Information_Element;
use Sylius\Behat\Element\Product\Show_Page\Lowest_Price_Information_Element_Interface;
use Sylius\Behat\Element\Product\Show_Page\Media_Element;
use Sylius\Behat\Element\Product\Show_Page\Options_Element;
use Sylius\Behat\Element\Product\Show_Page\Pricing_Element;
use Sylius\Behat\Element\Product\Show_Page\Shipping_Element;
use Sylius\Behat\Element\Product\Show_Page\Taxonomy_Element;
use Sylius\Behat\Element\Product\Show_Page\Translations_Element;
use Sylius\Behat\Element\Product\Show_Page\Variants_Element;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.behat.element.product.show.associations', Associations_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.product.show.attributes', Attributes_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.product.show.details', Details_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.product.show.media', Media_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.product.show.more_details', Translations_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.product.show.pricing', Pricing_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.product.show.shipping', Shipping_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.product.show.taxonomy', Taxonomy_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.product.show.options', Options_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.product.show.variants', Variants_Element::class)->parent('sylius.behat.element');
    $services->set(Lowest_Price_Information_Element_Interface::class, Lowest_Price_Information_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.product.index.vertical_menu', Vertical_Menu_Element::class)->parent('sylius.behat.element');
};
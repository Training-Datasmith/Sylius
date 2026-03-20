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
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Shop\Address_Context;
use Sylius\Behat\Context\Api\Shop\Cart_Context;
use Sylius\Behat\Context\Api\Shop\Channel_Context;
use Sylius\Behat\Context\Api\Shop\Checkout\Checkout_Complete_Context;
use Sylius\Behat\Context\Api\Shop\Checkout\Checkout_Order_Details_Context;
use Sylius\Behat\Context\Api\Shop\Checkout\Checkout_Shipping_Context;
use Sylius\Behat\Context\Api\Shop\Checkout_Context;
use Sylius\Behat\Context\Api\Shop\Contact_Context;
use Sylius\Behat\Context\Api\Shop\Currency_Context;
use Sylius\Behat\Context\Api\Shop\Customer_Context;
use Sylius\Behat\Context\Api\Shop\Exchange_Rate_Context;
use Sylius\Behat\Context\Api\Shop\Homepage_Context;
use Sylius\Behat\Context\Api\Shop\Locale_Context;
use Sylius\Behat\Context\Api\Shop\Login_Context;
use Sylius\Behat\Context\Api\Shop\Order_Context;
use Sylius\Behat\Context\Api\Shop\Order_Item_Context;
use Sylius\Behat\Context\Api\Shop\Payment_Context;
use Sylius\Behat\Context\Api\Shop\Payment_Request_Context;
use Sylius\Behat\Context\Api\Shop\Product_Attribute_Context;
use Sylius\Behat\Context\Api\Shop\Product_Context;
use Sylius\Behat\Context\Api\Shop\Product_Review_Context;
use Sylius\Behat\Context\Api\Shop\Product_Variant_Context;
use Sylius\Behat\Context\Api\Shop\Promotion_Context;
use Sylius\Behat\Context\Api\Shop\Registration_Context;
use Sylius\Behat\Context\Api\Shop\Shipment_Context;
use Sylius\Behat\Context\Api\Shop\Taxon_Context;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.behat.context.api.shop.address', Address_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('api_platform.symfony.iri_converter'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.shop.channel', Channel_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), '%sylius.security.api_route%']);
    $services->set('sylius.behat.context.api.shop.currency', Currency_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.shop.cart', Cart_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('sylius.resolver.product_variant'), service('api_platform.iri_converter'), service('sylius.behat.request_factory'), '%sylius.security.api_route%', service('sylius.repository.order')]);
    $services->set('sylius.behat.context.api.shop.customer', Customer_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service('sylius.behat.shared_storage'), service(Response_Checker_Interface::class), service('sylius.behat.context.api.shop.registration'), service('sylius.behat.context.api.shop.login'), service('sylius.behat.context.setup.shop_api_security'), service('sylius.behat.request_factory'), '%sylius.security.api_route%']);
    $services->set('sylius.behat.context.api.shop.exchange_rate', Exchange_Rate_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.shop.promotion', Promotion_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service('sylius.behat.shared_storage'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.shop.checkout', Checkout_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.context.api.shop.checkout.shipping'), service('sylius.repository.order'), service('sylius.repository.payment_method'), service('sylius.resolver.product_variant'), service('api_platform.iri_converter'), service('sylius.behat.shared_storage'), service('sylius.behat.request_factory'), service('sylius.behat.factory.address'), '%sylius.model.shipping_method.class%', '%sylius.model.payment_method.class%']);
    $services->set('sylius.behat.context.api.shop.homepage', Homepage_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('api_platform.iri_converter'), service('doctrine.orm.entity_manager'), '%sylius.security.api_route%']);
    $services->set('sylius.behat.context.api.shop.login', Login_Context::class)->args([service('sylius.behat.client.shop_api_platform_security_client'), service('sylius.behat.api_platform_client.shop'), service('api_platform.iri_converter'), service('test.client'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('sylius.behat.request_factory'), '%sylius.security.api_route%']);
    $services->set('sylius.behat.context.api.shop.product', Product_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('api_platform.iri_converter'), service('sylius.behat.channel_context_setter'), service('sylius.behat.request_factory'), service('doctrine.orm.entity_manager'), '%sylius.security.api_route%', service('sylius.resolver.product_variant.default')]);
    $services->set('sylius.behat.context.api.shop.product_attribute', Product_Attribute_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.shop.product_variant', Product_Variant_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('api_platform.iri_converter')]);
    $services->set('sylius.behat.context.api.shop.product_review', Product_Review_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('api_platform.iri_converter')]);
    $services->set('sylius.behat.context.api.shop.registration', Registration_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service('sylius.behat.context.api.shop.login'), service('sylius.behat.shared_storage'), service(Response_Checker_Interface::class), service('sylius.behat.request_factory'), '%sylius.security.api_route%']);
    $services->set('sylius.behat.context.api.shop.order', Order_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('api_platform.iri_converter'), service('sylius.behat.api_admin_security'), service('sylius.behat.request_factory'), '%sylius.security.api_route%']);
    $services->set('sylius.behat.context.api.shop.order_item', Order_Item_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.shop.payment_request', Payment_Request_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.request_factory'), service('sylius.repository.payment_request')]);
    $services->set('sylius.behat.context.api.shop.payment', Payment_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.shop.shipment', Shipment_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.shop.locale', Locale_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.shop.contact', Contact_Context::class)->args([service('sylius.behat.request_factory'), service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.shop.checkout.shipping', Checkout_Shipping_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('api_platform.iri_converter'), service('sylius.repository.shipping_method')]);
    $services->set('sylius.behat.context.api.shop.checkout.complete', Checkout_Complete_Context::class)->args([service('sylius.behat.request_factory'), service('sylius.behat.shared_storage'), service('sylius.behat.api_platform_client.shop')]);
    $services->set('sylius.behat.context.api.shop.checkout.order_details', Checkout_Order_Details_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.shop.taxon', Taxon_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service(Response_Checker_Interface::class), service('api_platform.iri_converter'), service('doctrine.orm.entity_manager')]);
};
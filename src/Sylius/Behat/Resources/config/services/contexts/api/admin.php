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
use Sylius\Behat\Context\Api\Admin\Browsing_Catalog_Promotion_Product_Variants_Context;
use Sylius\Behat\Context\Api\Admin\Browsing_Product_Variants_Context;
use Sylius\Behat\Context\Api\Admin\Channel_Pricing_Log_Entry_Context;
use Sylius\Behat\Context\Api\Admin\Creating_Product_Variant_Context;
use Sylius\Behat\Context\Api\Admin\Dashboard_Context;
use Sylius\Behat\Context\Api\Admin\Login_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Administrators_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Catalog_Promotions_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Channel_Price_History_Config_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Channels_Billing_Data_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Channels_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Countries_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Currencies_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Customer_Groups_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Customers_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Exchange_Rates_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Locales_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Orders_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Payment_Methods_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Payment_Requests_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Payments_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Placed_Order_Addresses_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Product_Associations_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Product_Association_Types_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Product_Attributes_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Product_Images_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Product_Options_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Product_Reviews_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Products_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Product_Taxons_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Product_Variants_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Product_Variants_Prices_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Promotion_Coupons_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Promotions_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Shipments_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Shipping_Categories_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Shipping_Methods_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Tax_Categories_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Taxon_Images_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Taxons_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Tax_Rates_Context;
use Sylius\Behat\Context\Api\Admin\Managing_Zones_Context;
use Sylius\Behat\Context\Api\Admin\Removing_Product_Context;
use Sylius\Behat\Context\Api\Admin\Removing_Taxon_Context;
use Sylius\Behat\Context\Api\Admin\Resetting_Password_Context;
use Sylius\Behat\Context\Api\Admin\Translation_Context;
use Sylius\Behat\Service\Converter\Iri_Converter;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.behat.context.api.admin.login', Login_Context::class)->args([service('sylius.behat.client.admin_api_platform_security_client'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.translation', Translation_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class)]);
    $services->set(Browsing_Catalog_Promotion_Product_Variants_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.iri_converter')]);
    $services->set('sylius.behat.context.api.admin.browsing_product_variant', Browsing_Product_Variants_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.admin.creating_product_variant', Creating_Product_Variant_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service('api_platform.iri_converter')]);
    $services->set('sylius.behat.context.api.admin.managing_administrators', Managing_Administrators_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('behat.mink.parameters'), service('translator')]);
    $services->set('sylius.behat.context.api.admin.managing_product_taxons', Managing_Product_Taxons_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service('api_platform.iri_converter'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_channels', Managing_Channels_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.symfony.iri_converter')]);
    $services->set(Managing_Channels_Billing_Data_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.admin.managing_countries', Managing_Countries_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('api_platform.iri_converter')]);
    $services->set('sylius.behat.context.api.admin.managing_currencies', Managing_Currencies_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.admin.managing_exchange_rates', Managing_Exchange_Rates_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), '%sylius.security.api_route%']);
    $services->set('sylius.behat.context.api.admin.managing_locales', Managing_Locales_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.admin.managing_product_associations', Managing_Product_Associations_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service(Iri_Converter::class), service('sylius.repository.product_association'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_product_association_types', Managing_Product_Association_Types_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_product_attributes', Managing_Product_Attributes_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_product_images', Managing_Product_Images_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('behat.mink.parameters'), service(Iri_Converter::class)]);
    $services->set('sylius.behat.context.api.admin.managing_product_options', Managing_Product_Options_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('api_platform.iri_converter')]);
    $services->set('sylius.behat.context.api.admin.managing_products', Managing_Products_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service(Iri_Converter::class), service('sylius.behat.shared_storage'), '%sylius.security.api_route%']);
    $services->set('sylius.behat.context.api.admin.managing_product_variants', Managing_Product_Variants_Context::class)->args([service('sylius.resolver.product_variant'), service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.symfony.iri_converter')]);
    $services->set('sylius.behat.context.api.admin.managing_product_variants_prices', Managing_Product_Variants_Prices_Context::class)->args([service('sylius.behat.api_platform_client.admin')]);
    $services->set('sylius.behat.context.api.admin.managing_tax_categories', Managing_Tax_Categories_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.admin.managing_taxons', Managing_Taxons_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service(Iri_Converter::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_taxon_images', Managing_Taxon_Images_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), service('behat.mink.parameters')]);
    $services->set('sylius.behat.context.api.admin.managing_shipping_categories', Managing_Shipping_Categories_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.admin.managing_shipping_methods', Managing_Shipping_Methods_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service(Iri_Converter::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_product_reviews', Managing_Product_Reviews_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service(Iri_Converter::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_payments', Managing_Payments_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.symfony.iri_converter'), '%sylius.security.api_route%']);
    $services->set('sylius.behat.context.api.admin.managing_shipments', Managing_Shipments_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service(Iri_Converter::class), service('sylius.behat.shared_storage'), '%sylius.security.api_route%']);
    $services->set('sylius.behat.context.api.admin.managing_orders', Managing_Orders_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.iri_converter'), service('sylius.behat.api_admin_security'), service('sylius.behat.shared_storage'), service('sylius.behat.api.shared_security')]);
    $services->set('sylius.behat.context.api.admin.managing_payment_methods', Managing_Payment_Methods_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.iri_converter'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_zones', Managing_Zones_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.removing_product', Removing_Product_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class)]);
    $services->set(Removing_Taxon_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.admin.managing_promotions', Managing_Promotions_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.iri_converter')]);
    $services->set('sylius.behat.context.api.admin.managing_catalog_promotions', Managing_Catalog_Promotions_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.symfony.iri_converter'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_tax_rates', Managing_Tax_Rates_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.iri_converter'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.resetting_password', Resetting_Password_Context::class)->args([service('sylius.behat.api_platform_client.shop'), service('sylius.behat.request_factory'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage'), '%sylius.security.api_route%']);
    $services->set('sylius.behat.context.api.admin.channel_pricing_log_entry', Channel_Pricing_Log_Entry_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_customers', Managing_Customers_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.iri_converter'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_customer_groups', Managing_Customer_Groups_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class)]);
    $services->set('sylius.behat.context.api.admin.managing_placed_order_addresses', Managing_Placed_Order_Addresses_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_promotion_coupons', Managing_Promotion_Coupons_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service('sylius.behat.request_factory'), service(Response_Checker_Interface::class), service('api_platform.symfony.iri_converter')]);
    $services->set('sylius.behat.context.api.admin.dashboard_context', Dashboard_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('sylius.behat.clock')]);
    $services->set(Managing_Channel_Price_History_Config_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.symfony.iri_converter'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.api.admin.managing_payment_requests', Managing_Payment_Requests_Context::class)->args([service('sylius.behat.api_platform_client.admin'), service(Response_Checker_Interface::class), service('api_platform.symfony.iri_converter'), service('sylius.repository.payment_request'), service('sylius.behat.request_factory'), service('sylius.behat.api.shared_security'), service('sylius.behat.shared_storage')]);
};
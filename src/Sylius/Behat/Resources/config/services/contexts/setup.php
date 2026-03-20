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
use Sylius\Behat\Context\Setup\Address_Context;
use Sylius\Behat\Context\Setup\Admin_Security_Context;
use Sylius\Behat\Context\Setup\Admin_User_Context;
use Sylius\Behat\Context\Setup\Calendar_Context;
use Sylius\Behat\Context\Setup\Cart_Context;
use Sylius\Behat\Context\Setup\Catalog_Promotion_Context;
use Sylius\Behat\Context\Setup\Channel_Context;
use Sylius\Behat\Context\Setup\Checkout\Address_Context as CheckoutAddressContext;
use Sylius\Behat\Context\Setup\Checkout\Payment_Context as CheckoutPaymentContext;
use Sylius\Behat\Context\Setup\Checkout\Shipping_Context as CheckoutShippingContext;
use Sylius\Behat\Context\Setup\Checkout_Context;
use Sylius\Behat\Context\Setup\Currency_Context;
use Sylius\Behat\Context\Setup\Customer_Context;
use Sylius\Behat\Context\Setup\Customer_Group_Context;
use Sylius\Behat\Context\Setup\Exchange_Rate_Context;
use Sylius\Behat\Context\Setup\Geographical_Context;
use Sylius\Behat\Context\Setup\Locale_Context;
use Sylius\Behat\Context\Setup\Order_Context;
use Sylius\Behat\Context\Setup\Payment_Context;
use Sylius\Behat\Context\Setup\Payment_Request_Context;
use Sylius\Behat\Context\Setup\Price_History_Context;
use Sylius\Behat\Context\Setup\Product_Association_Context;
use Sylius\Behat\Context\Setup\Product_Attribute_Context;
use Sylius\Behat\Context\Setup\Product_Context;
use Sylius\Behat\Context\Setup\Product_Option_Context;
use Sylius\Behat\Context\Setup\Product_Review_Context;
use Sylius\Behat\Context\Setup\Product_Taxon_Context;
use Sylius\Behat\Context\Setup\Promotion_Context;
use Sylius\Behat\Context\Setup\Shipping_Category_Context;
use Sylius\Behat\Context\Setup\Shipping_Context;
use Sylius\Behat\Context\Setup\Shop_Security_Context;
use Sylius\Behat\Context\Setup\Taxation_Context;
use Sylius\Behat\Context\Setup\Taxonomy_Context;
use Sylius\Behat\Context\Setup\Theme_Context;
use Sylius\Behat\Context\Setup\User_Context;
use Sylius\Behat\Context\Setup\Zone_Context;
use Sylius\Bundle\Theme_Bundle\Configuration\Test\Test_Theme_Configuration_Manager_Interface;
use Sylius\Resource\Generator\Randomness_Generator_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.behat.context.setup.address', Address_Context::class)->args([service('sylius.repository.address'), service('sylius.manager.customer'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.setup.admin_user', Admin_User_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.fixture.example_factory.admin_user'), service('sylius.repository.admin_user'), service('sylius.uploader.image'), service('doctrine.orm.entity_manager'), service('behat.mink.parameters'), service('sylius.factory.avatar_image')]);
    $services->set('sylius.behat.context.setup.calendar', Calendar_Context::class)->args(['%sylius.behat.clock.date_file%']);
    $services->set('sylius.behat.context.setup.cart', Cart_Context::class)->args([service('sylius.repository.order'), service('sylius.command_bus'), service('sylius.resolver.product_variant'), service('sylius.random_generator'), service('sylius.behat.shared_storage'), service('sylius.behat.context.setup.checkout.address'), service('sylius.behat.context.setup.checkout.shipping'), service('sylius.behat.context.setup.checkout.payment'), '%sylius.behat.guest_cart_token_file%']);
    $services->set('sylius.behat.context.setup.checkout', Checkout_Context::class)->args([service('sylius.repository.order'), service('sylius.repository.shipping_method'), service('sylius.repository.payment_method'), service('sylius.command_bus'), service('sylius.factory.address'), service('sylius.behat.shared_storage'), service('sylius.behat.context.setup.checkout.shipping'), service('sylius.behat.context.setup.checkout.payment')]);
    $services->set('sylius.behat.context.setup.checkout.address', Checkout_Address_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.command_bus'), service('sylius.behat.factory.address'), service('sylius.converter.country_name')]);
    $services->set('sylius.behat.context.setup.checkout.shipping', Checkout_Shipping_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.command_bus')]);
    $services->set('sylius.behat.context.setup.checkout.payment', Checkout_Payment_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.command_bus')]);
    $services->set('sylius.behat.context.setup.channel', Channel_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.channel_context_setter'), service('sylius.behat.factory.default_united_states_channel'), service('sylius.behat.factory.default_channel'), service('sylius.repository.channel'), service('sylius.manager.channel'), service('sylius.factory.shop_billing_data')]);
    $services->set('sylius.behat.context.setup.currency', Currency_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.currency'), service('sylius.factory.currency'), service('sylius.manager.channel')]);
    $services->set('sylius.behat.context.setup.customer', Customer_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.customer'), service('sylius.manager.customer'), service('sylius.factory.customer'), service('sylius.factory.shop_user'), service('sylius.factory.address')]);
    $services->set('sylius.behat.context.setup.customer_group', Customer_Group_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.customer_group'), service('sylius.factory.customer_group')]);
    $services->set('sylius.behat.context.setup.exchange_rate', Exchange_Rate_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.factory.exchange_rate'), service('sylius.repository.exchange_rate')]);
    $services->set('sylius.behat.context.setup.geographical', Geographical_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.factory.country'), service('sylius.factory.province'), service('sylius.repository.country'), service('sylius.converter.country_name'), service('sylius.manager.province'), service('sylius.repository.province')]);
    $services->set('sylius.behat.context.setup.locale', Locale_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.converter.locale'), service('sylius.factory.locale'), service('sylius.repository.locale'), service('sylius.manager.locale'), service('sylius.manager.channel')]);
    $services->set('sylius.behat.context.setup.order', Order_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.factory.order'), service('sylius.factory.address'), service('sylius.factory.customer'), service('sylius.factory.order_item'), service('sylius.factory.shipment'), service('sylius_abstraction.state_machine'), service('sylius.repository.country'), service('sylius.repository.customer'), service('sylius.repository.order'), service('sylius.repository.payment_method'), service('sylius.repository.shipping_method'), service('sylius.resolver.product_variant'), service('sylius.modifier.order_item_quantity'), service('doctrine.orm.entity_manager'), service('sylius.behat.clock'), service(Randomness_Generator_Interface::class)]);
    $services->set('sylius.behat.context.setup.payment', Payment_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.payment_method'), service('sylius.fixture.example_factory.payment_method'), service('sylius.factory.payment_method_translation'), service('sylius.manager.payment_method'), ['offline' => 'Offline']]);
    $services->set(Price_History_Context::class)->args([service('sylius.behat.context.setup.calendar'), service('sylius.manager.channel_pricing'), service('sylius.resolver.product_variant')]);
    $services->set('sylius.behat.context.setup.product', Product_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.product'), service('sylius.factory.product'), service('sylius.factory.product_translation'), service('sylius.factory.product_variant'), service('sylius.factory.product_variant_translation'), service('sylius.factory.channel_pricing'), service('sylius.factory.product_option'), service('sylius.factory.product_option_value'), service('sylius.factory.product_image'), service('sylius.factory.product_taxon'), service('doctrine.orm.entity_manager'), service('sylius.generator.product_variant'), service('sylius.repository.product_variant'), service('sylius.resolver.product_variant'), service('sylius.uploader.image'), service('sylius.generator.slug'), service('behat.mink.parameters'), service('sylius.event_bus'), service('sylius.behat.context.setup.product_taxon')]);
    $services->set('sylius.behat.context.setup.product_association', Product_Association_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.factory.product_association_type'), service('sylius.factory.product_association_type_translation'), service('sylius.factory.product_association'), service('sylius.repository.product_association_type'), service('sylius.repository.product_association'), service('doctrine.orm.entity_manager')]);
    $services->set('sylius.behat.context.setup.product_attribute', Product_Attribute_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.product_attribute'), service('sylius.factory.product_attribute'), service('sylius.factory.product_attribute_value'), service('doctrine.orm.entity_manager')]);
    $services->set('sylius.behat.context.setup.product_option', Product_Option_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.product_option'), service('sylius.factory.product_option'), service('sylius.factory.product_option_value'), service('doctrine.orm.entity_manager')]);
    $services->set('sylius.behat.context.setup.product_review', Product_Review_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.factory.product_review'), service('sylius.repository.product_review'), service('sylius_abstraction.state_machine')]);
    $services->set('sylius.behat.context.setup.product_taxon', Product_Taxon_Context::class)->args([service('sylius.factory.product_taxon'), service('doctrine.orm.entity_manager')]);
    $services->set('sylius.behat.context.setup.promotion', Promotion_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.factory.promotion_action'), service('sylius.factory.promotion_coupon'), service('sylius.factory.promotion_rule'), service('sylius.repository.promotion'), service('sylius.generator.promotion_coupon'), service('doctrine.orm.entity_manager'), service('sylius.fixture.example_factory.promotion'), service('sylius.command_bus')]);
    $services->set('sylius.behat.context.setup.admin_security', Admin_Security_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.admin_security'), service('sylius.fixture.example_factory.admin_user'), service('sylius.repository.admin_user')]);
    $services->set('sylius.behat.context.setup.admin_api_security', Admin_Security_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.api_admin_security'), service('sylius.fixture.example_factory.admin_user'), service('sylius.repository.admin_user')]);
    $services->set('sylius.behat.context.setup.shop_security', Shop_Security_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.shop_security'), service('sylius.fixture.example_factory.shop_user'), service('sylius.repository.shop_user'), service('lexik_jwt_authentication.jwt_manager')]);
    $services->set('sylius.behat.context.setup.shop_api_security', Shop_Security_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.api_shop_security'), service('sylius.fixture.example_factory.shop_user'), service('sylius.repository.shop_user'), service('lexik_jwt_authentication.jwt_manager')]);
    $services->set('sylius.behat.context.setup.shipping', Shipping_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.shipping_method'), service('sylius.repository.zone'), service('sylius.fixture.example_factory.shipping_method'), service('sylius.factory.shipping_method_rule'), service('sylius.manager.shipping_method')]);
    $services->set('sylius.behat.context.setup.shipping_category', Shipping_Category_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.factory.shipping_category'), service('sylius.repository.shipping_category')]);
    $services->set('sylius.behat.context.setup.taxation', Taxation_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.factory.tax_rate'), service('sylius.factory.tax_category'), service('sylius.repository.tax_rate'), service('sylius.repository.tax_category'), service('doctrine.orm.entity_manager')]);
    $services->set('sylius.behat.context.setup.taxonomy', Taxonomy_Context::class)->args([service('sylius.repository.taxon'), service('sylius.factory.taxon'), service('sylius.factory.taxon_translation'), service('sylius.factory.taxon_image'), service('doctrine.orm.entity_manager'), service('sylius.uploader.image'), service('sylius.generator.taxon_slug'), service('behat.mink.parameters')]);
    $services->set('sylius.behat.context.setup.theme', Theme_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.theme'), service('sylius.manager.channel'), service(Test_Theme_Configuration_Manager_Interface::class)]);
    $services->set('sylius.behat.context.setup.user', User_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.shop_user'), service('sylius.fixture.example_factory.shop_user'), service('sylius.manager.shop_user'), service('sylius.command_bus'), '%sylius.shop_user.token.password_reset.ttl%']);
    $services->set('sylius.behat.context.setup.zone', Zone_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.zone'), service('doctrine.orm.entity_manager'), service('sylius.factory.zone'), service('sylius.factory.zone_member')]);
    $services->set(Catalog_Promotion_Context::class)->args([service('sylius.fixture.example_factory.catalog_promotion'), service('sylius.factory.catalog_promotion_scope'), service('sylius.factory.catalog_promotion_action'), service('sylius.manager.catalog_promotion'), service('sylius.repository.channel'), service('sylius_abstraction.state_machine'), service('sylius.event_bus'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.setup.payment_request', Payment_Request_Context::class)->args([service('sylius.command_bus'), service('sylius.repository.payment_request'), service('sylius.factory.payment_request'), service('sylius_abstraction.state_machine')]);
};
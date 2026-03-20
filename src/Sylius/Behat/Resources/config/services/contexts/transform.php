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
use Sylius\Behat\Context\Transform\Address_Context;
use Sylius\Behat\Context\Transform\Admin_User_Context;
use Sylius\Behat\Context\Transform\Cart_Context;
use Sylius\Behat\Context\Transform\Catalog_Promotion_Context;
use Sylius\Behat\Context\Transform\Channel_Context;
use Sylius\Behat\Context\Transform\Country_Context;
use Sylius\Behat\Context\Transform\Coupon_Context;
use Sylius\Behat\Context\Transform\Currency_Context;
use Sylius\Behat\Context\Transform\Customer_Context;
use Sylius\Behat\Context\Transform\Customer_Group_Context;
use Sylius\Behat\Context\Transform\Date_Time_Context;
use Sylius\Behat\Context\Transform\Exchange_Rate_Context;
use Sylius\Behat\Context\Transform\Lexical_Context;
use Sylius\Behat\Context\Transform\Locale_Context;
use Sylius\Behat\Context\Transform\Order_Context;
use Sylius\Behat\Context\Transform\Payment_Method_Context;
use Sylius\Behat\Context\Transform\Product_Association_Type_Context;
use Sylius\Behat\Context\Transform\Product_Attribute_Context;
use Sylius\Behat\Context\Transform\Product_Context;
use Sylius\Behat\Context\Transform\Product_Option_Context;
use Sylius\Behat\Context\Transform\Product_Option_Value_Context;
use Sylius\Behat\Context\Transform\Product_Review_Context;
use Sylius\Behat\Context\Transform\Product_Variant_Context;
use Sylius\Behat\Context\Transform\Promotion_Context;
use Sylius\Behat\Context\Transform\Province_Context;
use Sylius\Behat\Context\Transform\Shared_Storage_Context;
use Sylius\Behat\Context\Transform\Shipping_Calculator_Context;
use Sylius\Behat\Context\Transform\Shipping_Category_Context;
use Sylius\Behat\Context\Transform\Shipping_Method_Context;
use Sylius\Behat\Context\Transform\Shop_User_Context;
use Sylius\Behat\Context\Transform\Tax_Category_Context;
use Sylius\Behat\Context\Transform\Taxon_Context;
use Sylius\Behat\Context\Transform\Tax_Rate_Context;
use Sylius\Behat\Context\Transform\Theme_Context;
use Sylius\Behat\Context\Transform\User_Context;
use Sylius\Behat\Context\Transform\Zone_Context;
use Sylius\Behat\Context\Transform\Zone_Member_Context;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.behat.context.transform.address', Address_Context::class)->args([service('sylius.factory.address'), service('sylius.converter.country_name'), service('sylius.repository.address'), service('sylius.fixture.example_factory.address')]);
    $services->set(Catalog_Promotion_Context::class)->args([service('sylius.repository.catalog_promotion')]);
    $services->set('sylius.behat.context.transform.channel', Channel_Context::class)->args([service('sylius.repository.channel')]);
    $services->set('sylius.behat.context.transform.country', Country_Context::class)->args([service('sylius.converter.country_name'), service('sylius.repository.country')]);
    $services->set('sylius.behat.context.transform.coupon', Coupon_Context::class)->args([service('sylius.repository.promotion_coupon')]);
    $services->set('sylius.behat.context.transform.currency', Currency_Context::class)->args([service('sylius.converter.currency_name'), service('sylius.repository.currency')]);
    $services->set('sylius.behat.context.transform.customer', Customer_Context::class)->args([service('sylius.repository.customer'), service('sylius.factory.customer'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.transform.customer_group', Customer_Group_Context::class)->args([service('sylius.repository.customer_group')]);
    $services->set('sylius.behat.context.transform.date_time', Date_Time_Context::class);
    $services->set('sylius.behat.context.transform.exchange_rate', Exchange_Rate_Context::class)->args([service('sylius.converter.currency_name'), service('sylius.repository.currency'), service('sylius.repository.exchange_rate')]);
    $services->set('sylius.behat.context.transform.lexical', Lexical_Context::class);
    $services->set('sylius.behat.context.transform.locale', Locale_Context::class)->args([service('sylius.converter.locale'), service('sylius.repository.locale'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.transform.order', Order_Context::class)->args([service('sylius.repository.customer'), service('sylius.repository.order'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.transform.payment', Payment_Method_Context::class)->args([service('sylius.repository.payment_method')]);
    $services->set('sylius.behat.context.transform.product', Product_Context::class)->args([service('sylius.repository.product'), '%locale%']);
    $services->set('sylius.behat.context.transform.product_association_type', Product_Association_Type_Context::class)->args([service('sylius.repository.product_association_type')]);
    $services->set('sylius.behat.context.transform.product_attribute', Product_Attribute_Context::class)->args([service('sylius.repository.product_attribute_translation')]);
    $services->set('sylius.behat.context.transform.product_option', Product_Option_Context::class)->args([service('sylius.repository.product_option')]);
    $services->set('sylius.behat.context.transform.product_option_value', Product_Option_Value_Context::class)->args([service('sylius.repository.product_option_value')]);
    $services->set('sylius.behat.context.transform.product_review', Product_Review_Context::class)->args([service('sylius.repository.product_review')]);
    $services->set('sylius.behat.context.transform.product_variant', Product_Variant_Context::class)->args([service('sylius.repository.product'), service('sylius.repository.product_variant'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.transform.promotion', Promotion_Context::class)->args([service('sylius.repository.promotion'), service('sylius.repository.promotion_coupon')]);
    $services->set('sylius.behat.context.transform.province', Province_Context::class)->args([service('sylius.repository.province')]);
    $services->set('sylius.behat.context.transform.shared_storage', Shared_Storage_Context::class)->args([service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.transform.shipping_calculator', Shipping_Calculator_Context::class)->args(['%sylius.shipping_calculators%', service('translator')]);
    $services->set('sylius.behat.context.transform.shipping_category', Shipping_Category_Context::class)->args([service('sylius.repository.shipping_category')]);
    $services->set('sylius.behat.context.transform.shipping_method', Shipping_Method_Context::class)->args([service('sylius.repository.shipping_method')]);
    $services->set('sylius.behat.context.transform.tax_category', Tax_Category_Context::class)->args([service('sylius.repository.tax_category')]);
    $services->set('sylius.behat.context.transform.tax_rate', Tax_Rate_Context::class)->args([service('sylius.repository.tax_rate')]);
    $services->set('sylius.behat.context.transform.taxon', Taxon_Context::class)->args([service('sylius.repository.taxon'), '%locale%']);
    $services->set('sylius.behat.context.transform.theme', Theme_Context::class)->args([service('sylius.repository.theme')]);
    $services->set('sylius.behat.context.transform.user', User_Context::class)->args([service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.transform.admin', Admin_User_Context::class)->args([service('sylius.repository.admin_user'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.transform.cart', Cart_Context::class)->args([service('sylius.repository.order'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.transform.zone', Zone_Context::class)->args([service('sylius.repository.zone')]);
    $services->set('sylius.behat.context.transform.zone_member', Zone_Member_Context::class)->args([service('sylius.converter.country_name'), service('sylius.repository.province'), service('sylius.repository.zone'), service('sylius.repository.zone_member')]);
    $services->set('sylius.behat.context.transform.shop_user', Shop_User_Context::class)->args([service('sylius.repository.shop_user')]);
};
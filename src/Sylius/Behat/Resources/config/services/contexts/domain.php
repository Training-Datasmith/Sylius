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
use Sylius\Behat\Context\Domain\Cart_Context;
use Sylius\Behat\Context\Domain\Managing_Orders_Context;
use Sylius\Behat\Context\Domain\Managing_Payments_Context;
use Sylius\Behat\Context\Domain\Managing_Price_History_Context;
use Sylius\Behat\Context\Domain\Managing_Products_Context;
use Sylius\Behat\Context\Domain\Managing_Promotion_Coupons_Context;
use Sylius\Behat\Context\Domain\Managing_Promotions_Context;
use Sylius\Behat\Context\Domain\Managing_Shipments_Context;
use Sylius\Behat\Context\Domain\Managing_Shipping_Methods_Context;
use Sylius\Behat\Context\Domain\Notification_Context;
use Sylius\Behat\Context\Domain\Security_Context;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.behat.context.domain.notification', Notification_Context::class);
    $services->set('sylius.behat.context.domain.managing_orders', Managing_Orders_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.order'), service('sylius.repository.order_item'), service('sylius.repository.address'), service('sylius.repository.adjustment'), service('sylius.manager.order'), service('sylius.resolver.product_variant'), service('sylius.updater.unpaid_orders_state')]);
    $services->set('sylius.behat.context.domain.managing_payments', Managing_Payments_Context::class)->args([service('sylius.repository.payment')]);
    $services->set(Managing_Price_History_Context::class)->args([service('sylius.repository.channel_pricing_log_entry'), service('sylius.resolver.product_variant'), service('sylius.remover.channel_pricing_log_entries')]);
    $services->set('sylius.behat.context.domain.managing_products', Managing_Products_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.product'), service('sylius.repository.product_variant'), service('sylius.repository.product_review')]);
    $services->set('sylius.behat.context.domain.managing_promotions', Managing_Promotions_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.promotion'), service('sylius.manager.promotion')]);
    $services->set('sylius.behat.context.domain.managing_promotion_coupons', Managing_Promotion_Coupons_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.promotion_coupon')]);
    $services->set('sylius.behat.context.domain.security', Security_Context::class);
    $services->set('sylius.behat.context.domain.managing_shipments', Managing_Shipments_Context::class)->args([service('sylius.repository.shipment')]);
    $services->set('sylius.behat.context.domain.cart', Cart_Context::class)->args([service('sylius.manager.order'), service('sylius.remover.expired_carts')]);
    $services->set('sylius.behat.context.domain.managing_shipping_methods', Managing_Shipping_Methods_Context::class)->args([service('sylius.repository.shipping_method'), service('sylius.manager.shipping_method')]);
};
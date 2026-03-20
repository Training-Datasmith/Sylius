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
use Sylius\Behat\Context\Ui\Shop\Account_Context;
use Sylius\Behat\Context\Ui\Shop\Address_Book_Context;
use Sylius\Behat\Context\Ui\Shop\Authorization_Context;
use Sylius\Behat\Context\Ui\Shop\Browsing_Product_Context;
use Sylius\Behat\Context\Ui\Shop\Cart_Context;
use Sylius\Behat\Context\Ui\Shop\Checkout\Checkout_Addressing_Context;
use Sylius\Behat\Context\Ui\Shop\Checkout\Checkout_Complete_Context;
use Sylius\Behat\Context\Ui\Shop\Checkout\Checkout_Order_Details_Context;
use Sylius\Behat\Context\Ui\Shop\Checkout\Checkout_Payment_Context;
use Sylius\Behat\Context\Ui\Shop\Checkout\Checkout_Shipping_Context;
use Sylius\Behat\Context\Ui\Shop\Checkout\Checkout_Thank_You_Context;
use Sylius\Behat\Context\Ui\Shop\Checkout\Registration_After_Checkout_Context;
use Sylius\Behat\Context\Ui\Shop\Checkout_Context;
use Sylius\Behat\Context\Ui\Shop\Contact_Context;
use Sylius\Behat\Context\Ui\Shop\Currency_Context;
use Sylius\Behat\Context\Ui\Shop\Error_Page_Context;
use Sylius\Behat\Context\Ui\Shop\Homepage_Context;
use Sylius\Behat\Context\Ui\Shop\Locale_Context;
use Sylius\Behat\Context\Ui\Shop\Login_Context;
use Sylius\Behat\Context\Ui\Shop\Payment_Request_Context;
use Sylius\Behat\Context\Ui\Shop\Product_Attribute_Context;
use Sylius\Behat\Context\Ui\Shop\Product_Context;
use Sylius\Behat\Context\Ui\Shop\Product_Review_Context;
use Sylius\Behat\Context\Ui\Shop\Registration_Context;
use Sylius\Behat\Element\Product\Show_Page\Lowest_Price_Information_Element_Interface;
use Sylius\Behat\Element\Shop\Cart_Widget_Element_Interface;
use Sylius\Behat\Element\Shop\Checkout_Subtotal_Element_Interface;
use Sylius\Behat\Service\Session_Manager_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.behat.context.ui.shop.checkout', Checkout_Context::class)->args([service('sylius.behat.page.shop.checkout.address'), service('sylius.behat.page.shop.checkout.select_payment'), service('sylius.behat.page.shop.checkout.select_shipping'), service('sylius.behat.page.shop.checkout.complete'), service('sylius.behat.page.shop.account.register'), service('sylius.behat.element.shop.account.register'), service('sylius.behat.current_page_resolver'), service('sylius.behat.context.ui.shop.checkout.addressing'), service('sylius.behat.context.ui.shop.checkout.shipping'), service('sylius.behat.context.ui.shop.checkout.payment'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.ui.shop.checkout.thank_you', Checkout_Thank_You_Context::class)->args([service('sylius.behat.page.shop.order.thank_you'), service('sylius.behat.page.shop.account.order.show'), service('sylius.repository.order'), service('sylius.behat.page.shop.order.show')]);
    $services->set('sylius.behat.context.ui.shop.checkout.order_details', Checkout_Order_Details_Context::class)->args([service('sylius.behat.page.shop.order.show'), service('sylius.behat.page.shop.order.thank_you')]);
    $services->set('sylius.behat.context.ui.shop.checkout.addressing', Checkout_Addressing_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.page.shop.checkout.address'), service('sylius.behat.factory.address'), service('sylius.comparator.address'), service('sylius.behat.page.shop.checkout.select_shipping'), service('sylius.behat.java_script_test_helper')]);
    $services->set('sylius.behat.context.ui.shop.checkout.shipping', Checkout_Shipping_Context::class)->args([service('sylius.behat.page.shop.checkout.select_shipping'), service('sylius.behat.page.shop.checkout.select_payment'), service('sylius.behat.page.shop.checkout.complete')]);
    $services->set('sylius.behat.context.ui.shop.checkout.payment', Checkout_Payment_Context::class)->args([service('sylius.behat.page.shop.checkout.select_payment'), service('sylius.behat.page.shop.checkout.complete')]);
    $services->set('sylius.behat.context.ui.shop.checkout.complete', Checkout_Complete_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.page.shop.checkout.complete'), service('sylius.behat.notification_checker.shop'), service('sylius.behat.page.shop.order.thank_you'), service('sylius.repository.order'), service('doctrine.orm.entity_manager')]);
    $services->set('sylius.behat.context.ui.shop.checkout.registration_after_checkout', Registration_After_Checkout_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.page.shop.account.login'), service('sylius.behat.page.shop.order.thank_you'), service('sylius.behat.page.shop.home'), service('sylius.behat.page.shop.account.verify'), service('sylius.behat.page.shop.account.register.thank_you'), service('sylius.behat.page.shop.account.dashboard'), service('sylius.behat.element.shop.account.register'), service('sylius.repository.customer')]);
    $services->set('sylius.behat.context.ui.shop.account', Account_Context::class)->args([service('sylius.behat.page.shop.account.dashboard'), service('sylius.behat.page.shop.account.profile_update'), service('sylius.behat.page.shop.account.change_password'), service('sylius.behat.page.shop.account.order.index'), service('sylius.behat.page.shop.account.order.show'), service('sylius.behat.page.shop.account.login'), service('sylius.behat.notification_checker.shop'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.ui.shop.address_book', Address_Book_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.repository.address'), service('sylius.behat.page.shop.account.address_book.index'), service('sylius.behat.page.shop.account.address_book.create'), service('sylius.behat.page.shop.account.address_book.update'), service('sylius.behat.current_page_resolver'), service('sylius.behat.notification_checker.shop')]);
    $services->set('sylius.behat.context.ui.shop.authorization', Authorization_Context::class)->args([service('sylius.behat.page.shop.account.login'), service('sylius.behat.page.shop.account.register'), service('sylius.behat.element.shop.account.register')]);
    $services->set('sylius.behat.context.ui.shop.cart', Cart_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.page.shop.cart_summary'), service('sylius.behat.page.shop.checkout.address'), service(Checkout_Subtotal_Element_Interface::class), service('sylius.behat.page.shop.product.show'), service(Cart_Widget_Element_Interface::class), service('sylius.behat.notification_checker.shop'), service(Session_Manager_Interface::class), service('sylius.behat.element.browser')]);
    $services->set('sylius.behat.context.ui.shop.contact', Contact_Context::class)->args([service('sylius.behat.page.shop.contact'), service('sylius.behat.notification_checker.shop')]);
    $services->set('sylius.behat.context.ui.shop.currency', Currency_Context::class)->args([service('sylius.behat.page.shop.home')]);
    $services->set('sylius.behat.context.ui.shop.error_page', Error_Page_Context::class)->args([service('sylius.behat.page.error')]);
    $services->set('sylius.behat.context.ui.shop.homepage', Homepage_Context::class)->args([service('sylius.behat.page.shop.home'), service('sylius.behat.element.shop.menu')]);
    $services->set('sylius.behat.context.ui.shop.locale', Locale_Context::class)->args([service('sylius.behat.page.shop.home'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.context.ui.shop.login', Login_Context::class)->args([service('sylius.behat.page.shop.home'), service('sylius.behat.page.shop.account.login'), service('sylius.behat.page.shop.account.register'), service('sylius.behat.page.shop.account.request_password_reset'), service('sylius.behat.page.shop.account.reset_password'), service('sylius.behat.page.shop.account.well_known_password_change'), service('sylius.behat.element.shop.account.register'), service('sylius.behat.notification_checker.shop'), service('sylius.behat.current_page_resolver'), service('sylius.behat.shared_storage'), service('sylius.repository.customer')]);
    $services->set('sylius.behat.context.ui.shop.product', Product_Context::class)->args([service('sylius.behat.page.shop.product.show'), service('sylius.behat.page.shop.product.index'), service('sylius.behat.page.shop.product_reviews.index'), service('sylius.behat.page.error'), service('sylius.behat.element.product.index.vertical_menu'), service('sylius.behat.channel_context_setter'), service(Lowest_Price_Information_Element_Interface::class)]);
    $services->set('sylius.behat.context.ui.shop.product_attribute', Product_Attribute_Context::class)->args([service('sylius.behat.page.shop.product.show')]);
    $services->set('sylius.behat.context.ui.shop.browsing_product', Browsing_Product_Context::class)->args([service('sylius.behat.page.shop.product.show')]);
    $services->set('sylius.behat.context.ui.shop.product_review', Product_Review_Context::class)->args([service('sylius.behat.page.shop.product_reviews.create'), service('sylius.behat.notification_checker.shop'), service('sylius.behat.page.shop.product_reviews.index')]);
    $services->set('sylius.behat.context.ui.shop.registration', Registration_Context::class)->args([service('sylius.behat.shared_storage'), service('sylius.behat.page.shop.account.dashboard'), service('sylius.behat.page.shop.home'), service('sylius.behat.page.shop.account.login'), service('sylius.behat.page.shop.account.register'), service('sylius.behat.page.shop.account.register.thank_you'), service('sylius.behat.page.shop.account.verify'), service('sylius.behat.page.shop.account.profile_update'), service('sylius.behat.element.shop.account.register'), service('sylius.behat.notification_checker.shop'), service('sylius.repository.customer')]);
    $services->set('sylius.behat.context.ui.shop.payment_request', Payment_Request_Context::class)->args([service('sylius.repository.payment_request'), service('sylius.behat.page.shop.payment_request.payment_method_notify'), service('sylius.behat.page.shop.payment_request.payment_request_notify'), service('doctrine.orm.entity_manager')]);
};
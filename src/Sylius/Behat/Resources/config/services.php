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
use Psr\Clock\Clock_Interface as PsrClockInterface;
use Sylius\Behat\Service\Accessor\Notification_Accessor;
use Sylius\Behat\Service\Accessor\Table_Accessor;
use Sylius\Behat\Service\Api_Security_Service;
use Sylius\Behat\Service\Checker\Email_Checker;
use Sylius\Behat\Service\Checker\Image_Existence_Checker;
use Sylius\Behat\Service\Clock;
use Sylius\Behat\Service\Context\Guest_Cart_Context;
use Sylius\Behat\Service\Converter\Iri_Converter;
use Sylius\Behat\Service\Factory\Address_Factory;
use Sylius\Behat\Service\Helper\Autocomplete_Helper;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Sylius\Behat\Service\Helper\Java_Script_Test_Helper;
use Sylius\Behat\Service\Message_Send_Cacher;
use Sylius\Behat\Service\Notification_Checker;
use Sylius\Behat\Service\Payment_Request\Command_Handler\Offline\Notify_Payment_Request_Handler;
use Sylius\Behat\Service\Payment_Request\Command_Provider\Offline\Notify_Payment_Request_Command_Provider;
use Sylius\Behat\Service\Payment_Request\Provider\Dummy_Notify_Payment_Provider;
use Sylius\Behat\Service\Provider\Email_Messages_Provider;
use Sylius\Behat\Service\Provider\Email_Messages_Provider_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver;
use Sylius\Behat\Service\Response_Loader;
use Sylius\Behat\Service\Security_Service;
use Sylius\Behat\Service\Session_Manager;
use Sylius\Behat\Service\Session_Manager_Interface;
use Sylius\Behat\Service\Setter\Channel_Context_Setter;
use Sylius\Behat\Service\Setter\Cookie_Setter;
use Sylius\Behat\Service\Shared_Security_Service;
use Sylius\Behat\Service\Shared_Storage;
use Sylius\Bundle\Api_Bundle\Resolver\Operation_Resolver_Interface;
use Symfony\Component\Clock\Clock_Interface as SymfonyClockInterface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $container->import('services/api.php');
    $container->import('services/contexts.php');
    $container->import('services/elements/**/*.php');
    $container->import('services/pages.php');
    $parameters->set('sylius.behat.clock.date_file', '%kernel.project_dir%/var/date.txt');
    $parameters->set('sylius.behat.guest_cart_token_file', '%kernel.project_dir%/var/guest_cart_token.txt');
    $parameters->set('sylius.behat.notification_accessor.admin.locator', '[data-test-sylius-flash-message]');
    $parameters->set('sylius.behat.notification_accessor.shop.locator', '[data-test-sylius-flash-message]');
    $parameters->set('sylius.behat.notification_checker.admin.class_map', ['failure' => 'alert-danger', 'error' => 'alert-danger', 'info' => 'alert-info', 'success' => 'alert-success']);
    $parameters->set('sylius.behat.notification_checker.shop.class_map', ['failure' => 'alert-danger', 'error' => 'alert-danger', 'info' => 'alert-info', 'success' => 'alert-success']);
    $services->defaults()->public();
    $services->set('sylius.behat.cookie_setter', Cookie_Setter::class)->private()->args([service('behat.mink.default_session'), service('behat.mink.parameters')]);
    $services->set('sylius.behat.channel_context_setter', Channel_Context_Setter::class)->private()->args([service('sylius.behat.cookie_setter')]);
    $services->set('sylius.behat.admin_security', Security_Service::class)->private()->args([service('request_stack'), service('sylius.behat.cookie_setter'), 'admin', service('session.factory')->null_on_invalid()]);
    $services->set('sylius.behat.api_admin_security', Api_Security_Service::class)->public()->args([service('sylius.behat.shared_storage'), service('lexik_jwt_authentication.jwt_manager'), 'api_admin']);
    $services->set('sylius.behat.api_shop_security', Api_Security_Service::class)->public()->args([service('sylius.behat.shared_storage'), service('lexik_jwt_authentication.jwt_manager'), 'api_shop']);
    $services->set('sylius.behat.shop_security', Security_Service::class)->private()->args([service('request_stack'), service('sylius.behat.cookie_setter'), 'shop', service('session.factory')->null_on_invalid()]);
    $services->set(Session_Manager_Interface::class, Session_Manager::class)->private()->args([service('behat.mink'), service('sylius.behat.shared_storage'), service('sylius.behat.shop_security')]);
    $services->set('sylius.behat.shared_security', Shared_Security_Service::class)->private()->args([service('sylius.behat.admin_security')]);
    $services->set('sylius.behat.api.shared_security', Shared_Security_Service::class)->private()->args([service('sylius.behat.api_admin_security')]);
    $services->set('sylius.behat.table_accessor', Table_Accessor::class)->private();
    $services->set('sylius.behat.checker.image_existence', Image_Existence_Checker::class)->args([service('sylius.liip.filter_service'), '%sylius_core.public_dir%']);
    $services->set('sylius.behat.response_loader', Response_Loader::class)->private();
    $services->set('sylius.behat.notification_accessor.admin', Notification_Accessor::class)->private()->args([service('behat.mink.default_session'), '%sylius.behat.notification_accessor.admin.locator%']);
    $services->set('sylius.behat.notification_accessor.shop', Notification_Accessor::class)->private()->args([service('behat.mink.default_session'), '%sylius.behat.notification_accessor.shop.locator%']);
    $services->set('sylius.behat.notification_checker.admin', Notification_Checker::class)->private()->args([service('sylius.behat.notification_accessor.admin'), '%sylius.behat.notification_checker.admin.class_map%']);
    $services->set('sylius.behat.notification_checker.shop', Notification_Checker::class)->private()->args([service('sylius.behat.notification_accessor.shop'), '%sylius.behat.notification_checker.shop.class_map%']);
    $services->set('sylius.behat.current_page_resolver', Current_Page_Resolver::class)->private()->args([service('behat.mink.default_session'), service('router')]);
    $services->set('sylius.behat.shared_storage', Shared_Storage::class)->private();
    $services->set(Autocomplete_Helper_Interface::class, Autocomplete_Helper::class);
    $services->set('sylius.behat.java_script_test_helper', Java_Script_Test_Helper::class)->args([1000000, 7]);
    $services->set('sylius.behat.email_checker', Email_Checker::class)->args([service(Email_Messages_Provider_Interface::class)]);
    $services->set(Email_Messages_Provider_Interface::class, Email_Messages_Provider::class)->args([service('test.mailer_pool')]);
    $services->set('sylius.behat.message_send_cacher', Message_Send_Cacher::class)->args([service('test.mailer_pool')])->tag('kernel.event_subscriber');
    $services->set('sylius.behat.clock', Clock::class)->args(['%sylius.behat.clock.date_file%']);
    $services->alias('clock', 'sylius.behat.clock');
    $services->alias(Symfony_Clock_Interface::class, 'sylius.behat.clock');
    $services->alias(Psr_Clock_Interface::class, 'sylius.behat.clock');
    $services->alias('argument_resolver.datetime', 'sylius.behat.clock');
    $services->set(Iri_Converter::class)->private()->decorate('api_platform.symfony.iri_converter', null, 32)->args([service('.inner'), service(Operation_Resolver_Interface::class)]);
    $services->set('sylius.behat.factory.address', Address_Factory::class)->decorate('sylius.custom_factory.address')->args([service('.inner')]);
    $services->set(Dummy_Notify_Payment_Provider::class)->autoconfigure()->args([service('sylius.repository.payment')]);
    $services->set(Notify_Payment_Request_Command_Provider::class)->tag('sylius.command_provider.payment_request.offline', ['action' => 'notify']);
    $services->set(Notify_Payment_Request_Handler::class)->args([service('sylius.provider.payment_request'), service('sylius_abstraction.state_machine')])->tag('messenger.message_handler', ['bus' => 'sylius.payment_request.command_bus']);
    $services->set('sylius.behat.context.guest_cart', Guest_Cart_Context::class)->args([service('sylius.repository.order'), '%sylius.behat.guest_cart_token_file%'])->tag('sylius.context.cart', ['priority' => -900]);
};
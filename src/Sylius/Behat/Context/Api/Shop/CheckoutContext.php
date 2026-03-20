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
namespace Sylius\Behat\Context\Api\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Context\Api\Shop\Checkout\Checkout_Shipping_Context;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Factory\Address_Factory_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Address_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Model\Promotion_Interface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\Core\Order_Checkout_States;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Sylius\Component\Product\Resolver\Product_Variant_Resolver_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Symfony\Component\Http_Foundation\Request as HTTPRequest;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final class Checkout_Context implements Context
{
    public const CHECKOUT_STATE_TYPES = ['address' => Order_Checkout_States::STATE_ADDRESSED, 'shipping method' => Order_Checkout_States::STATE_SHIPPING_SELECTED, 'payment' => Order_Checkout_States::STATE_PAYMENT_SELECTED];
    /** @var string[] */
    private array $content = [];
    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     * @param RepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     */
    public function __construct(private readonly Api_Client_Interface $client, private readonly Response_Checker_Interface $response_checker, private readonly Checkout_Shipping_Context $checkout_shipping_context, private readonly Order_Repository_Interface $order_repository, private readonly Repository_Interface $payment_method_repository, private readonly Product_Variant_Resolver_Interface $product_variant_resolver, private readonly Iri_Converter_Interface $iri_converter, private readonly Shared_Storage_Interface $shared_storage, private readonly Request_Factory_Interface $request_factory, private readonly Address_Factory_Interface $address_factory, private readonly string $shipping_method_class, private readonly string $payment_method_class)
    {
    }
    #[Given('/^(my) billing address is fulfilled automatically through default address$/')]
    public function my_billing_address_is_fulfilled_automatically_through_default_address(Shop_User_Interface $user): void
    {
        /** @var CustomerInterface|null $customer */
        $customer = $user->get_customer();
        Assert::not_null($customer);
        $default_address = $customer->get_default_address();
        Assert::not_null($default_address);
        $this->i_specify_the_billing_address_as($default_address);
    }
    #[When('I try to complete the shipping step')]
    public function i_try_to_complete_the_shipping_step(): void
    {
        $response = $this->client->request_get(sprintf('orders/%s', $this->shared_storage->get('cart_token')));
        $content = $this->response_checker->get_response_content($response);
        $this->client->request_patch(uri: sprintf('orders/%s/shipments/%s', $this->shared_storage->get('cart_token'), $content['shipments'][0]['id']), body: ['shippingMethod' => $content['shipments'][0]['method']]);
    }
    #[When('the customer is at the checkout payment step')]
    public function the_customer_is_at_the_checkout_payment_step(): void
    {
        // Intentionally left blank, as this is a UI-specific action.
    }
    #[When('I try to complete the shipping step with :shippingMethod shipping method')]
    public function i_try_to_complete_the_shipping_step_with_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        $response = $this->client->request_get(sprintf('orders/%s', $this->shared_storage->get('cart_token')));
        $content = $this->response_checker->get_response_content($response);
        $this->shared_storage->set('shipping_method', $shipping_method);
        $this->client->request_patch(uri: sprintf('orders/%s/shipments/%s', $this->shared_storage->get('cart_token'), $content['shipments'][0]['id']), body: ['shippingMethod' => $this->iri_converter->get_iri_from_resource($shipping_method)]);
    }
    #[When('I specified the billing address')]
    public function i_specified_the_billing_address(): void
    {
        $this->address_order($this->get_array_with_default_address());
    }
    #[When('I proceed with :shippingMethod shipping method and :paymentMethod payment')]
    #[When('I have proceeded order with :shippingMethod shipping method and :paymentMethod payment')]
    public function i_proceed_order_with_shipping_method_and_payment(Shipping_Method_Interface $shipping_method, Payment_Method_Interface $payment_method): void
    {
        $this->checkout_shipping_context->choose_shipping_method($shipping_method);
        $this->i_choose_payment_method($payment_method);
    }
    #[Given('I am at the checkout addressing step')]
    #[When('I complete the payment step')]
    #[When('I complete the shipping step')]
    #[When('I go to the checkout addressing step')]
    #[Then('there should be information about no available shipping methods')]
    #[Then('I should be informed that my order cannot be shipped to this address')]
    #[Then('I should not be able to address an order with an empty cart')]
    public function i_am_at_the_checkout_addressing_step(): void
    {
        // Intentionally left blank
    }
    #[Then('I should see that there is no shipment assigned')]
    public function i_should_see_that_there_is_no_shipment_assigned(): void
    {
        $response = $this->client->request_get(sprintf('orders/%s', $this->shared_storage->get('cart_token')));
        Assert::is_empty($this->response_checker->get_value($response, 'shipments'));
    }
    #[Then('I should see that no payment method is assigned')]
    public function i_should_see_that_no_payment_method_is_assigned(): void
    {
        $response = $this->client->request_get(sprintf('orders/%s', $this->shared_storage->get('cart_token')));
        Assert::is_empty($this->response_checker->get_value($response, 'payments'));
    }
    #[Then('there should not be any payment methods available for selection')]
    public function there_should_not_be_any_payment_methods_available_for_selection(): void
    {
        $response = $this->client->request_get('payment-methods');
        Assert::is_empty($this->response_checker->get_collection($response));
    }
    #[When('/^I choose "([^"]+)" street for (billing|shipping) address$/')]
    public function i_choose_for_billing_address(string $street, string $address_type): void
    {
        $address_book = $this->response_checker->get_collection($this->client->index(Resources::ADDRESSES));
        $address_type .= 'Address';
        $address = $this->get_address_by_field_value($address_book, 'street', $street);
        if ($address_type === 'shippingAddress') {
            $this->content['billingAddress'] = $address;
        }
        $this->content[$address_type] = $address;
        $this->address_order($this->content);
    }
    #[Given('/^the (?:customer|visitor) has specified the email as "([^"]+)"$/')]
    #[When('I specify the email as :email')]
    #[When('/^the (?:customer|visitor) specify the email as "([^"]+)"$/')]
    public function i_specify_the_email_as(?string $email): void
    {
        $this->content['email'] = $email;
    }
    #[Given('the customer specify the billing address')]
    #[Given('the visitor specify the billing address')]
    #[Given('/^the (?:visitor|customer) has specified (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[When('/^I specify(?: the| different) billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[When('/^the visitor changes the billing (address to "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[When('/^the (?:customer|visitor) specify the billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[When('/^I specify the billing (address for "([^"]+)" from "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)")$/')]
    public function i_specify_the_billing_address_as(?Address_Interface $address = null): void
    {
        $this->fill_address('billingAddress', $address ?? $this->address_factory->create_default());
    }
    #[When('/^the visitor try to specify the incorrect billing address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)"$/')]
    public function i_try_to_specify_the_incorrect_billing_address_as(string $city, string $street, string $postcode, string $country_name, string $customer_name): void
    {
        $address_type = 'billingAddress';
        $this->add_address($address_type, $city, $street, $postcode, $customer_name, $country_name);
    }
    #[When('/^the visitor try to specify the billing address without country as "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)"$/')]
    public function i_try_to_specify_the_billing_address_without_country_as(string $city, string $street, string $postcode, string $customer_name): void
    {
        $this->add_address('billingAddress', $city, $street, $postcode, $customer_name);
    }
    #[When('/^I specify the(?:| required) shipping (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[When('/^I specify the shipping (address for "([^"]+)" from "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)")$/')]
    public function i_specify_the_shipping_address_as(Address_Interface $address): void
    {
        $this->fill_address('shippingAddress', $address);
    }
    #[When('/^I (do not specify any shipping address) information$/')]
    public function i_do_not_specify_any_shipping_address_information(Address_Interface $address): void
    {
        $this->fill_address('billingAddress', $address);
        $this->fill_address('shippingAddress', $address);
    }
    #[When('/^I (do not specify any billing address) information$/')]
    public function i_do_not_specify_any_billing_address_information(Address_Interface $address): void
    {
        $this->fill_address('billingAddress', $address);
    }
    #[When('/^I specified the billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[When('/^I define the billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    #[When('/^I try to change the billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    public function i_specified_the_billing_address_as(Address_Interface $address): void
    {
        $this->fill_address('billingAddress', $address);
        $this->address_order($this->content);
        $this->content = [];
    }
    #[When('/^I specify (billing|shipping) country (province as "[^"]+")$/')]
    public function i_specify_country_province_as(string $address_type, Province_Interface $province): void
    {
        $this->content[$address_type . 'Address']['provinceCode'] = $province->get_code();
    }
    #[When('/^I specify the province name manually as "([^"]+)" for (billing|shipping) address$/')]
    public function i_specify_the_province_name_manually_as_for_address(string $province_name, string $address_type): void
    {
        $this->content[$address_type . 'Address']['provinceName'] = $province_name;
    }
    #[When('I specify the first and last name as :fullName for billing address')]
    public function i_specify_the_first_and_last_name_as_for_billing_address(string $full_name): void
    {
        $names = explode(' ', $full_name);
        $this->content['billingAddress']['firstName'] = $names[0];
        $this->content['billingAddress']['lastName'] = $names[1];
    }
    #[Given('/^I have completed addressing step with email "([^"]+)" and ("[^"]+" based billing address)$/')]
    #[When('/^I complete addressing step with email "([^"]+)" and ("[^"]+" based billing address)$/')]
    public function i_complete_addressing_step_with_email(string $email, Address_Interface $address): void
    {
        $this->address_order(['email' => $email, 'billingAddress' => ['city' => $address->get_city(), 'street' => $address->get_street(), 'postcode' => $address->get_postcode(), 'countryCode' => $address->get_country_code(), 'firstName' => $address->get_first_name(), 'lastName' => $address->get_last_name()]]);
    }
    #[When('/^I complete addressing step with ("[^"]+" based billing address)$/')]
    public function i_complete_addressing_step_with_address(Address_Interface $address): void
    {
        $this->address_order(['billingAddress' => ['city' => $address->get_city(), 'street' => $address->get_street(), 'postcode' => $address->get_postcode(), 'countryCode' => $address->get_country_code(), 'firstName' => $address->get_first_name(), 'lastName' => $address->get_last_name()]]);
    }
    #[Given('/^the (?:customer|visitor) has completed the addressing step$/')]
    #[When('I complete the addressing step')]
    #[When('I try to complete the addressing step')]
    #[When('/^the (?:customer|visitor) completes the addressing step$/')]
    #[When('the visitor try to complete the addressing step in the customer cart')]
    public function i_complete_the_addressing_step(): void
    {
        $this->address_order($this->content);
        $this->content = [];
    }
    #[When('I proceed as guest :email with :country as billing country')]
    public function i_proceed_logging_as_guest_with_as_billing_country(string $email, Country_Interface $country): void
    {
        $this->address_order_with_country_and_email($country, $email);
    }
    #[When('I proceed with selecting :country as billing country')]
    public function i_proceed_with_selecting_country_as_billing_country(Country_Interface $country): void
    {
        $this->address_order_with_country_and_email($country);
    }
    #[When('I provide additional note like :notes')]
    public function i_provide_additional_notes_like(string $notes): void
    {
        $this->content['additionalNote'] = $notes;
        $this->shared_storage->set('additional_note', $notes);
    }
    #[When('I want to complete checkout')]
    public function i_want_to_complete_checkout(): void
    {
        $response = $this->complete_order();
        Assert::not_same($response->get_status_code(), 200);
        $this->client->show(Resources::ORDERS, $this->shared_storage->get('cart_token'));
    }
    #[When('I confirm my order')]
    #[Given('I confirmed my order')]
    #[Given('the customer confirmed the order')]
    #[When('/^the (?:visitor|customer) confirm his order$/')]
    public function i_confirm_my_order(): void
    {
        $response = $this->complete_order();
        if ($response->get_status_code() > 299) {
            return;
        }
        $this->shared_storage->set('response', $response);
        $this->shared_storage->set('order_number', $this->response_checker->get_value($response, 'number'));
        $this->shared_storage->set('order', $this->order_repository->find_one_by_number($this->shared_storage->get('order_number')));
    }
    #[When('I try to confirm my order')]
    public function i_try_to_confirm_my_order(): void
    {
        $response = $this->complete_order();
        $this->shared_storage->set('response', $response);
    }
    #[When('I decide to change shipping method')]
    #[When('I go back to addressing step of the checkout')]
    public function i_decide_to_change_shipping_method(): void
    {
        // This step is relevant only for the UI
    }
    #[When('I try to select :shippingMethodCode shipping method')]
    public function i_try_to_select_shipping_method(string $shipping_method_code): void
    {
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $this->shared_storage->get('cart_token'), Http_Request::METHOD_PATCH, sprintf('shipments/%s', $this->get_cart()['shipments'][0]['id']));
        $request->set_content(['shippingMethod' => $this->iri_converter->get_iri_from_resource(resource: $this->shipping_method_class, context: ['uri_variables' => ['code' => $shipping_method_code]])]);
        $this->client->execute_custom_request($request);
    }
    #[When('I try to select :paymentMethodCode payment method')]
    public function i_try_to_select_payment_method(string $payment_method_code): void
    {
        $cart = $this->get_cart();
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $this->shared_storage->get('cart_token'), Http_Request::METHOD_PATCH, sprintf('payments/%s', $cart['payments'][0]['id']));
        $request->set_content(['paymentMethod' => $this->iri_converter->get_iri_from_resource(resource: $this->payment_method_class, context: ['uri_variables' => ['code' => $payment_method_code]])]);
        $this->client->execute_custom_request($request);
    }
    #[When('I decide to change my address')]
    #[When('I go to the addressing step')]
    public function i_decide_to_change_my_address(): void
    {
        // This step is relevant only for the UI
    }
    #[Then('I should be notified that the order should be addressed first')]
    public function i_should_be_notified_that_the_order_should_be_addressed_first(): void
    {
        Assert::true($this->response_checker->is_violation_with_message_in_response($this->client->get_last_response(), 'Order should be addressed first.'));
    }
    #[Then('the visitor has no access to proceed with :shippingMethod shipping method in the customer cart')]
    #[Then('the visitor has no access to proceed with :paymentMethod payment in the customer cart')]
    #[Then('the visitor has no access to confirm the customer order')]
    #[Then('the visitor has no access to change product :product quantity to :quantity in the customer cart')]
    public function the_visitor_has_no_proceed_with_shipping_method_in_the_customer_cart(): void
    {
        $response = $this->client->get_last_response();
        Assert::same($response->get_status_code(), 404, 'Resource should be inaccessible.');
        Assert::same($this->response_checker->get_response_content($response)['hydra:description'], 'Not Found');
    }
    #[When('the visitor proceeds with :paymentMethod payment method')]
    #[When('the customer proceeds with :paymentMethod payment method')]
    #[Given('I completed the payment step with :paymentMethod payment method')]
    #[Given('/^the (?:customer|visitor) has proceeded ("[^"]+" payment)$/')]
    #[When('I choose :paymentMethod payment method')]
    #[When('I select :paymentMethod payment method')]
    #[When('/^the (?:customer|visitor) proceed with ("[^"]+" payment)$/')]
    #[When('I try to change payment method to :paymentMethod payment')]
    #[When('I change payment method to :paymentMethod after checkout')]
    #[When('I retry the payment with :paymentMethod payment method')]
    public function i_choose_payment_method(Payment_Method_Interface $payment_method): void
    {
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $this->shared_storage->get('cart_token'), Http_Request::METHOD_PATCH, \sprintf('payments/%s', $this->get_cart()['payments'][0]['id']));
        $request->set_content(['paymentMethod' => $this->iri_converter->get_iri_from_resource($payment_method)]);
        $this->client->execute_custom_request($request);
    }
    #[When('I proceed through checkout process')]
    public function i_proceed_through_checkout_process(): void
    {
        $this->address_order($this->get_array_with_default_address());
        $this->checkout_shipping_context->choose_shipping_method();
        /** @var PaymentMethodInterface $paymentMethod */
        $payment_method = $this->payment_method_repository->find_one_by([]);
        $this->i_choose_payment_method($payment_method);
    }
    #[When('I proceed with selecting :paymentMethod payment method')]
    #[When('I have proceeded selecting :paymentMethod payment method')]
    public function i_have_proceeded_selecting_payment_method(Payment_Method_Interface $payment_method): void
    {
        $this->address_order($this->get_array_with_default_address());
        $this->checkout_shipping_context->choose_shipping_method();
        $this->i_choose_payment_method($payment_method);
    }
    #[Then('/^(address "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+") should be filled as (billing) address$/')]
    #[Then('/^the visitor should has ("[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+" specified as) (billing) address$/')]
    public function address_should_be_filled_as_billing_address(Address_Interface $address, string $address_type): void
    {
        $this->address_should_be_filled_as($address, $address_type);
    }
    #[Then('/^(address "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+") should be filled as (shipping) address$/')]
    #[Then('/^the visitor should has ("[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+" specified as) (shipping) address$/')]
    public function address_should_be_filled_as_shipping_address(Address_Interface $address, string $address_type): void
    {
        $this->address_should_be_filled_as($address, $address_type);
    }
    #[Then('I should be on the checkout complete step')]
    #[Then('I should be on the checkout summary step')]
    public function i_should_be_on_the_checkout_complete_step(): void
    {
        Assert::in_array($this->get_checkout_state(), [Order_Checkout_States::STATE_PAYMENT_SKIPPED, Order_Checkout_States::STATE_PAYMENT_SELECTED]);
    }
    #[Then('I should be informed with :paymentMethod payment method instructions')]
    public function i_should_be_informed_with_payment_method_instructions(Payment_Method_Interface $payment_method): void
    {
        $response = $this->client->get_last_response();
        $payments = $this->response_checker->get_value($response, 'payments');
        Assert::not_empty($payments, 'No payments found in response.');
        $payment_method_iri = $this->iri_converter->get_iri_from_resource($payment_method);
        foreach ($payments as $payment) {
            if ($payment['method'] !== $payment_method_iri) {
                continue;
            }
            $custom_request = $this->request_factory->custom($payment['method'], Http_Request::METHOD_GET);
            $payment_method_response = $this->client->execute_custom_request($custom_request);
            Assert::same($this->response_checker->get_value($payment_method_response, 'instructions'), $payment_method->get_instructions(), sprintf('Payment method instructions should be equal to %s', $payment_method->get_instructions()));
            return;
        }
        throw new \Exception(sprintf('Payment method %s not found in response.', $payment_method->get_name()));
    }
    #[Then('I should not be able to confirm order because products do not fit :shippingMethod requirements')]
    public function i_should_not_be_able_to_confirm_order_because_do_not_belongs_to_shipping_category(Shipping_Method_Interface $shipping_method): void
    {
        $response = $this->client->get_last_response();
        Assert::same($response->get_status_code(), 422);
        Assert::true($this->response_checker->is_violation_with_message_in_response($response, sprintf('Product does not fit requirements for %s shipping method. Please reselect your shipping method.', $shipping_method->get_name())));
    }
    #[Then('I should not be able to select :paymentMethod payment method')]
    public function i_should_not_be_able_to_select_payment_method(Payment_Method_Interface $payment_method): void
    {
        $this->i_choose_payment_method($payment_method);
        Assert::true($this->response_checker->has_violation_with_message($this->client->get_last_response(), sprintf('The payment method %s is not available for this order. Please choose another one.', $payment_method->get_name())));
    }
    #[Then('I should be informed that payment method with code :code does not exist')]
    public function i_should_be_informed_that_payment_method_with_code_does_not_exist(string $code): void
    {
        Assert::true($this->response_checker->has_violation_with_message($this->client->get_last_response(), sprintf('The payment method with %s code does not exist.', $code)));
    }
    #[Then('I should be able to select :paymentMethodName payment method')]
    public function i_should_be_able_to_select_payment_method(string $payment_method_name): void
    {
        $payment_methods = $this->get_possible_payment_methods();
        Assert::not_false(array_search($payment_method_name, array_column($payment_methods, 'name'), true));
    }
    #[Then('I should see :firstPaymentMethodName and :secondPaymentMethodName payment methods')]
    public function i_should_see_payment_methods(string ...$payment_methods_names): void
    {
        $payment_methods = $this->get_possible_payment_methods();
        foreach ($payment_methods_names as $payment_method_name) {
            Assert::in_array($payment_method_name, array_column($payment_methods, 'name'));
        }
    }
    #[Then('I should not see :firstPaymentMethodName and :secondPaymentMethodName payment methods')]
    public function i_should_not_see_payment_methods(string ...$payment_methods_names): void
    {
        $payment_methods = $this->get_possible_payment_methods();
        foreach ($payment_methods_names as $payment_method_name) {
            Assert::false(in_array($payment_method_name, array_column($payment_methods, 'name'), true));
        }
    }
    #[Then('I should have :paymentMethodName payment method available as the :choice choice')]
    public function i_should_have_payment_method_available_as_the_choice(string $payment_method_name, string $choice): void
    {
        $payment_methods = $this->get_possible_payment_methods();
        Assert::not_empty($payment_methods);
        if ($choice === 'first') {
            Assert::same(reset($payment_methods)['name'], $payment_method_name);
        }
        if ($choice === 'last') {
            Assert::same(end($payment_methods)['name'], $payment_method_name);
        }
    }
    #[Then('I should still be on the checkout addressing step')]
    public function i_should_still_be_on_the_checkout_addressing_step(): void
    {
        Assert::same($this->get_cart()['checkoutState'], Order_Checkout_States::STATE_CART);
    }
    #[Then('I should be on the checkout payment step')]
    public function i_should_be_on_the_checkout_payment_step(): void
    {
        Assert::in_array($this->get_checkout_state(), [Order_Checkout_States::STATE_SHIPPING_SELECTED, Order_Checkout_States::STATE_SHIPPING_SKIPPED]);
    }
    #[Then('I should not see any information about payment method')]
    public function i_should_not_see_any_information_about_payment_method(): void
    {
        $response = $this->client->get_last_response();
        Assert::true(empty($this->response_checker->get_response_content($response)['payments']));
    }
    #[Then('I should see :shippingMethod shipping method')]
    public function i_should_see_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        Assert::true($this->has_shipping_method($shipping_method));
    }
    #[Then('/^I should see (shipping method "[^"]+") with fee ("[^"]+")/')]
    public function i_should_see_shipping_fee(Shipping_Method_Interface $shipping_method, int $fee): void
    {
        Assert::true($this->has_shipping_method_with_fee($shipping_method, $fee));
    }
    #[Then('my order\'s payment method should be :paymentMethod')]
    public function my_orders_payment_method_should_be(Payment_Method_Interface $payment_method): void
    {
        $payment_methods = $this->get_possible_payment_methods();
        $payment_method_name = $payment_method->get_name();
        foreach ($payment_methods as $method) {
            if ($method['name'] === $payment_method_name) {
                return;
            }
        }
        throw new \InvalidArgumentException('Couldn\'t find given payment method for this order');
    }
    #[Then('my order\'s shipping method should be :shippingMethod')]
    public function my_orders_shipping_method_should_be(Shipping_Method_Interface $shipping_method): void
    {
        $shipping_methods = $this->get_cart_shipping_methods($this->get_cart());
        $shipping_method_name = $shipping_method->get_name();
        foreach ($shipping_methods as $method) {
            if ($method['name'] === $shipping_method_name) {
                return;
            }
        }
        throw new \InvalidArgumentException('Couldn\'t find given shipping method for this order');
    }
    #[Then('I should be on the checkout shipping step')]
    public function i_should_be_on_the_checkout_shipping_step(): void
    {
        Assert::same($this->get_checkout_state(), Order_Checkout_States::STATE_ADDRESSED);
    }
    #[Then('I should not be able to proceed checkout shipping step')]
    public function i_should_not_be_able_to_proceed_checkout_shipping_step(): void
    {
        Assert::same($this->get_checkout_state(), Order_Checkout_States::STATE_ADDRESSED);
        Assert::is_empty($this->get_cart()['shipments']);
    }
    #[Then('I should not be able to proceed checkout payment step')]
    public function i_should_not_be_able_to_proceed_checkout_payment_step(): void
    {
        $this->i_should_be_on_the_checkout_payment_step();
        Assert::is_empty($this->get_cart()['payments']);
    }
    #[Then('I should not be able to proceed checkout complete step')]
    public function i_should_not_be_able_to_proceed_checkout_complete_step(): void
    {
        $this->i_should_be_on_the_checkout_complete_step();
        $this->i_confirm_my_order();
        $response = $this->client->get_last_response();
        Assert::same($this->response_checker->get_error($response), 'An empty order cannot be processed.');
    }
    #[Then('/^the (?:visitor|customer) should have checkout (address|shipping method|payment) step completed$/')]
    public function the_visitor_should_have_checkout_address_step_completed(string $step_type): void
    {
        Assert::same($this->get_checkout_state(), $this::CHECKOUT_STATE_TYPES[$step_type]);
    }
    #[Then('I should be notified that :countryName country does not exist')]
    #[Then('they should be notified that :countryName country does not exist')]
    public function i_should_be_notified_that_country_does_not_exist(string $country_name): void
    {
        $this->response_checker->has_violation_with_message($this->client->get_last_response(), sprintf('The country %s does not exist.', String_Inflector::name_to_lowercase_code($country_name)));
    }
    #[Then('I should be notified that address without country cannot exist')]
    #[Then('they should be notified that address without country cannot exist')]
    public function i_should_be_notified_that_address_without_country_cannot_exist(): void
    {
        $this->response_checker->has_violation_with_message($this->client->get_last_response(), 'The address without country cannot exist');
    }
    #[Then('they should be notified that they cannot address an empty cart')]
    public function they_should_be_notified_that_they_cannot_address_an_empty_cart(): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->is_update_successful($response));
        $this->response_checker->has_violation_with_message($response, 'The empty cart cannot be addressed');
    }
    #[Then('my order should be completed successfully')]
    #[Then('I should see the thank you page')]
    #[Then('/^the (?:visitor|customer) should see the thank you page$/')]
    public function i_should_see_the_thank_you_page(): void
    {
        Assert::same($this->get_checkout_state(), Order_Checkout_States::STATE_COMPLETED);
    }
    #[Then('I should see selected :shippingMethod shipping method')]
    public function i_should_see_selected_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        Assert::true($this->has_shipping_method($shipping_method));
    }
    #[Then('I should not see :shippingMethod shipping method')]
    public function i_should_not_see_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        Assert::false($this->has_shipping_method($shipping_method));
    }
    #[Then('I should have :shippingMethod shipping method available as the first choice')]
    public function i_should_have_shipping_method_available_as_first_choice(Shipping_Method_Interface $shipping_method): void
    {
        $shipping_methods = $this->get_cart_shipping_methods($this->get_cart());
        Assert::true($shipping_methods[0]['code'] === $shipping_method->get_code());
    }
    #[Then('I should have :shippingMethod shipping method available as the last choice')]
    public function i_should_have_shipping_method_available_as_last_choice(Shipping_Method_Interface $shipping_method): void
    {
        $shipping_methods = $this->get_cart_shipping_methods($this->get_cart());
        Assert::true(end($shipping_methods)['code'] === $shipping_method->get_code());
    }
    #[Then('/^my order total should be ("[^"]+")$/')]
    public function my_order_total_should_be(int $total): void
    {
        Assert::same($total, (int) $this->get_cart()['total']);
    }
    #[Then('/^my order promotion total should be ("[^"]+")$/')]
    public function my_order_promotion_total_should_be(int $promotion_total): void
    {
        $response_promotion_total = $this->response_checker->get_value($this->client->get_last_response(), 'orderPromotionTotal');
        Assert::same($promotion_total, $response_promotion_total);
    }
    #[Then('/^my tax total should be ("[^"]+")$/')]
    public function my_tax_total_should_be(int $tax_total): void
    {
        $response_tax_total = $this->response_checker->get_value($this->client->get_last_response(), 'taxTotal');
        Assert::same($tax_total, $response_tax_total);
    }
    #[Then('I should have :quantity :productName products in the cart')]
    public function i_should_have_products_in_the_cart(int $quantity, string $product_name): void
    {
        Assert::true($this->has_product_with_name_and_quantity_in_cart($product_name, $quantity), sprintf('There is no product %s with quantity %d.', $product_name, $quantity));
    }
    #[Then('/^my discount should be ("[^"]+")$/')]
    #[Then('there should be no discount applied')]
    public function my_discount_should_be(int $discount = 0): void
    {
        if ($this->shared_storage->has('cart_token')) {
            $discount_total = $this->response_checker->get_value($this->client->show(Resources::ORDERS, $this->shared_storage->get('cart_token')), 'orderPromotionTotal');
            Assert::same($discount, (int) $discount_total);
            return;
        }
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'orderPromotionTotal'), $discount);
    }
    #[Then('there should be no taxes charged')]
    public function there_should_be_no_taxes_charged(): void
    {
        $this->client->show(Resources::ORDERS, $this->shared_storage->get('cart_token'));
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'taxTotal'), 0);
    }
    #[Then('my order\'s locale should be :localeCode')]
    public function my_order_locale_should_be(string $locale_code): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'localeCode'), $locale_code);
    }
    #[Then('/^my order shipping should be ("(?:\£|\$)\d+(?:\.\d+)?")$/')]
    public function my_order_shipping_should_be(int $price): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'shippingTotal'), $price);
    }
    #[Then('I should not see shipping total')]
    public function i_should_not_see_shipping_total(): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'shippingTotal'), 0);
    }
    #[Then('/^I should(?:| also) be notified that the "([^"]+)" and the "([^"]+)" in (shipping|billing) details are required$/')]
    public function i_should_be_notified_that_the_and_the_in_shipping_details_are_required(string $first_element, string $second_element, string $detail_type): void
    {
        $response = $this->client->get_last_response();
        Assert::true($response->get_status_code() === 422);
        /** @var array|null $violations */
        $violations = $this->response_checker->get_response_content($response)['violations'];
        $detail_type .= 'Address';
        foreach ([$first_element, $second_element] as $element) {
            $violation = $this->get_violation($violations, $detail_type . '.' . String_Inflector::name_to_camel_case($element));
            Assert::same($violation['message'], sprintf('Please enter %s.', $element));
        }
    }
    #[Then('/^I should(?:| also) be notified that the "([^"]+)" in (shipping|billing) details is required$/')]
    public function i_should_be_notified_that_the_in_shipping_details_is_required(string $element, string $type): void
    {
        /** @var array|null $violations */
        $violations = $this->response_checker->get_response_content($this->client->get_last_response())['violations'];
        $type .= 'Address';
        $violation = $this->get_violation($violations, $type . '.' . String_Inflector::name_to_camel_case($element));
        Assert::same($violation['message'], sprintf('Please enter %s.', $element));
    }
    #[Then('/^I should be informed that (this product) has been disabled$/')]
    #[Then('/^I should be informed that (product "[^"]+") is disabled$/')]
    public function i_should_be_informed_that_this_product_has_been_disabled(Product_Interface $product): void
    {
        Assert::true($this->response_checker->is_violation_with_message_in_response($this->client->get_last_response(), sprintf('The product %s is no longer available.', $product->get_name())));
    }
    #[Then('/^I should be informed that (product "[^"]+") does not exist$/')]
    public function i_should_be_informed_that_this_product_does_not_exist(Product_Interface $product): void
    {
        Assert::true($this->response_checker->is_violation_with_message_in_response($this->client->get_last_response(), sprintf('The product %s does not exist.', $product->get_name())));
    }
    #[Then('/^I should be informed that ("([^"]*)" product variant) does not exist$/')]
    public function i_should_be_informed_that_product_variant_does_not_exist(Product_Variant_Interface $product_variant): void
    {
        Assert::true($this->response_checker->is_violation_with_message_in_response($this->client->get_last_response(), sprintf('The product variant %s does not exist.', $product_variant->get_code())));
    }
    #[Then('I should be informed that product variant with code :code does not exist')]
    public function i_should_be_informed_that_product_variant_with_code_does_not_exist(string $code): void
    {
        Assert::true($this->response_checker->is_violation_with_message_in_response($this->client->get_last_response(), sprintf('The product variant %s does not exist.', $code)));
    }
    #[Then('I should not see the thank you page')]
    public function i_should_not_see_the_thank_you_page(): void
    {
        Assert::same($this->client->get_last_response()->get_status_code(), 422);
    }
    #[Then('address to :fullName should be used for both :addressType1 and :addressType2 of my order')]
    #[Then('my order\'s :addressType address should be to :fullName')]
    public function i_should_see_this_shipping_address_as_shipping_and_billing_address(string $full_name, string ...$address_types): void
    {
        foreach ($address_types as $address_type) {
            $this->has_full_name_in_address($full_name, $address_type);
        }
    }
    #[Then('I should see :provinceName in the :addressType address')]
    public function i_should_see_in_the_billing_address(string $province_name, string $address_type): void
    {
        $this->has_province_name_in_address($province_name, $address_type);
    }
    #[Then('/^I should be informed that (this payment method) has been disabled$/')]
    public function i_should_be_informed_that_this_payment_method_has_been_disabled(Payment_Method_Interface $payment_method): void
    {
        $response = $this->client->get_last_response();
        Assert::same($response->get_status_code(), 422);
        Assert::true($this->response_checker->is_violation_with_message_in_response($response, sprintf('This payment method %s has been disabled. Please reselect your payment method.', $payment_method->get_name())));
    }
    #[When('/^I try to add (product "[^"]+") to the cart$/')]
    public function i_try_to_add_product_to_cart(Product_Interface $product): void
    {
        $this->put_product_to_cart($product, $this->shared_storage->get('cart_token'));
    }
    #[When('/^I try to add ("([^"]+)" product variant)$/')]
    #[When('/^I try to add ("([^"]+)" variant of product "([^"]+)")$/')]
    public function i_try_to_add_product_variant(Product_Variant_Interface $product_variant): void
    {
        $token_value = $this->get_cart_token_value();
        $this->put_variant_to_cart($product_variant, $token_value);
    }
    #[When('/^I try to add (product "[^"]+") with variant code "([^"]+)"$/')]
    public function i_try_to_add_product_variant_with_code(Product_Interface $product, string $code): void
    {
        $token_value = $this->get_cart_token_value();
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $token_value, Http_Request::METHOD_POST, 'items');
        $variant = $product->get_variants()->first();
        $request->set_content(['productVariant' => $this->iri_converter->get_iri_from_resource(resource: $variant::class, context: ['uri_variables' => ['code' => $code]]), 'quantity' => 1]);
        $this->shared_storage->set('response', $this->client->execute_custom_request($request));
    }
    #[When('/^I try to remove (product "[^"]+") from the cart$/')]
    public function i_try_to_remove_product_from_the_cart(Product_Interface $product): void
    {
        $this->remove_order_item_from_cart($product->get_id(), $this->shared_storage->get('cart_token'));
    }
    #[When('/^I try to change quantity to (\d+) of (product "[^"]+") from the (cart)$/')]
    public function i_try_to_change_quantity_to_of_product_from_the_cart(int $quantity, Product_Interface $product, ?string $token_value): void
    {
        $this->put_product_to_cart($product, $token_value, $quantity);
    }
    #[Then('I should be informed that cart is no longer available')]
    public function i_should_be_informed_that_cart_is_no_longer_available(): void
    {
        $response = $this->client->get_last_response();
        Assert::same($response->get_status_code(), 404);
        Assert::same($this->response_checker->get_response_content($response)['hydra:description'], 'Not Found');
    }
    #[Then('/^I should not be able to specify province name manually for (billing address|shipping address)$/')]
    #[Then('/^I should be notified that selected province is invalid for (billing address|shipping address)$/')]
    public function i_should_not_be_able_to_specify_province_name_manually_for_address(string $address_type): void
    {
        $this->client->get_last_response();
        $this->assert_province_message(String_Inflector::name_to_camel_case($address_type));
    }
    #[Then('I should be notified that product :product does not have sufficient stock')]
    public function i_should_be_notified_that_this_product_does_not_have_sufficient_stock(Product_Interface $product): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $this->product_variant_resolver->get_variant($product);
        Assert::true($this->response_checker->has_violation_with_message($this->client->get_last_response(), sprintf('The product variant with %s name does not have sufficient stock.', $variant->get_name())));
    }
    #[Then('/^I should be informed that (this promotion) is no longer applied$/')]
    public function i_should_be_informed_that_my_promotion_is_no_longer_applied(Promotion_Interface $promotion): void
    {
        Assert::contains($this->client->get_last_response()->get_content(), sprintf('Order is no longer eligible for this %s promotion. Your cart was recalculated.', $promotion->get_name()));
    }
    #[Then('I should not be able to confirm order because the :shippingMethodName shipping method is not available')]
    public function i_should_not_be_able_to_confirm_order_because_the_shipping_method_is_not_available(string $shipping_method_name): void
    {
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), sprintf('The "%s" shipping method is not available. Please reselect your shipping method.', $shipping_method_name));
    }
    #[When('/^I should see (product "[^"]+") with unit price ("[^"]+")$/')]
    public function i_should_see_with_unit_price(Product_Interface $product, int $unit_price): void
    {
        Assert::true($this->has_product_with_unit_price($product->get_name(), $unit_price));
    }
    #[Then('I should be checking out as :email')]
    public function i_should_be_checking_out_as(string $email): void
    {
        $cart = $this->get_cart();
        Assert::not_null($cart['customer'], sprintf('Customer with an email "%s" was not expected to be null.', $email));
        Assert::same($cart['customer']['email'], $email);
    }
    #[Then('I should not be able to change email')]
    public function i_should_not_be_able_to_change_email(): void
    {
        $response = $this->client->build_update_request(Resources::ORDERS, $this->get_cart_token_value())->set_request_data(['email' => 'try_to_change@example.com'])->update();
        Assert::same($response->get_status_code(), 422);
        Assert::true($this->response_checker->has_violation_with_message($response, 'Email can be changed only for guest customers. Once the customer logs in and the cart is assigned, the email can\'t be changed.'));
    }
    private function assert_province_message(string $address_type): void
    {
        $response = $this->client->get_last_response();
        Assert::same($response->get_status_code(), 422);
        Assert::true($this->response_checker->has_violation_with_message($response, 'Please select proper province.', sprintf('%s.%s', $address_type, 'provinceCode')));
    }
    private function address_order_with_country_and_email(Country_Interface $country, ?string $email = null): void
    {
        $content = ['billingAddress' => ['city' => 'Madrid', 'street' => 'Av. de Concha Espina', 'postcode' => '28036', 'countryCode' => $country->get_code(), 'firstName' => 'Santiago', 'lastName' => 'Bernabeu']];
        if ($email !== null) {
            $content['email'] = $email;
        }
        $this->address_order($content);
    }
    /** @param array<array-key, mixed> $content */
    private function address_order(array $content): void
    {
        $this->client->build_update_request(Resources::ORDERS, $this->get_cart_token_value())->set_request_data($content)->update();
    }
    private function get_cart(): array
    {
        $cart = $this->client->show(Resources::ORDERS, $this->get_cart_token_value());
        return $this->response_checker->get_response_content($cart);
    }
    private function get_cart_token_value(): ?string
    {
        if ($this->shared_storage->has('cart_token')) {
            return $this->shared_storage->get('cart_token');
        }
        if ($this->shared_storage->has('previous_cart_token')) {
            return $this->shared_storage->get('previous_cart_token');
        }
        return null;
    }
    private function get_checkout_state(): string
    {
        $this->client->show(Resources::ORDERS, $this->shared_storage->get('cart_token'));
        $response = $this->client->get_last_response();
        return $this->response_checker->get_value($response, 'checkoutState');
    }
    private function get_cart_shipping_methods(array $cart): array
    {
        $this->client->custom_action(sprintf('/api/v2/shop/orders/%s/shipments/%s/methods', $cart['tokenValue'], $cart['shipments'][0]['id']), Http_Request::METHOD_GET);
        return $this->response_checker->get_collection($this->client->get_last_response());
    }
    private function has_shipping_method(Shipping_Method_Interface $shipping_method): bool
    {
        foreach ($this->get_cart_shipping_methods($this->get_cart()) as $cart_shipping_method) {
            if ($cart_shipping_method['code'] === $shipping_method->get_code()) {
                return true;
            }
        }
        return false;
    }
    private function has_shipping_method_with_fee(Shipping_Method_Interface $shipping_method, int $fee): bool
    {
        foreach ($this->get_cart_shipping_methods($this->get_cart()) as $cart_shipping_method) {
            if ($cart_shipping_method['price'] === $fee && $cart_shipping_method['code'] === $shipping_method->get_code()) {
                return true;
            }
        }
        return false;
    }
    private function get_possible_payment_methods(): array
    {
        /** @var OrderInterface|null $order */
        $order = $this->order_repository->find_cart_by_token_value($this->shared_storage->get('cart_token'));
        Assert::not_null($order);
        if (!$order->get_last_payment()) {
            return [];
        }
        $this->client->index(Resources::PAYMENT_METHODS);
        $this->client->add_filter('paymentId', $order->get_last_payment()->get_id());
        $this->client->add_filter('tokenValue', $order->get_token_value());
        $this->client->filter();
        return $this->response_checker->get_collection($this->client->get_last_response());
    }
    private function has_product_with_name_and_quantity_in_cart(string $product_name, int $quantity): bool
    {
        /** @var array $items */
        $items = $this->response_checker->get_value($this->client->get_last_response(), 'items');
        foreach ($items as $item) {
            if ($item['productName'] === $product_name && $item['quantity'] === $quantity) {
                return true;
            }
        }
        return false;
    }
    private function has_product_with_unit_price(string $product_name, int $unit_price): bool
    {
        /** @var array $items */
        $items = $this->response_checker->get_value($this->client->get_last_response(), 'items');
        foreach ($items as $item) {
            if ($item['productName'] === $product_name && $item['unitPrice'] === $unit_price) {
                return true;
            }
        }
        return false;
    }
    private function fill_address(string $address_type, Address_Interface $address): void
    {
        $this->content[$address_type]['city'] = $address->get_city() ?? '';
        $this->content[$address_type]['street'] = $address->get_street() ?? '';
        $this->content[$address_type]['postcode'] = $address->get_postcode() ?? '';
        $this->content[$address_type]['countryCode'] = $address->get_country_code() ?? '';
        $this->content[$address_type]['firstName'] = $address->get_first_name() ?? '';
        $this->content[$address_type]['lastName'] = $address->get_last_name() ?? '';
        $this->content[$address_type]['provinceName'] = $address->get_province_name();
    }
    private function get_array_with_default_address(): array
    {
        try {
            $email = $this->shared_storage->get('created_as_guest') ? 'rich@sylius.com' : null;
        } catch (\InvalidArgumentException) {
            $email = null;
        }
        $content = ['billingAddress' => ['city' => 'New York', 'street' => 'Wall Street', 'postcode' => '00-001', 'countryCode' => 'US', 'firstName' => 'Richy', 'lastName' => 'Rich']];
        if ($email !== null) {
            $content['email'] = $email;
        }
        return $content;
    }
    private function get_violation(array $violations, string $element): array
    {
        return $violations[array_search($element, array_column($violations, 'propertyPath'), true)];
    }
    private function has_full_name_in_address(string $full_name, string $address_type): void
    {
        $response = $this->client->get_last_response();
        $names = explode(' ', $full_name);
        $address_type .= 'Address';
        Assert::same($this->response_checker->get_response_content($response)[$address_type]['firstName'], $names[0]);
        Assert::same($this->response_checker->get_response_content($response)[$address_type]['lastName'], $names[1]);
    }
    private function has_province_name_in_address(string $province_name, string $address_type): void
    {
        $response = $this->client->get_last_response();
        $address_type .= 'Address';
        Assert::same($this->response_checker->get_response_content($response)[$address_type]['provinceName'], $province_name);
    }
    private function put_product_to_cart(Product_Interface $product, ?string $token_value, int $quantity = 1): void
    {
        Assert::not_null($product_variant = $this->product_variant_resolver->get_variant($product));
        Assert::is_instance_of($product_variant, Product_Variant_Interface::class);
        $this->put_variant_to_cart($product_variant, $token_value, $quantity);
    }
    private function put_variant_to_cart(Product_Variant_Interface $product_variant, ?string $token_value, int $quantity = 1): void
    {
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $token_value, Http_Request::METHOD_POST, 'items');
        $request->set_content(['productVariant' => $this->iri_converter->get_iri_from_resource($product_variant), 'quantity' => $quantity]);
        $this->shared_storage->set('response', $this->client->execute_custom_request($request));
    }
    private function remove_order_item_from_cart(int $order_item_id, ?string $token_value): void
    {
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $token_value, Http_Request::METHOD_DELETE, \sprintf('items/%s', $order_item_id));
        $this->shared_storage->set('response', $this->client->execute_custom_request($request));
    }
    private function get_address_by_field_value(array $address_book, string $field_name, string $field_value): array
    {
        foreach ($address_book as $address) {
            if ($address[$field_name] === $field_value) {
                return $address;
            }
        }
        return [];
    }
    /**
     * @param array<string, string> $address
     */
    private function addresses_are_equal(array $address, Address_Interface $address_to_compare): bool
    {
        if ($address['firstName'] === $address_to_compare->get_first_name() && $address['lastName'] === $address_to_compare->get_last_name() && $address['countryCode'] === $address_to_compare->get_country_code() && $address['street'] === $address_to_compare->get_street() && $address['city'] === $address_to_compare->get_city() && $address['postcode'] === $address_to_compare->get_postcode() && ($address_to_compare->get_province_name() !== null && isset($address['provinceName'])) ? $address['provinceName'] === $address_to_compare->get_province_name() : true) {
            return true;
        }
        return false;
    }
    private function address_should_be_filled_as(Address_Interface $address, string $address_type): void
    {
        $response = $this->client->show(Resources::ORDERS, $this->shared_storage->get('cart_token'));
        $address_from_response = $this->response_checker->get_value($response, $address_type . 'Address');
        Assert::true($this->addresses_are_equal($address_from_response, $address));
    }
    private function complete_order(): Response
    {
        $notes = $this->content['additionalNote'] ?? null;
        $request = $this->request_factory->custom_item_action('shop', Resources::ORDERS, $this->shared_storage->get('cart_token'), Http_Request::METHOD_PATCH, 'complete');
        $request->set_content(['notes' => $notes]);
        return $this->client->execute_custom_request($request);
    }
    private function add_address(string $address_type, string $city, string $street, string $postcode, string $customer_name, ?string $country_name = null): void
    {
        [$first_name, $last_name] = explode(' ', $customer_name);
        $this->content[$address_type]['city'] = $city;
        $this->content[$address_type]['street'] = $street;
        $this->content[$address_type]['postcode'] = $postcode;
        $this->content[$address_type]['firstName'] = $first_name;
        $this->content[$address_type]['lastName'] = $last_name;
        $this->content[$address_type]['countryCode'] = $country_name !== null ? String_Inflector::name_to_lowercase_code($country_name) : null;
    }
}
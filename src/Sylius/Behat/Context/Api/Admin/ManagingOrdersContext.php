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
namespace Sylius\Behat\Context\Api\Admin;

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Context\Api\Subresources;
use Sylius\Behat\Service\Security_Service_Interface;
use Sylius\Behat\Service\Shared_Security_Service_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Adjustment_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Core\Model\Shipment_Interface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Sylius\Component\Currency\Model\Currency_Interface;
use Sylius\Component\Order\Order_Transitions;
use Sylius\Component\Payment\Payment_Transitions;
use Sylius\Component\Shipping\Shipment_Transitions;
use Symfony\Component\Http_Foundation\Request as HttpRequest;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Intl\Countries;
use Webmozart\Assert\Assert;
final readonly class Managing_Orders_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Security_Service_Interface $admin_security_service, private Shared_Storage_Interface $shared_storage, private Shared_Security_Service_Interface $shared_security_service)
    {
    }
    #[Given('/^I am viewing the summary of (this order)$/')]
    #[Given('I am viewing the summary of the order :order')]
    #[When('I view the summary of the order :order')]
    #[When('/^I view the summary of the (order placed by "[^"]+")$/')]
    public function i_see_the_order(Order_Interface $order): void
    {
        $response = $this->client->show(Resources::ORDERS, $order->get_token_value());
        Assert::same($this->response_checker->get_value($response, '@id'), $this->iri_converter->get_iri_from_resource_in_section($order, 'admin'));
        $this->shared_storage->set('order', $order);
    }
    #[When('/^I view the summary of the (last order)$/')]
    public function i_view_the_summary_of_the_last_order(Order_Interface $order): void
    {
        $this->client->show(Resources::ORDERS, $order->get_token_value());
    }
    #[Given('I am browsing orders')]
    #[When('I browse orders')]
    public function i_browse_orders(): void
    {
        $this->client->index(Resources::ORDERS);
    }
    #[When('I browse order\'s :order history')]
    public function i_browse_order_history(Order_Interface $order): void
    {
        $this->i_see_the_order($order);
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->client->filter();
    }
    #[When('I choose :channel as a channel filter')]
    public function i_choose_channel_as_a_channel_filter(Channel_Interface $channel): void
    {
        $this->client->add_filter('channel.code', $channel->get_code());
    }
    #[When('I specify filter date from as :dateTime')]
    public function i_specify_filter_date_from_as(string $date_time): void
    {
        $this->client->add_filter('checkoutCompletedAt[after]', $date_time);
    }
    #[When('specify its tracking code as :trackingCode')]
    public function specify_its_tracking_code_as(string $tracking_code): void
    {
        $shipment = $this->shared_storage->get('order')->get_shipments()->first();
        $this->client->build_update_request(Resources::SHIPMENTS, (string) $shipment->get_id());
        $this->client->add_request_data('tracking', $tracking_code);
        $this->client->update();
    }
    #[When('/^I try to view the summary of the (customer\'s latest cart)$/')]
    public function i_try_to_view_the_summary_of_the_customers_latest_cart(Order_Interface $cart): void
    {
        $this->client->show(Resources::ORDERS, $cart->get_token_value());
    }
    #[When('I specify filter date to as :dateTime')]
    public function i_specify_filter_date_to_as(string $date_time): void
    {
        $this->client->add_filter('checkoutCompletedAt[before]', $date_time);
    }
    #[When('I resend the order confirmation email')]
    public function i_resend_the_order_confirmation_email(): void
    {
        $this->client->custom_item_action(Resources::ORDERS, $this->shared_storage->get('order')->get_token_value(), Http_Request::METHOD_POST, 'resend-confirmation-email');
    }
    #[When('I filter by product :productName')]
    #[When('I filter by products :firstProduct and :secondProduct')]
    public function i_filter_by_product(string ...$product_names): void
    {
        foreach ($product_names as $product_name) {
            $this->client->add_filter('items.productName[]', $product_name);
        }
        $this->client->filter();
    }
    #[When('I filter by customer :customer')]
    public function i_filter_by_customer(Customer_Interface $customer): void
    {
        $this->client->add_filter('customer.id', $customer->get_id());
        $this->client->filter();
    }
    #[When('I resend the shipment confirmation email')]
    public function i_resend_the_shipment_confirmation_email(): void
    {
        $this->client->custom_item_action(Resources::SHIPMENTS, (string) $this->shared_storage->get('order')->get_shipments()->last()->get_id(), Http_Request::METHOD_POST, 'resend-confirmation-email');
    }
    #[When('I choose :shippingMethod as a shipping method filter')]
    public function i_choose_as_a_shipping_method_filter(Shipping_Method_Interface $shipping_method): void
    {
        $this->client->add_filter('shipments.method.code', $shipping_method->get_code());
    }
    #[When('I choose :currency as the filter currency')]
    public function i_choose_currency_as_the_filter_currency(Currency_Interface $currency): void
    {
        $this->client->add_filter('currencyCode', $currency->get_code());
    }
    #[When('I specify filter total being greater than :total')]
    public function i_specify_filter_total_being_greater_than(string $total): void
    {
        if (str_contains($total, '.')) {
            $total = str_replace('.', '', $total);
            $this->client->add_filter('total[gt]', $total);
            return;
        }
        $this->client->add_filter('total[gt]', $total . '00');
    }
    #[When('I specify filter total being less than :total')]
    public function i_specify_filter_total_being_less_than(string $total): void
    {
        $this->client->add_filter('total[lt]', $total . '00');
    }
    #[When('I filter by variant :variantName')]
    #[When('I filter by variants :firstVariant and :secondVariant')]
    public function i_filter_by_variant(string ...$variants_names): void
    {
        foreach ($variants_names as $variant_name) {
            $this->client->add_filter('items.variant.translations.name[]', $variant_name);
        }
        $this->client->filter();
    }
    #[When('I switch the way orders are sorted by :fieldName')]
    public function i_switch_sorting_by(string $field_name): void
    {
        $this->client->add_filter('order[number]', 'asc');
        $this->client->filter();
    }
    #[When('/^I cancel (this order)$/')]
    public function i_cancel_this_order(Order_Interface $order): void
    {
        $this->client->apply_transition(Resources::ORDERS, $this->response_checker->get_value($this->client->show(Resources::ORDERS, $order->get_token_value()), 'tokenValue'), Order_Transitions::TRANSITION_CANCEL);
    }
    #[When('/^I mark (this order) as paid$/')]
    public function i_mark_this_order_as_a_paid(Order_Interface $order): void
    {
        $this->client->apply_transition(Resources::PAYMENTS, (string) $order->get_last_payment()->get_id(), Payment_Transitions::TRANSITION_COMPLETE);
    }
    #[When('/^I mark (this order)\'s payment as refunded$/')]
    public function i_mark_this_order_s_payment_as_refunded(Order_Interface $order): void
    {
        $this->client->apply_transition(Resources::PAYMENTS, (string) $order->get_last_payment()->get_id(), Payment_Transitions::TRANSITION_REFUND);
    }
    #[When('/^I ship (this order)$/')]
    public function i_ship_this_order(Order_Interface $order): void
    {
        $shipment = $order->get_shipments()->last();
        Assert::not_null($shipment, 'There is no shipment for this order');
        $this->client->apply_transition(Resources::SHIPMENTS, (string) $shipment->get_id(), Shipment_Transitions::TRANSITION_SHIP);
        $this->shared_storage->set('shipment', $shipment);
    }
    #[When('I limit number of items to :limit')]
    public function i_limit_number_of_items_to(int $limit): void
    {
        $this->client->add_filter('itemsPerPage', $limit);
        $this->client->filter();
    }
    #[When('I check :itemName data')]
    public function i_check_data(string $item_name): void
    {
        /** @var string $lastResponseContent */
        $last_response_content = $this->client->get_last_response()->get_content();
        /** @var array{productName: string}[] $items */
        $items = json_decode($last_response_content, true)['items'];
        foreach ($items as $item) {
            if ($item['productName'] === $item_name) {
                $this->shared_storage->set('item', $item);
                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('There is no item with name "%s".', $item_name));
    }
    #[Then('I should see a single order from customer :customer')]
    public function i_should_see_a_single_order_from_customer(Customer_Interface $customer): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'customer', $this->iri_converter->get_iri_from_resource($customer)), sprintf('There is no order for customer %s', $customer->get_email()));
    }
    #[Then('/^I should be notified that the (order|shipment) confirmation email has been successfully resent to the customer$/')]
    public function i_should_be_notified_that_the_order_confirmation_email_has_been_successfully_resent_to_the_customer(): void
    {
        $this->response_checker->is_creation_successful($this->client->get_last_response());
    }
    #[Then('it should( still) have a :state state')]
    public function it_should_have_state(string $state): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'state', $state));
        Assert::count($this->response_checker->get_collection($this->client->get_last_response()), 1);
    }
    #[Then('I should see a single order in the list')]
    #[Then('I should see :number orders in the list')]
    public function i_should_see_a_single_order_in_the_list(int $number = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $number);
    }
    #[Then('I should be notified that it has been successfully updated')]
    public function i_should_be_notified_about_it_has_been_successfully_canceled(): void
    {
        $response = $this->client->get_last_response();
        Assert::true($this->response_checker->is_update_successful($response), 'Resource could not be completed. Reason: ' . $response->get_content());
    }
    #[Then('this order should have state :state')]
    #[Then('its state should be :state')]
    public function its_state_should_be(string $state): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $order_state = $this->response_checker->get_value($this->client->show(Resources::ORDERS, $order->get_token_value()), 'state');
        Assert::same($order_state, strtolower($state));
    }
    #[Then('/^(it) should have shipment in state "([^"]+)"$/')]
    #[Then('/^(order "[^"]+") should have shipment state "([^"]+)"$/')]
    public function it_should_have_shipment_state(Order_Interface $order, string $state): void
    {
        $shipment_iri = $this->response_checker->get_value($this->client->show(Resources::ORDERS, $order->get_token_value()), 'shipments')[0];
        Assert::true($this->response_checker->has_value($this->client->show_by_iri($shipment_iri['@id']), 'state', strtolower($state)), sprintf('Shipment for this order is not %s', $state));
    }
    #[Then('it should have payment state :state')]
    #[Then('it should have payment with state :paymentState')]
    public function it_should_have_payment_state(string $state): void
    {
        $payment_iri = $this->response_checker->get_value($this->client->show(Resources::ORDERS, $this->shared_storage->get('order')->get_token_value()), 'payments')[0];
        Assert::true($this->response_checker->has_value($this->client->show_by_iri($payment_iri['@id']), 'state', strtolower($state)), sprintf('payment for this order is not %s', $state));
    }
    #[Then('/^(its) payment state should be refunded$/')]
    public function its_payment_state_should_be_refunded(Order_Interface $order): void
    {
        $response = $this->client->show(Resources::ORDERS, $order->get_token_value());
        Assert::same($this->response_checker->get_value($response, 'paymentState'), 'refunded');
    }
    #[Then('/^there should be(?:| only) (\d+) payments?$/')]
    public function the_order_should_have_number_of_payments(int $number): void
    {
        Assert::count($this->response_checker->get_value($this->client->show(Resources::ORDERS, $this->shared_storage->get('order')->get_token_value()), 'payments'), $number);
    }
    #[Then('the order :order should have order payment state :orderPaymentState')]
    #[Then('/^(this order) should have order payment state "([^"]+)"$/')]
    public function the_order_should_have_payment_state(Order_Interface $order, string $payment_state): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::ORDERS, $order->get_token_value()), 'paymentState', str_replace(' ', '_', strtolower($payment_state))), sprintf('Order %s does not have %s payment state', $order->get_token_value(), $payment_state));
    }
    #[Then('the last order should have order payment state :orderPaymentState')]
    public function the_last_order_should_have_payment_state(string $order_payment_state): void
    {
        $order = $this->response_checker->get_collection($this->client->get_last_response())[0];
        Assert::same($order['paymentState'], strtolower($order_payment_state), sprintf('Order "%s" does not have "%s" payment state', $order['tokenValue'], $order_payment_state));
    }
    #[Then('it should have :amount items')]
    public function it_should_have_amount_of_items(int $amount): void
    {
        Assert::count($this->response_checker->get_value($this->client->get_last_response(), 'items'), $amount);
    }
    #[Then('the product named :productName should be in the items list')]
    public function the_product_should_be_in_the_items_list(string $product_name): void
    {
        $items = $this->response_checker->get_value($this->client->get_last_response(), 'items');
        foreach ($items as $item) {
            if ($item['productName'] === $product_name) {
                return;
            }
        }
        throw new \InvalidArgumentException('There is no product with given name.');
    }
    #[Then('/^the order\'s shipping total should be ("[^"]+")$/')]
    public function the_orders_shipping_total_should_be(int $shipping_total): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'shippingTotal'), $shipping_total);
    }
    #[Then('/^the order\'s tax total should(?:| still) be ("[^"]+")$/')]
    public function the_orders_tax_total_should_be(int $tax_total): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'taxTotal'), $tax_total);
    }
    #[Then('I should not be able to resend the shipment confirmation email')]
    public function i_should_not_be_able_to_resend_the_shipment_confirmation_email(): void
    {
        $this->client->custom_item_action(Resources::SHIPMENTS, (string) $this->shared_storage->get('order')->get_shipments()->last()->get_id(), Http_Request::METHOD_POST, 'resend-confirmation-email');
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'Cannot resend shipment confirmation email for shipment in state ready.');
    }
    #[Then('/^the order\'s items total should be ("[^"]+")$/')]
    public function the_orders_items_total_should_be(int $items_total): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'itemsTotal'), $items_total);
    }
    #[Then('/^the order\'s payment should(?:| also) be ("[^"]+")$/')]
    public function the_orders_payment_should_be(int $payment_amount): void
    {
        $response = $this->client->show_by_iri($this->response_checker->get_value($this->client->get_last_response(), 'payments')[0]['@id']);
        Assert::same($this->response_checker->get_value($response, 'amount'), $payment_amount);
    }
    #[Then('/^I should not be able to cancel (this order)$/')]
    public function i_should_not_be_able_to_cancel_this_order(Order_Interface $order): void
    {
        $this->i_cancel_this_order($order);
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot cancel the order.');
    }
    #[Then('/^the order\'s total should(?:| still) be ("[^"]+")$/')]
    public function the_orders_total_should_be(int $total): void
    {
        $response = $this->client->show(Resources::ORDERS, $this->shared_storage->get('order')->get_token_value());
        Assert::same($this->response_checker->get_value($response, 'total'), $total);
    }
    #[Then('/^the order\'s promotion total should(?:| still) be ("[^"]+")$/')]
    public function the_orders_promotion_total_should_be(int $promotion_total): void
    {
        $response = $this->client->show(Resources::ORDERS, $this->shared_storage->get('order')->get_token_value());
        Assert::same($this->response_checker->get_value($response, 'orderPromotionTotal'), $promotion_total);
    }
    #[Then('the order\'s promotion discount should be :promotionAmount from :promotionName promotion')]
    public function the_orders_promotion_discount_should_be_from_promotion(string $promotion_amount, string $promotion_name): void
    {
        $this->response_checker->has_item_with_values($this->get_adjustments_response_for_order(true), ['type' => Adjustment_Interface::ORDER_PROMOTION_ADJUSTMENT, 'label' => $promotion_name, 'amount' => $this->get_total_as_int($promotion_amount)]);
    }
    #[Then('the order\'s shipping promotion should be :promotionAmount')]
    public function the_orders_shipping_promotion_discount_should_be(string $promotion_amount): void
    {
        $this->response_checker->has_item_with_values($this->get_adjustments_response_for_order(true), ['type' => Adjustment_Interface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT, 'amount' => $this->get_total_as_int($promotion_amount)]);
    }
    #[Then('there should be a shipping charge :shippingCharge for :shippingMethodName method')]
    public function there_should_be_a_shipping_charge_for_method(string $shipping_charge, string $shipping_method_name): void
    {
        $this->response_checker->has_item_with_values($this->get_adjustments_response_for_order(true), ['type' => Adjustment_Interface::SHIPPING_ADJUSTMENT, 'label' => $shipping_method_name, 'amount' => $this->get_total_as_int($shipping_charge)]);
    }
    #[Then('there should be a shipping tax :shippingTax for :shippingMethodName method')]
    public function there_should_be_a_shipping_tax_for_method(string $shipping_tax, string $shipping_method_name): void
    {
        $this->response_checker->has_item_with_values($this->get_adjustments_response_for_order(true), ['type' => Adjustment_Interface::TAX_ADJUSTMENT, 'label' => $shipping_method_name, 'amount' => $this->get_total_as_int($shipping_tax)]);
    }
    #[Then('/^(the administrator) should see that (order placed by "[^"]+") has "([^"]+)" currency$/')]
    public function the_administrator_should_see_that_this_order_has_been_placed_in(Admin_User_Interface $user, Order_Interface $order, string $currency): void
    {
        $this->admin_security_service->log_in($user);
        $currency_code = $this->response_checker->get_value($this->client->show(Resources::ORDERS, $order->get_token_value()), 'currencyCode');
        Assert::same($currency_code, $currency);
    }
    #[Then('I should see an order with :orderNumber number')]
    public function i_should_see_order_with_number(string $order_number): void
    {
        $response = $this->client->get_last_response();
        Assert::true($this->response_checker->has_item_with_value($response, 'number', $order_number), sprintf('No order with number "%s" has been found.', $order_number));
    }
    #[Then('I should not see an order with :orderNumber number')]
    public function i_should_not_see_order_with_number(string $order_number): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->has_item_with_value($response, 'number', $order_number), sprintf('The order with number "%s" has been found, but should not.', $order_number));
    }
    #[Then('I should not see any orders with currency :currencyCode')]
    public function i_should_not_see_any_order_with_currency(string $currency_code): void
    {
        $response = $this->client->get_last_response();
        Assert::false($this->response_checker->has_item_with_value($response, 'currencyCode', $currency_code), sprintf('The order with currency code "%s" has been found, but should not.', $currency_code));
    }
    #[Then('the first order should have number :number')]
    public function the_first_order_should_have_number(string $number): void
    {
        $items = $this->response_checker->get_value($this->client->get_last_response(), 'hydra:member');
        $first_item = $items[0];
        Assert::same($first_item['number'], str_replace('#', '', $number));
    }
    #[Then('/^I should see the order "([^"]+)" with total ("[^"]+")$/')]
    public function i_should_see_the_order_with_total(string $order_number, int $total): void
    {
        $order = $this->response_checker->get_collection_items_with_value($this->client->get_last_response(), 'number', trim($order_number, '#'))[0];
        Assert::same($order['total'], $total);
    }
    #[Then('the administrator should see the order with total :total in order list')]
    public function the_administrator_should_see_the_order_with_total_in_order_list(string $total): void
    {
        $admin_user = $this->shared_storage->get('administrator');
        $currency_code = $this->get_currency_code_from_total($total);
        $total = $this->get_total_as_int($total);
        $this->shared_security_service->perform_action_as_admin_user($admin_user, fn(): \Symfony\Component\Http_Foundation\Response => $this->client->index(Resources::ORDERS));
        $items_with_currency = $this->response_checker->get_collection_items_with_value($this->client->get_last_response(), 'currencyCode', $currency_code);
        $first_item = array_pop($items_with_currency);
        Assert::not_empty($first_item);
        Assert::same($first_item['total'], $total);
    }
    #[Then('it should have been placed by the customer :customer')]
    public function it_should_have_been_placed_by_the_customer(Customer_Interface $customer): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'customer'), $this->iri_converter->get_iri_from_resource($customer));
    }
    #[Then('it should be shipped via the :shippingMethod shipping method')]
    public function it_should_be_shipped_via_the_shipping_method(Shipping_Method_Interface $shipping_method): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'shipments')[0]['method'], $this->iri_converter->get_iri_from_resource($shipping_method));
    }
    #[Then('it should be paid with :paymentMethod')]
    public function it_should_be_paid_with(Payment_Method_Interface $payment_method): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'payments')[0]['method'], $this->iri_converter->get_iri_from_resource($payment_method));
    }
    #[Then('it should have no shipping address set')]
    public function it_should_have_no_shipping_address_set(): void
    {
        Assert::null($this->response_checker->get_value($this->client->get_last_response(), 'shippingAddress'));
    }
    #[Then('it should be shipped to :customerName, :street, :postcode, :city, :countryName')]
    public function it_should_be_shipped_to(string $customer_name, string $street, string $postcode, string $city, string $country_name): void
    {
        $shipping_address = $this->response_checker->get_value($this->client->get_last_response(), 'shippingAddress');
        $this->it_should_be_addressed_to($shipping_address, $customer_name, $street, $postcode, $city, $country_name);
    }
    #[Then('it should have :customerName, :street, :postcode, :city, :countryName as its billing address')]
    public function it_should_have_address_as_it_billing_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): void
    {
        $billing_address = $this->response_checker->get_value($this->client->get_last_response(), 'billingAddress');
        $this->it_should_be_addressed_to($billing_address, $customer_name, $street, $postcode, $city, $country_name);
    }
    #[Then('I should see :provinceName as province in the shipping address')]
    public function i_should_see_as_province_in_the_shipping_address(string $province_name): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'shippingAddress')['provinceName'], $province_name);
    }
    #[Then('I should see :provinceName as province in the billing address')]
    public function i_should_see_as_province_in_the_billing_address(string $province_name): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'billingAddress')['provinceName'], $province_name);
    }
    #[Then('I should see the shipping date as :dateTime')]
    public function i_should_see_the_shipping_date_as(string $date_time): void
    {
        $response = $this->client->show(Resources::SHIPMENTS, (string) $this->shared_storage->get('shipment')->get_id());
        Assert::same($this->response_checker->get_value($response, 'shippedAt'), (new \DateTime($date_time))->format('Y-m-d H:i:s'));
    }
    #[Then('/^(its) unit price should be ([^"]+)$/')]
    public function item_unit_price_should_be(array $order_item, string $unit_price): void
    {
        Assert::same($this->get_total_as_int($unit_price), $order_item['unitPrice']);
    }
    #[Then('/^(its) total should be ([^"]+)$/')]
    public function item_total_should_be(array $order_item, string $total): void
    {
        Assert::same($this->get_total_as_int($total), $order_item['total']);
    }
    #[Then('/^(its) code should be "([^"]+)"$/')]
    public function item_code_should_be(array $order_item, string $code): void
    {
        Assert::ends_with($order_item['variant'], $code);
    }
    #[Then('/^(its) quantity should be ([^"]+)$/')]
    public function item_quantity_should_be(array $order_item, int $quantity): void
    {
        Assert::same($quantity, $order_item['quantity']);
    }
    #[Then('/^its discounted unit price should be ([^"]+)$/')]
    public function item_discounted_unit_price_should_be(string $discounted_unit_price): void
    {
        $this->response_checker->has_item_with_values($this->get_adjustments_response_for_order(), ['type' => Adjustment_Interface::ORDER_ITEM_PROMOTION_ADJUSTMENT, 'amount' => $this->get_total_as_int($discounted_unit_price)]);
    }
    #[Then('/^its subtotal should be ([^"]+)$/')]
    public function item_subtotal_should_be(string $subtotal): void
    {
        $order_item = $this->shared_storage->get('item');
        $unit_promotion_adjustments = 0;
        foreach ($this->response_checker->get_collection($this->client->get_last_response()) as $item) {
            if (in_array($item['type'], [Adjustment_Interface::ORDER_UNIT_PROMOTION_ADJUSTMENT, Adjustment_Interface::ORDER_PROMOTION_ADJUSTMENT])) {
                $unit_promotion_adjustments += $item['amount'];
            }
        }
        Assert::same($this->get_total_as_int($subtotal), $order_item['unitPrice'] * $order_item['quantity'] + $unit_promotion_adjustments);
    }
    #[Then('/^its discount should be ([^"]+)$/')]
    public function the_item_should_have_discount(string $discount): void
    {
        $this->response_checker->has_item_with_values($this->client->get_last_response(), ['type' => Adjustment_Interface::ORDER_UNIT_PROMOTION_ADJUSTMENT, 'amount' => $this->get_total_as_int($discount)]);
    }
    #[Then('/^its tax should be ([^"]+)$/')]
    public function item_tax_should_be(string $tax): void
    {
        $this->response_checker->has_item_with_values($this->client->get_last_response(), ['type' => Adjustment_Interface::TAX_ADJUSTMENT, 'amount' => $this->get_total_as_int($tax)]);
    }
    #[Then('/^its tax included in price should be ([^"]+)$/')]
    public function its_tax_included_in_price_should_be(string $tax): void
    {
        $unit_promotion_adjustments = $this->response_checker->get_collection_items_with_value($this->get_adjustments_response_for_order(), 'type', Adjustment_Interface::TAX_ADJUSTMENT);
        $total_tax = 0;
        foreach ($unit_promotion_adjustments as $unit_promotion_adjustment) {
            if (true === $unit_promotion_adjustment['neutral']) {
                $total_tax += $unit_promotion_adjustment['amount'];
            }
        }
        Assert::same($this->get_total_as_int($tax), $total_tax);
    }
    #[Then('I should be informed that there are no payments')]
    public function i_should_see_information_about_no_payments(): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'payments'), []);
    }
    #[Then('/^the order "[^"]+" should have order shipping state "([^"]+)"$/')]
    #[Then('it should have order\'s shipping state :orderShippingState')]
    public function the_order_should_have_shipping_state(string $order_shipping_state): void
    {
        $orders_response = $this->client->index(Resources::ORDERS, forgetResponse: true);
        Assert::true($this->response_checker->has_item_with_value($orders_response, 'shippingState', strtolower($order_shipping_state)), sprintf('Order does not have %s shipping state', $order_shipping_state));
    }
    #[Then('I should not see information about shipments')]
    public function i_should_not_see_information_about_shipping(): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'shipments'), []);
    }
    #[Then('the :productName product\'s unit price should be :price')]
    public function product_unit_price_should_be(string $product_name, string $price): void
    {
        $this->i_check_data($product_name);
        $order_item = $this->shared_storage->get('item');
        Assert::same($this->get_total_as_int($price), $order_item['unitPrice']);
    }
    #[Then('the :productName product\'s discounted unit price should be :price')]
    public function product_discounted_unit_price_should_be(string $product_name, string $price): void
    {
        $order_item = $this->shared_storage->get('item');
        Assert::same($this->get_total_as_int($price), $order_item['fullDiscountedUnitPrice']);
    }
    #[Then('the :productName product\'s quantity should be :quantity')]
    public function product_quantity_should_be(string $product_name, int $quantity): void
    {
        $order_item = $this->shared_storage->get('item');
        Assert::same($quantity, $order_item['quantity']);
    }
    #[Then('the :productName product\'s item discount should be :price')]
    public function product_item_discount_should_be(string $product_name, string $price): void
    {
        $order_item = $this->shared_storage->get('item');
        $adjustments = $this->response_checker->get_collection_items_with_value($this->get_adjustments_response_for_order(true), 'type', Adjustment_Interface::ORDER_UNIT_PROMOTION_ADJUSTMENT);
        foreach ($adjustments as $adjustment) {
            if (in_array($adjustment['orderItemUnit'], $order_item['units'])) {
                Assert::same($this->get_total_as_int($price), $adjustment['amount']);
                return;
            }
        }
    }
    #[Then('the :productName product\'s order discount should be :price')]
    public function product_order_discount_should_be(string $product_name, string $price): void
    {
        $order_item = $this->shared_storage->get('item');
        $adjustments = $this->response_checker->get_collection_items_with_value($this->get_adjustments_response_for_order(true), 'type', Adjustment_Interface::ORDER_PROMOTION_ADJUSTMENT);
        foreach ($adjustments as $adjustment) {
            if (in_array($adjustment['orderItemUnit'], $order_item['units'])) {
                Assert::same($this->get_total_as_int(trim($price, ' ~')), $adjustment['amount']);
                return;
            }
        }
    }
    #[Then('the :productName product\'s subtotal should be :subTotal')]
    public function product_subtotal_should_be(string $product_name, string $sub_total): void
    {
        $order_item = $this->shared_storage->get('item');
        $response = $this->get_adjustments_response_for_order(true);
        $unit_promotion_adjustments = 0;
        foreach ($this->response_checker->get_collection($response) as $adjustment) {
            if (!in_array($adjustment['type'], [Adjustment_Interface::ORDER_UNIT_PROMOTION_ADJUSTMENT, Adjustment_Interface::ORDER_PROMOTION_ADJUSTMENT])) {
                continue;
            }
            if (!in_array($adjustment['orderItemUnit'], $order_item['units'])) {
                continue;
            }
            $unit_promotion_adjustments += $adjustment['amount'];
        }
        Assert::same($this->get_total_as_int($sub_total), $order_item['unitPrice'] * $order_item['quantity'] + $unit_promotion_adjustments);
    }
    #[Then('I should be notified that the order has been successfully shipped')]
    public function i_should_be_notified_that_the_order_has_been_successfully_shipped(): void
    {
        $response = $this->client->get_last_response();
        Assert::true($this->response_checker->is_accepted($response), 'Order could not be shipped.');
    }
    #[Then('it should have shipment in state shipped')]
    public function it_should_have_shipment_in_state_shipped(): void
    {
        $shipment_iri = $this->response_checker->get_value($this->client->show(Resources::ORDERS, $this->shared_storage->get('order')->get_token_value()), 'shipments')[0];
        Assert::true($this->response_checker->has_value($this->client->show_by_iri($shipment_iri['@id']), 'state', Shipment_Interface::STATE_SHIPPED), sprintf('Shipment for this order is not %s', Shipment_Interface::STATE_SHIPPED));
    }
    #[Then('this order should have order shipping state :orderShippingState')]
    public function this_order_should_have_order_shipping_state(string $order_shipping_state): void
    {
        $orders_response = $this->client->index(Resources::ORDERS);
        Assert::true($this->response_checker->has_item_with_value($orders_response, 'shippingState', strtolower($order_shipping_state)), sprintf('Order does not have %s shipping state', $order_shipping_state));
    }
    #[Then('I should not be able to ship this order')]
    public function i_should_not_be_able_to_ship_this_order(): void
    {
        $order = $this->shared_storage->get('order');
        $this->client->apply_transition(Resources::SHIPMENTS, (string) $order->get_shipments()->first()->get_id(), Shipment_Transitions::TRANSITION_SHIP);
        Assert::false($this->response_checker->is_update_successful($this->client->get_last_response()), 'Order has been shipped, but should not.');
    }
    #[Then('I should be informed that the order does not exist')]
    public function i_should_be_informed_that_the_order_does_not_exist(): void
    {
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'Not Found');
    }
    #[Then('there should be :count shipping address changes in the registry')]
    public function there_should_be_count_shipping_address_changes_in_the_registry(int $count): void
    {
        $order = $this->shared_storage->get('order');
        $response = $this->client->sub_resource_index(Resources::ADDRESSES, Subresources::ADDRESSES_LOG_ENTRIES, (string) $order->get_shipping_address()->get_id());
        Assert::same($this->response_checker->count_collection_items($response), $count);
    }
    #[Then('I should not be able to resend the order confirmation email')]
    public function i_should_not_be_able_to_resend_the_order_confirmation_email(): void
    {
        $this->client->custom_item_action(Resources::ORDERS, $this->shared_storage->get('order')->get_token_value(), Http_Request::METHOD_POST, 'resend-confirmation-email');
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'Cannot resend order confirmation email for order with state cancelled.');
    }
    #[Then('there should be :count billing address changes in the registry')]
    public function there_should_be_count_billing_address_changes_in_the_registry(int $count): void
    {
        $order = $this->shared_storage->get('order');
        $response = $this->client->sub_resource_index(Resources::ADDRESSES, Subresources::ADDRESSES_LOG_ENTRIES, (string) $order->get_billing_address()->get_id());
        Assert::same($this->response_checker->count_collection_items($response), $count);
    }
    #[Then('I should be notified that the order\'s payment has been successfully completed')]
    public function i_should_be_notified_that_the_orders_payment_has_been_successfully_completed(): void
    {
        Assert::true($this->response_checker->is_update_successful($this->client->get_last_response()));
    }
    #[Then('I should be notified that the order\'s payment has been successfully refunded')]
    public function i_should_be_notified_that_the_order_s_payment_has_been_successfully_refunded(): void
    {
        Assert::true($this->response_checker->is_update_successful($this->client->get_last_response()));
    }
    #[Then('/^I should not be able to mark (this order) as paid again$/')]
    public function i_should_not_be_able_to_mark_this_order_as_paid_again(Order_Interface $order): void
    {
        $this->client->apply_transition(Resources::PAYMENTS, (string) $order->get_last_payment()->get_id(), Payment_Transitions::TRANSITION_COMPLETE);
        Assert::false($this->response_checker->is_update_successful($this->client->get_last_response()));
    }
    #[Then('I should be notified that the order\'s payment could not be finalized due to insufficient stock')]
    public function i_should_be_notified_that_the_orders_payment_could_not_be_finalized_due_to_insufficient_stock(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Not enough units to decrease on hold quantity from the inventory of a variant');
    }
    #[Then('I should see this customer\'s IP address')]
    public function i_should_see_customers_ip_address(): void
    {
        Assert::not_empty($this->response_checker->get_value($this->client->get_last_response(), 'customerIp'));
    }
    /**
     * @param array<string, mixed> $address
     */
    private function it_should_be_addressed_to(array $address, string $customer_name, string $street, string $postcode, string $city, string $country_name): void
    {
        Assert::same($address['firstName'] . ' ' . $address['lastName'], $customer_name);
        Assert::same($address['street'], $street);
        Assert::same($address['postcode'], $postcode);
        Assert::same($address['city'], $city);
        Assert::same($address['countryCode'], $this->get_country_code_from_name($country_name));
    }
    private function get_country_code_from_name(string $name): string
    {
        return array_flip(Countries::get_names())[$name];
    }
    private function get_currency_code_from_total(string $total): string
    {
        return match (true) {
            str_starts_with($total, '$') => 'USD',
            str_starts_with($total, '€') => 'EUR',
            str_starts_with($total, '£') => 'GBP',
            default => throw new \InvalidArgumentException('Unsupported currency symbol'),
        };
    }
    private function get_total_as_int(string $total): int
    {
        if ($is_minus = str_starts_with($total, '-')) {
            $total = substr($total, 1);
        }
        $amount = (int) round((float) trim($total, '$€£') * 100, 2);
        if ($is_minus) {
            return $amount * -1;
        }
        return $amount;
    }
    private function get_adjustments_response_for_order(bool $forget_response = false): Response
    {
        $order_token = $this->shared_storage->get('order')->get_token_value();
        return $this->client->sub_resource_index(Resources::ORDERS, Resources::ADJUSTMENTS, (string) $order_token, forgetResponse: $forget_response);
    }
}
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

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Security_Service_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Promotion_Interface;
use Sylius\Component\Core\Order_Checkout_States;
use Symfony\Component\Http_Foundation\Request as HttpRequest;
use Symfony\Component\Http_Foundation\Response;
use Webmozart\Assert\Assert;
final readonly class Order_Context implements Context
{
    public function __construct(private Api_Client_Interface $shop_client, private Api_Client_Interface $admin_client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private Iri_Converter_Interface $iri_converter, private Security_Service_Interface $security_service, private Request_Factory_Interface $request_factory, private string $api_url_prefix)
    {
    }
    #[When('I change my payment method to :paymentMethod')]
    #[When('I try to change my payment method to :paymentMethod')]
    public function i_change_my_payment_method_to(Payment_Method_Interface $payment_method): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $request = $this->request_factory->custom(sprintf('%s/shop/account/orders/%s/payments/%s', $this->api_url_prefix, $order->get_token_value(), (string) $order->get_payments()->first()->get_id()), Http_Request::METHOD_PATCH, [], $this->shop_client->get_token());
        $request->set_content(['paymentMethod' => $this->iri_converter->get_iri_from_resource($payment_method)]);
        $this->shop_client->execute_custom_request($request);
    }
    #[When('I view the summary of my order :order')]
    public function i_view_the_summary_of_my_order(Order_Interface $order): void
    {
        $this->shop_client->show(Resources::ORDERS, $order->get_token_value());
        $this->shared_storage->set('order', $order);
        $this->shared_storage->set('cart_token', $order->get_token_value());
    }
    #[When('I try to see the order placed by a customer :customer')]
    public function i_try_to_see_the_order_placed_by_a_customer(Customer_Interface $customer): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        Assert::eq($order->get_customer(), $customer);
        $this->i_view_the_summary_of_my_order($order);
    }
    #[Then('I should be able to access this order\'s details')]
    public function i_should_be_able_to_access_this_order_details(): void
    {
        $response = $this->shop_client->show(Resources::ORDERS, $this->shared_storage->get('cart_token'));
        Assert::same($response->get_status_code(), Response::HTTP_OK);
        Assert::same($this->response_checker->get_value($this->shop_client->get_last_response(), 'checkoutState'), Order_Checkout_States::STATE_COMPLETED);
        Assert::same($this->shared_storage->get('order_number'), $this->response_checker->get_value($this->shop_client->get_last_response(), 'number'));
    }
    #[Then('it should have the number :orderNumber')]
    public function it_should_have_the_number(string $order_number): void
    {
        Assert::same($this->response_checker->get_value($this->shop_client->get_last_response(), 'number'), $order_number);
    }
    #[Then('I should see :customerName, :street, :postcode, :city, :country as :addressType address')]
    public function i_should_see_as_shipping_address(string $customer_name, string $street, string $postcode, string $city, Country_Interface $country, string $address_type): void
    {
        $address = $this->response_checker->get_value($this->shop_client->get_last_response(), $address_type . 'Address');
        $names = explode(' ', $customer_name);
        Assert::same($address['firstName'], $names[0]);
        Assert::same($address['lastName'], $names[1]);
        Assert::same($address['street'], $street);
        Assert::same($address['postcode'], $postcode);
        Assert::same($address['city'], $city);
        Assert::same($address['countryCode'], $country->get_code());
    }
    #[Then('I should see :amount items in the list')]
    public function i_should_see_items_in_the_list(int $amount): void
    {
        Assert::same(count($this->response_checker->get_value($this->shop_client->get_last_response(), 'items')), $amount);
    }
    #[Then('the product named :productName should be in the items list')]
    public function the_product_should_be_in_the_items_list(string $product_name): void
    {
        $items = $this->response_checker->get_value($this->shop_client->get_last_response(), 'items');
        foreach ($items as $item) {
            if ($item['productName'] === $product_name) {
                return;
            }
        }
        throw new \InvalidArgumentException('There is no product with given name.');
    }
    #[Then('/^the order\'s (shipment) status should be "([^"]+)"$/')]
    #[Then('/^I should see its order\'s (payment) status as "([^"]+)"$/')]
    public function i_should_see_its_order_s_status_as(string $element_type, string $order_element_state): void
    {
        if ($element_type === 'shipment') {
            $element_type = 'shipping';
        }
        Assert::same($order_element_state, String_Inflector::code_to_name($this->response_checker->get_value($this->shop_client->get_last_response(), $element_type . 'State')));
    }
    #[Then('I should see :provinceName as province in the :addressType address')]
    public function i_should_see_as_province_in_the_shipping_address(string $province_name, string $address_type): void
    {
        $address = $this->response_checker->get_value($this->shop_client->get_last_response(), $address_type . 'Address');
        Assert::same($address['provinceName'], $province_name);
    }
    #[Then('/^I should see ("[^"]+") as order\'s subtotal$/')]
    public function i_should_see_as_order_s_subtotal(int $expected_subtotal): void
    {
        $items = $this->response_checker->get_value($this->shop_client->get_last_response(), 'items');
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal = $subtotal + $item['subtotal'];
        }
        Assert::same($subtotal, $expected_subtotal);
    }
    #[Then('/^I should see ("[^"]+") as order\'s total$/')]
    public function i_should_see_as_order_s_total(int $total): void
    {
        Assert::same($this->response_checker->get_value($this->shop_client->get_last_response(), 'total'), $total);
    }
    #[Then('/^I should see that I have to pay ("[^"]+") for this order$/')]
    public function i_should_see_i_have_to_pay_for_this_order(int $payment_amount): void
    {
        $response = $this->shop_client->show_by_iri($this->response_checker->get_value($this->shop_client->get_last_response(), 'payments')[0]['@id']);
        Assert::same($this->response_checker->get_value($response, 'amount'), $payment_amount);
    }
    #[Then(':promotionName should be applied to my order')]
    #[Then(':promotionName should be applied to my order shipping')]
    public function should_be_applied_to_my_order(string $promotion_name): void
    {
        Assert::true($this->has_adjustment_with_label($promotion_name));
    }
    #[Then('/^(this promotion) should give ("[^"]+") discount on shipping$/')]
    public function this_promotion_should_give_discount_on_shipping(Promotion_Interface $promotion, int $discount): void
    {
        $adjustment = $this->get_adjustment_with_label($promotion->get_name());
        Assert::not_null($adjustment);
        Assert::same($discount, $adjustment['amount']);
    }
    #[Then('/^the ("[^"]+" product) should have unit price discounted by ("[^"]+")$/')]
    public function the_should_have_unit_price_discounted_for(Product_Interface $product, int $amount): void
    {
        $discount = 0;
        $item_id = $this->ge_order_item_id_for_product_in_cart($product, $this->shared_storage->get('cart_token'));
        $adjustments = $this->get_adjustments_for_order_item($item_id);
        foreach ($adjustments as $adjustment) {
            $discount += $adjustment['amount'];
        }
        Assert::same(-$discount, $amount);
    }
    #[Then('/^the ("[^"]+" product) should have unit prices discounted by ("[^"]+"), ("[^"]+") and ("[^"]+")$/')]
    public function the_should_have_unit_prices_discounted_for(Product_Interface $product, int $amount_one, int $amount_two, int $amount_three): void
    {
        Assert::true($this->has_correct_amounts_distributed_on_adjustments([$amount_one, $amount_two, $amount_three], $product));
    }
    #[Then('/^the ("[^"]+" product) should have unit prices discounted by ("[^"]+") and ("[^"]+")$/')]
    public function the_should_have_unit_prices_discounted_for_and(Product_Interface $product, int $amount_one, int $amount_two): void
    {
        Assert::true($this->has_correct_amounts_distributed_on_adjustments([$amount_one, $amount_two], $product));
    }
    #[Then('I should have chosen :paymentMethod payment method')]
    public function i_should_have_chosen_payment_method_for_my_order(Payment_Method_Interface $payment_method): void
    {
        $payment = $this->response_checker->get_value($this->shop_client->show(Resources::ORDERS, $this->shared_storage->get('cart_token')), 'payments')[0];
        Assert::same($this->iri_converter->get_iri_from_resource($payment_method), $payment['method']);
    }
    #[Then('I should not be able to see that order')]
    public function i_should_not_be_able_to_see_that_order(): void
    {
        Assert::false($this->response_checker->is_show_successful($this->shop_client->get_last_response()));
    }
    #[Then('I should be denied an access to order list')]
    public function i_should_denied_an_access_to_order_list(): void
    {
        Assert::true($this->response_checker->has_access_denied($this->shop_client->get_last_response()));
    }
    #[Then('I should have :paymentMethod payment method on my order')]
    public function i_should_have_payment_method_on_my_order(Payment_Method_Interface $payment_method): void
    {
        $payment_method_iri = $this->response_checker->get_value($this->shop_client->get_last_response(), 'payments')[0]['method'];
        Assert::same($this->iri_converter->get_resource_from_iri($payment_method_iri)->get_code(), $payment_method->get_code());
    }
    #[Then('/^(the administrator) should know about (this additional note) for (this order made by "[^"]+")$/')]
    public function the_customer_service_should_know_about_this_additional_notes(Admin_User_Interface $user, string $notes, Order_Interface $order): void
    {
        $this->security_service->log_in($user);
        Assert::same($notes, $this->response_checker->get_value($this->admin_client->show(Resources::ORDERS, $order->get_token_value()), 'notes'));
    }
    #[Then('/^(the administrator) should see that (order placed by "[^"]+") has "([^"]+)" currency$/')]
    public function the_administrator_should_see_that_this_order_has_been_placed_in(Admin_User_Interface $user, Order_Interface $order, string $currency): void
    {
        $this->security_service->log_in($user);
        Assert::same($currency, $this->response_checker->get_value($this->admin_client->show(Resources::ORDERS, $order->get_token_value()), 'currencyCode'));
    }
    private function get_adjustments_for_order(): array
    {
        $response = $this->shop_client->sub_resource_index(Resources::ORDERS, 'adjustments', $this->shared_storage->get('cart_token'));
        return $this->response_checker->get_collection($response);
    }
    private function get_adjustments_for_order_item(string $item_id): array
    {
        $response = $this->shop_client->custom_action(sprintf('%s/shop/orders/%s/items/%s/adjustments', $this->api_url_prefix, $this->shared_storage->get('cart_token'), $item_id), Http_Request::METHOD_GET);
        return $this->response_checker->get_collection($response);
    }
    private function ge_order_item_id_for_product_in_cart(Product_Interface $product, string $token_value): ?string
    {
        $items = $this->response_checker->get_value($this->shop_client->show(Resources::ORDERS, $token_value), 'items');
        foreach ($items as $item) {
            $response = $this->get_product_for_item($item);
            if ($this->response_checker->has_value($response, 'code', $product->get_code())) {
                return (string) $item['id'];
            }
        }
        return null;
    }
    private function get_product_for_item(array $item): Response
    {
        if (!isset($item['variant'])) {
            throw new \InvalidArgumentException('Expected array to have variant key, but this key is missing. Current array: ' . json_encode($item));
        }
        $request = $this->request_factory->custom($item['variant'], Http_Request::METHOD_GET);
        $this->shop_client->execute_custom_request($request);
        return $this->shop_client->show_by_iri($this->response_checker->get_value($this->shop_client->get_last_response(), 'product'));
    }
    private function get_adjustment_with_label(string $label): ?array
    {
        $adjustments = $this->get_adjustments_for_order();
        $index = array_search($label, array_column($adjustments, 'label'));
        if ($index) {
            return $adjustments[$index];
        }
        return null;
    }
    private function has_adjustment_with_label(string $label): bool
    {
        return $this->get_adjustment_with_label($label) !== null;
    }
    private function has_correct_amounts_distributed_on_adjustments(array $amounts, Product_Interface $product): bool
    {
        $item_id = $this->ge_order_item_id_for_product_in_cart($product, $this->shared_storage->get('cart_token'));
        $adjustments = $this->get_adjustments_for_order_item($item_id);
        /** @var int $index */
        foreach ($adjustments as $index => $adjustment) {
            if (-$amounts[$index] !== $adjustment['amount']) {
                return false;
            }
        }
        return true;
    }
}
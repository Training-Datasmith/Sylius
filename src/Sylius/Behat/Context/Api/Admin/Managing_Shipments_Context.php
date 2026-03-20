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

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Channel\Model\Channel_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Shipment_Interface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Sylius\Component\Customer\Model\Customer_Interface;
use Sylius\Component\Shipping\Shipment_Transitions;
use Symfony\Component\Http_Foundation\Request as HttpRequest;
use Webmozart\Assert\Assert;
final readonly class Managing_Shipments_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Shared_Storage_Interface $shared_storage, private string $api_url_prefix)
    {
    }
    #[When('I browse shipments')]
    public function i_browse_shipments(): void
    {
        $this->client->index(Resources::SHIPMENTS);
    }
    #[When('I choose :state as a shipment state')]
    public function i_choose_shipment_state(string $state): void
    {
        $this->client->add_filter('state', $state);
    }
    #[When('I move to the details of first shipment\'s order')]
    public function i_move_to_details_of_first_shipment(): void
    {
        $first_shipment = $this->response_checker->get_collection($this->client->get_last_response())[0];
        $this->client->show_by_iri($first_shipment['order']);
    }
    #[When('I choose :channel as a channel filter')]
    public function i_choose_channel_as_a_channel_filter(Channel_Interface $channel): void
    {
        $this->client->add_filter('order.channel.code', $channel->get_code());
    }
    #[When('I choose :shippingMethod as a shipping method filter')]
    public function i_choose_as_a_shipping_method_filter(Shipping_Method_Interface $shipping_method): void
    {
        $this->client->add_filter('method.code', $shipping_method->get_code());
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->client->filter();
    }
    #[When('I view the first shipment of the order :order')]
    public function i_view_the_shipment_of_the_order(Order_Interface $order): void
    {
        $response = $this->client->show(Resources::SHIPMENTS, (string) $order->get_shipments()->first()->get_id());
        $this->shared_storage->set('response', $response);
    }
    #[Then('I should see( only) :count shipment(s) in the list')]
    #[Then('I should see a single shipment in the list')]
    public function i_should_see_count_shipments_in_list(int $count = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[When('I ship the shipment of order :order')]
    public function i_ship_shipment_of_order(Order_Interface $order): void
    {
        $this->client->apply_transition(Resources::SHIPMENTS, (string) $order->get_shipments()->first()->get_id(), Shipment_Transitions::TRANSITION_SHIP);
    }
    #[When('I try to ship the shipment of order :order')]
    public function i_try_to_ship_shipment_of_order(Order_Interface $order): void
    {
        /** @var ShipmentInterface $shipment */
        $shipment = $order->get_shipments()->first();
        $this->client->custom_action(sprintf('%s/admin/shipments/%s/ship', $this->api_url_prefix, (string) $shipment->get_id()), Http_Request::METHOD_PATCH);
    }
    #[When('I ship the shipment of order :order with :trackingCode tracking code')]
    public function i_ship_the_shipment_of_order_with_tracking_code(Order_Interface $order, string $tracking_code): void
    {
        $this->client->apply_transition(Resources::SHIPMENTS, (string) $order->get_shipments()->first()->get_id(), Shipment_Transitions::TRANSITION_SHIP, ['tracking' => $tracking_code]);
    }
    #[Then('I should be notified that the shipment has been successfully shipped')]
    public function i_should_be_notified_that_the_shipment_has_been_successfully_shipped(): void
    {
        Assert::true($this->response_checker->is_accepted($this->client->get_last_response()), 'Shipment was not successfully shipped');
    }
    #[Then('I should be notified that shipment has been already shipped')]
    public function i_should_be_notified_that_the_shipment_has_been_already_shipped(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'You cannot ship a shipment that was shipped before.', 'Shipment was able to be shipped when should not.');
    }
    #[Then('/^I should see the shipment of (order "[^"]+") as "([^"]+)"$/')]
    public function i_should_see_the_shipment_of_order_as(Order_Interface $order, string $shipping_state): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->index(Resources::SHIPMENTS), ['order' => $this->iri_converter->get_iri_from_resource_in_section($order, 'admin'), 'state' => strtolower($shipping_state)]), sprintf('Shipment for order %s with state %s does not exist', $order->get_number(), $shipping_state));
    }
    #[Then('/^I should see shipment for the ("[^"]+" order) as (\d+)(?:|st|nd|rd|th) in the list$/')]
    public function i_should_see_shipment_for_the_order_in_the_list(Order_Interface $order, int $position): void
    {
        Assert::true($this->response_checker->has_item_on_position_with_value($this->client->get_last_response(), --$position, 'order', $this->iri_converter->get_iri_from_resource($order)), sprintf('On position %s there is no shipment for order %s', $position, $order->get_number()));
    }
    #[Then('I should see the shipment of order :order shipped at :dateTime')]
    public function i_should_see_the_shipping_date_as(Order_Interface $order, string $date_time): void
    {
        Assert::eq(new \DateTime($this->response_checker->get_value($this->client->show(Resources::SHIPMENTS, (string) $order->get_shipments()->first()->get_id()), 'shippedAt')), new \DateTime($date_time), 'Shipment was shipped in different date');
    }
    #[Then('the shipment of the :orderNumber order should be :shippingState for :customer')]
    #[Then('the shipment of the :orderNumber order should be :shippingState for :customer in :channel channel')]
    public function shipment_of_order_should_be(string $order_number, string $shipping_state, Customer_Interface $customer, ?Channel_Interface $channel = null): void
    {
        $this->client->index(Resources::SHIPMENTS);
        foreach ($this->response_checker->get_collection_items_with_value($this->client->get_last_response(), 'state', String_Inflector::name_to_lowercase_code($shipping_state)) as $shipment) {
            $order_show_response = $this->client->show_by_iri($shipment['order']);
            if (!$this->response_checker->has_value($order_show_response, 'number', $order_number)) {
                continue;
            }
            $this->client->show_by_iri($this->response_checker->get_value($order_show_response, 'customer'));
            if (!$this->response_checker->has_value($this->client->get_last_response(), 'email', $customer->get_email())) {
                continue;
            }
            if ($channel === null) {
                return;
            }
            $this->client->show_by_iri($this->response_checker->get_value($order_show_response, 'channel'));
            if ($this->response_checker->has_value($this->client->get_last_response(), 'name', $channel->get_name())) {
                return;
            }
        }
        throw new \InvalidArgumentException('There is no shipment with given data');
    }
    #[Then('I should see a shipment of order :order')]
    public function i_should_see_shipment_with_order_number(Order_Interface $order): void
    {
        Assert::true($this->is_shipment_for_order($order), sprintf('There is no shipment for order %s', $order->get_number()));
    }
    #[Then('I should not see a shipment of order :order')]
    public function i_should_not_see_shipment_with_order_number(Order_Interface $order): void
    {
        Assert::false($this->is_shipment_for_order($order), sprintf('There is shipment for order %s', $order->get_number()));
    }
    #[Then('I should see :amount :product units in the list')]
    public function i_should_see_units_in_the_list(int $amount, Product_Interface $product): void
    {
        $response = $this->shared_storage->has('response') ? $this->shared_storage->get('response') : $this->client->get_last_response();
        $shipment_units_from_response = $this->response_checker->get_value($response, 'units');
        $product_units_counter = 0;
        foreach ($shipment_units_from_response as $shipment_unit_from_response) {
            $shipment_unit_response = $this->client->show_by_iri($shipment_unit_from_response);
            $product_variant_response = $this->client->show_by_iri($this->response_checker->get_value($shipment_unit_response, 'shippable')['@id']);
            $product_response = $this->client->show_by_iri($this->response_checker->get_value($product_variant_response, 'product'));
            $product_name = $this->response_checker->get_value($product_response, 'translations')['en_US']['name'];
            if ($product_name === $product->get_name()) {
                ++$product_units_counter;
            }
        }
        Assert::same($product_units_counter, $amount);
    }
    #[Then('I should see the details of order :order')]
    public function i_should_see_order_with_details(Order_Interface $order): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'number'), $order->get_number(), sprintf('Order with number %s does not exist', $order->get_number()));
    }
    #[Then('I should see the shipment state as :shipmentState')]
    public function i_should_see_the_shipment_state_as(string $shipment_state): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'state', String_Inflector::name_to_lowercase_code($shipment_state)), sprintf('Shipment state is not %s', $shipment_state));
    }
    private function is_shipment_for_order(Order_Interface $order): bool
    {
        return $this->response_checker->has_item_with_value($this->client->get_last_response(), 'order', $this->iri_converter->get_iri_from_resource_in_section($order, 'admin'));
    }
}
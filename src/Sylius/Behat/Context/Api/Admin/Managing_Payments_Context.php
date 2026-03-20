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
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Converter\Iri_Converter_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Payment\Payment_Transitions;
use Symfony\Component\Http_Foundation\Request as HttpRequest;
use Webmozart\Assert\Assert;
final readonly class Managing_Payments_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private string $api_url_prefix)
    {
    }
    #[Given('I am browsing payments')]
    #[When('I browse payments')]
    public function i_am_browsing_payments(): void
    {
        $this->client->index(Resources::PAYMENTS);
    }
    #[When('I go to the details of the first payment\'s order')]
    public function i_go_to_the_details_of_the_first_payment_s_order(): void
    {
        $first_payment = $this->response_checker->get_collection($this->client->get_last_response())[0];
        /** @var OrderInterface $order */
        $order = $this->iri_converter->get_resource_from_iri($first_payment['order']);
        $this->client->custom_item_action(Resources::ORDERS, $order->get_token_value(), Http_Request::METHOD_GET, 'payments');
    }
    #[When('I want to view the payment requests of the first payment')]
    public function i_want_to_view_the_payment_requests_of_the_first_payment(): void
    {
        $response = $this->client->get_last_response();
        $this->client->sub_resource_index(Resources::PAYMENTS, Resources::PAYMENT_REQUESTS, (string) $this->response_checker->get_collection($response)[0]['id']);
    }
    #[Then('I should see the details of order :order')]
    public function i_should_see_order_with_details(Order_Interface $order): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'order', $this->iri_converter->get_iri_from_resource_in_section($order, 'admin')), sprintf('Order with number %s does not exist', $order->get_number()));
    }
    #[When('I complete the payment of order :order')]
    public function i_complete_the_payment_of_order(Order_Interface $order): void
    {
        $payment = $order->get_last_payment();
        Assert::not_null($payment);
        $this->client->apply_transition(Resources::PAYMENTS, (string) $payment->get_id(), Payment_Transitions::TRANSITION_COMPLETE);
    }
    #[When('I choose :state as a payment state')]
    public function i_choose_as_a_payment_state(string $state): void
    {
        $this->client->add_filter('state', $state);
    }
    #[When('I choose :channel as a channel filter')]
    public function i_choose_channel_as_a_channel_filter(Channel_Interface $channel): void
    {
        $this->client->add_filter('order.channel.code', $channel->get_code());
    }
    #[When('/^I sort payments by date in (ascending|descending) order$/')]
    public function i_sort_payments_by_registration_date(string $order): void
    {
        $this->client->sort(['createdAt' => str_starts_with($order, 'de') ? 'desc' : 'asc']);
        $this->client->filter();
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->client->filter();
    }
    #[Then('I should see a single payment in the list')]
    #[Then('I should see :count payments in the list')]
    public function i_should_see_payments_in_the_list(int $count = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('the payment of the :orderNumber order should be :paymentState for :customer')]
    public function the_payment_of_the_order_should_be_for(string $order_number, string $payment_state, Customer_Interface $customer): void
    {
        $payments = $this->response_checker->get_collection_items_with_value($this->client->get_last_response(), 'state', String_Inflector::name_to_lowercase_code($payment_state));
        foreach ($payments as $payment) {
            $this->client->show_by_iri($payment['order']);
            $order_response = $this->client->get_last_response();
            if (!$this->response_checker->has_value($order_response, 'number', $order_number)) {
                continue;
            }
            $this->client->show_by_iri($this->response_checker->get_value($order_response, 'customer'));
            $customer_response = $this->client->get_last_response();
            if ($this->response_checker->has_value($customer_response, 'email', $customer->get_email())) {
                return;
            }
        }
        throw new \InvalidArgumentException('There is no payment with given data.');
    }
    #[Then('/^I should see payment for the ("[^"]+" order) as (\d+)(?:|st|nd|rd|th) in the list$/')]
    public function i_should_see_payment_for_the_order_in_the_list(Order_Interface $order, int $position): void
    {
        Assert::true($this->response_checker->has_item_on_position_with_value($this->client->get_last_response(), $position - 1, 'order', sprintf('%s/admin/orders/%s', $this->api_url_prefix, $order->get_token_value())));
    }
    #[Then('I should be notified that the payment has been completed')]
    public function i_should_be_notified_that_the_payment_has_been_completed(): void
    {
        Assert::true($this->response_checker->is_update_successful($this->client->get_last_response()), 'Resource could not be completed');
    }
    #[Then('I should see the payment of order :order as :paymentState')]
    public function i_should_see_the_payment_of_order_as(Order_Interface $order, string $payment_state): void
    {
        $payment = $order->get_last_payment();
        Assert::not_null($payment);
        Assert::true($this->response_checker->has_value($this->client->show(Resources::PAYMENTS, (string) $payment->get_id()), 'state', String_Inflector::name_to_lowercase_code($payment_state)));
    }
    #[Then('I should see (also) the payment of the :order order')]
    public function i_should_see_the_payment_of_the_order(Order_Interface $order): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'order', $this->iri_converter->get_iri_from_resource_in_section($order, 'admin')));
    }
    #[Then('I should not see the payment of the :order order')]
    public function i_should_not_see_the_payment_of_the_order(Order_Interface $order): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->get_last_response(), 'order', $this->iri_converter->get_iri_from_resource_in_section($order, 'admin')));
    }
}
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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request;
use Sylius\Behat\Client\Request_Factory_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Payment\Repository\Payment_Request_Repository_Interface;
use Symfony\Component\Http_Foundation\Request as HTTPRequest;
use Webmozart\Assert\Assert;
final readonly class Payment_Request_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Request_Factory_Interface $request_factory, private Payment_Request_Repository_Interface $payment_request_repository)
    {
    }
    #[When('I try to pay for my order')]
    public function i_try_to_pay_for_my_order(array $payload = []): void
    {
        $this->client->show(Resources::ORDERS, $this->shared_storage->get('cart_token'));
        $payments = $this->response_checker->get_value($this->client->get_last_response(), 'payments');
        $payment = end($payments);
        $this->post_payment_request($payment, $payload);
        $uri = $this->response_checker->get_value($this->client->get_last_response(), '@id');
        $this->shared_storage->set('payment_request_uri', $uri);
    }
    #[When('I try to update my payment request')]
    public function i_try_to_update_my_payment_request(array $payload = []): void
    {
        $this->put_payment_request($this->shared_storage->get('payment_request_uri'), $payload);
    }
    #[Then('a payment request with action :action for payment method :paymentMethod should have state :state')]
    public function a_payment_request_with_action_for_payment_method_should_have_state(string $action, Payment_Method_Interface $payment_method, string $state): void
    {
        $request = $this->get_request_for_payment_request_with_action($action);
        Assert::not_null($request, sprintf('Payment request with action %s not found', $action));
        $this->client->execute_custom_request($request);
        $response = $this->client->get_last_response();
        Assert::same($this->response_checker->get_value($response, 'action'), $action, sprintf('Payment request should have action %s', $action));
        Assert::contains($this->response_checker->get_value($response, 'method'), $payment_method->get_code(), sprintf('Payment request should have payment method %s', $payment_method->get_code()));
        Assert::same($this->response_checker->get_value($response, 'state'), $state, sprintf('Payment request should have state %s', $state));
    }
    private function post_payment_request(array $payment, array $payload): void
    {
        $request = $this->request_factory->create('shop', sprintf('orders/%s/payment-requests', $this->shared_storage->get('cart_token')), 'Authorization', $this->client->get_token());
        $request->set_content(['paymentId' => $payment['id'], 'paymentMethodCode' => $payment['method'], 'payload' => $payload]);
        $this->client->execute_custom_request($request);
    }
    private function put_payment_request(string $payment_request_uri, array $payload = []): void
    {
        $request = $this->request_factory->custom($payment_request_uri, Http_Request::METHOD_PUT, [], $this->client->get_token());
        $request->set_content(['payload' => $payload]);
        $this->client->execute_custom_request($request);
    }
    private function get_request_for_payment_request_with_action(string $action): ?Request
    {
        $order_token = $this->shared_storage->get('cart_token');
        $order = $this->client->show(Resources::ORDERS, $order_token);
        $payments = $this->response_checker->get_value($order, 'payments');
        $payment_id = end($payments)['id'];
        $payment_request = $this->payment_request_repository->find_one_by(['payment' => $payment_id, 'action' => $action]);
        return $payment_request ? $this->request_factory->custom('/api/v2/shop/payment-requests/' . $payment_request->get_hash(), Http_Request::METHOD_GET, [], $this->client->get_token()) : null;
    }
}
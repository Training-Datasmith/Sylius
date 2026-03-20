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
namespace Sylius\Behat\Context\Api\Shop\Checkout;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Order_Payment_States;
use Sylius\Component\Payment\Model\Payment_Interface;
use Webmozart\Assert\Assert;
final readonly class Checkout_Order_Details_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[When('/^I want to browse order details for (this order)$/')]
    public function i_want_to_browse_order_details_for_this_order(Order_Interface $order): void
    {
        $this->shared_storage->set('cart_token', $order->get_token_value());
        $this->shared_storage->set('order', $order);
        $this->client->show(Resources::ORDERS, $order->get_token_value());
    }
    #[Then('I should be able to pay (again)')]
    public function i_should_be_able_to_pay(): void
    {
        $state = $this->get_latest_payment_state();
        Assert::eq($state, Payment_Interface::STATE_NEW);
    }
    #[Then('I should not be able to pay (again)')]
    public function i_should_not_be_able_to_pay(): void
    {
        $state = $this->get_latest_payment_state();
        Assert::not_eq($state, Payment_Interface::STATE_NEW);
    }
    #[When('I want to pay for my order')]
    #[When('I go to the change payment method page')]
    public function i_want_to_pay_for_my_order(): void
    {
        $this->client->show(Resources::ORDERS, $this->shared_storage->get('cart_token'));
    }
    private function get_latest_payment_state(): ?string
    {
        $response = $this->client->show(Resources::ORDERS, $this->shared_storage->get('cart_token'));
        Assert::same($this->client->get_last_response()->get_status_code(), 200);
        // If the payment is canceled we won't be able to retrieve it because only new one are retrievable
        if (Order_Payment_States::STATE_CANCELLED === $this->response_checker->get_value($response, 'paymentState')) {
            return Payment_Interface::STATE_CANCELLED;
        }
        $payments = $this->response_checker->get_value($response, 'payments');
        $payment = end($payments);
        $payment_id = $payment['id'];
        $response = $this->client->request_get(sprintf('orders/%s/payments/%s', $this->shared_storage->get('cart_token'), $payment_id));
        return $this->response_checker->get_value($response, 'state');
    }
}
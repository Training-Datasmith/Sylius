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
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Shipment_Interface;
use Webmozart\Assert\Assert;
final readonly class Shipment_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I try to see the shipment of the order placed by a customer :customer')]
    public function i_try_to_see_the_shipment_of_the_order_placed_by_a_customer(Customer_Interface $customer): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        Assert::eq($order->get_customer(), $customer);
        /** @var ShipmentInterface $shipment */
        $shipment = $order->get_shipments()->first();
        $this->client->request_get(sprintf('orders/%s/shipments/%s', $order->get_token_value(), $shipment->get_id()));
    }
    #[Then('the shipment state should be :state')]
    #[Then('the order\'s shipment state should be :state')]
    public function the_shipment_state_should_be(string $state): void
    {
        $response = $this->client->get_last_response();
        $shipments = $this->response_checker->get_value($response, 'shipments');
        $token = $this->response_checker->get_value($response, 'tokenValue');
        $response = $this->client->request_get(sprintf('orders/%s/shipments/%s', $token, $shipments[0]['id']));
        Assert::true($this->response_checker->has_value($response, 'state', $state, isCaseSensitive: false));
    }
    #[Then('I should not be able to see that shipment')]
    public function i_should_not_be_able_to_see_that_shipment(): void
    {
        Assert::false($this->response_checker->is_show_successful($this->client->get_last_response()));
    }
}
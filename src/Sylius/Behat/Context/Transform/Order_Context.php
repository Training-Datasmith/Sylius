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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Repository\Customer_Repository_Interface;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Order_Context implements Context
{
    /**
     * @param CustomerRepositoryInterface<CustomerInterface> $customerRepository
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(private Customer_Repository_Interface $customer_repository, private Order_Repository_Interface $order_repository)
    {
    }
    #[Transform(':order')]
    #[Transform('/^"([^"]+)" order$/')]
    #[Transform('/^order "([^"]+)"$/')]
    public function get_order_by_number(string $order_number): Order_Interface
    {
        $order_number = $this->get_order_number($order_number);
        $order = $this->order_repository->find_one_by(['number' => $order_number]);
        Assert::not_null($order, sprintf('Cannot find order with number %s', $order_number));
        return $order;
    }
    #[Transform('/^latest order$/')]
    #[Transform('/^last order$/')]
    public function get_latest_order(): Order_Interface
    {
        $orders = $this->order_repository->find_latest(1);
        Assert::not_empty($orders, 'No order have been made');
        return $orders[0];
    }
    #[Transform('/^this order made by "([^"]+)"$/')]
    #[Transform('/^order placed by "([^"]+)"$/')]
    #[Transform('/^the order of "([^"]+)"$/')]
    public function get_order_by_customer(string $email): Order_Interface
    {
        $customer = $this->customer_repository->find_one_by(['email' => $email]);
        Assert::not_null($customer, sprintf('Cannot find customer with email %s.', $email));
        $orders = $this->order_repository->find_by_customer($customer);
        Assert::not_empty($orders);
        return end($orders);
    }
    #[Transform(':orderNumber')]
    #[Transform('/^an order "([^"]+)"$/')]
    #[Transform('/^another order "([^"]+)"$/')]
    #[Transform('/^the order "([^"]+)"$/')]
    #[Transform('/^the "([^"]+)" order$/')]
    public function get_order_number(string $order_number): string
    {
        return str_replace('#', '', $order_number);
    }
}
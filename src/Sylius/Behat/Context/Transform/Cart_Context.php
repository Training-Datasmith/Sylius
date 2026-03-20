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
use Sylius\Behat\Exception\Shared_Storage_Element_Not_Found_Exception;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Order_Checkout_States;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Cart_Context implements Context
{
    /** @param OrderRepositoryInterface<OrderInterface> $orderRepository */
    public function __construct(private Order_Repository_Interface $order_repository, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Transform('/^(cart)$/')]
    public function provide_cart_token(): ?string
    {
        try {
            $token = $this->shared_storage->get('cart_token');
            /** @var OrderInterface $order */
            $order = $this->shared_storage->get('order');
        } catch (Shared_Storage_Element_Not_Found_Exception) {
            return null;
        }
        return $order->get_token_value() === $token && $order->get_checkout_state() !== Order_Checkout_States::STATE_COMPLETED ? $token : null;
    }
    #[Transform('/^(previous cart)$/')]
    public function provide_previous_cart(): ?Order_Interface
    {
        return $this->order_repository->find_one_by(['tokenValue' => $this->shared_storage->get('previous_cart_token'), 'state' => Order_Checkout_States::STATE_CART]);
    }
    #[Transform('/^(customer\'s latest cart)$/')]
    public function provide_latest_cart(): Order_Interface
    {
        $carts = $this->order_repository->find_by(['state' => Order_Checkout_States::STATE_CART], ['createdAt' => 'DESC'], 1);
        Assert::count($carts, 1);
        return $carts[0];
    }
}
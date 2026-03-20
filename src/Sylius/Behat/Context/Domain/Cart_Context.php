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
namespace Sylius\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Doctrine\Persistence\Object_Manager;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Order\Remover\Expired_Carts_Remover_Interface;
use Webmozart\Assert\Assert;
final readonly class Cart_Context implements Context
{
    public function __construct(private Object_Manager $order_manager, private Expired_Carts_Remover_Interface $expired_carts_remover)
    {
    }
    #[Given('/^(?:|he|she) abandoned (the cart) (\d+) (day|days|hour|hours) ago$/')]
    public function they_abandoned_their_cart(Order_Interface $cart, string $amount, string $time): void
    {
        $cart->set_updated_at(new \DateTime('-' . $amount . ' ' . $time));
        $this->order_manager->flush();
    }
    #[Then('/^(this cart) should be automatically deleted$/')]
    public function this_cart_should_be_automatically_deleted(Order_Interface $cart): void
    {
        $this->expired_carts_remover->remove();
        Assert::null($cart->get_id());
    }
    #[Then('/^(this cart) should not be deleted$/')]
    public function this_cart_should_not_be_deleted(Order_Interface $cart): void
    {
        $this->expired_carts_remover->remove();
        Assert::not_null($cart->get_id());
    }
}
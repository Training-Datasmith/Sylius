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
namespace Sylius\Behat\Context\Setup\Checkout;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Sylius\Behat\Exception\Shared_Storage_Element_Not_Found_Exception;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Api_Bundle\Command\Checkout\Choose_Payment_Method;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Symfony\Component\Messenger\Message_Bus_Interface;
final readonly class Payment_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Message_Bus_Interface $command_bus)
    {
    }
    #[Given('I chose :paymentMethod payment method')]
    #[Given('the customer chose :paymentMethod payment method')]
    #[Given('the visitor chose :paymentMethod payment method')]
    public function i_chose_payment_method(Payment_Method_Interface $payment_method): void
    {
        $this->choose_payment_method($payment_method);
    }
    /** @throws SharedStorageElementNotFoundException */
    public function choose_payment_method(?Payment_Method_Interface $payment_method = null): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $payment_method_code = $payment_method?->get_code() ?? $this->shared_storage->get('payment_method')->get_code();
        $this->command_bus->dispatch(new Choose_Payment_Method($order->get_token_value(), $order->get_payments()->first()->get_id(), $payment_method_code));
    }
}
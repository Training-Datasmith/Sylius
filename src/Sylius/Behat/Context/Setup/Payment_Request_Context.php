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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Sylius\Abstraction\State_Machine\State_Machine_Interface;
use Sylius\Bundle\Api_Bundle\Command\Payment\Add_Payment_Request;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Payment\Factory\Payment_Request_Factory_Interface;
use Sylius\Component\Payment\Model\Payment_Request_Interface;
use Sylius\Component\Payment\Payment_Request_Transitions;
use Sylius\Component\Payment\Repository\Payment_Request_Repository_Interface;
use Symfony\Component\Messenger\Message_Bus_Interface;
final readonly class Payment_Request_Context implements Context
{
    public function __construct(private Message_Bus_Interface $command_bus, private Payment_Request_Repository_Interface $payment_request_repository, private Payment_Request_Factory_Interface $payment_request_factory, private State_Machine_Interface $state_machine)
    {
    }
    #[Given('the payment request action :action has been executed for order :order with the payment method :paymentMethod')]
    public function the_payment_request_action_has_been_executed_for_order_with_the_payment_method(string $action, Order_Interface $order, Payment_Method_Interface $payment_method): void
    {
        $add_payment_request = new Add_Payment_Request($order->get_token_value(), $order->get_last_payment()->get_id(), $payment_method->get_code(), $action);
        $this->command_bus->dispatch($add_payment_request);
    }
    #[Given('there is (also) a :state :action payment request for order :order using the :paymentMethod payment method')]
    public function the_payment_request_action_has_been_executed_for_order_with_the_payment_method_and_state(string $state, string $action, Order_Interface $order, Payment_Method_Interface $payment_method): void
    {
        $payment_request = $this->payment_request_factory->create($order->get_last_payment(), $payment_method);
        if ($state !== Payment_Request_Interface::STATE_NEW) {
            $this->state_machine->apply($payment_request, Payment_Request_Transitions::GRAPH, $this->get_transition_for_state($state));
        }
        $payment_request->set_action($action);
        $this->payment_request_repository->add($payment_request);
    }
    private function get_transition_for_state(string $state): string
    {
        return match ($state) {
            'completed' => Payment_Request_Transitions::TRANSITION_COMPLETE,
            'processing' => Payment_Request_Transitions::TRANSITION_PROCESS,
            default => throw new \InvalidArgumentException(sprintf('Invalid state "%s" provided.', $state)),
        };
    }
}
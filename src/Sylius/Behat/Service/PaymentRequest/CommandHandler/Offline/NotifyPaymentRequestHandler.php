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
namespace Sylius\Behat\Service\Payment_Request\Command_Handler\Offline;

use Sylius\Abstraction\State_Machine\State_Machine_Interface;
use Sylius\Behat\Service\Payment_Request\Command\Offline\Notify_Payment_Request;
use Sylius\Bundle\Payment_Bundle\Provider\Payment_Request_Provider_Interface;
use Sylius\Component\Payment\Payment_Request_Transitions;
use Symfony\Component\Messenger\Attribute\As_Message_Handler;
#[As_Message_Handler]
final readonly class Notify_Payment_Request_Handler
{
    public function __construct(private Payment_Request_Provider_Interface $payment_request_provider, private State_Machine_Interface $state_machine)
    {
    }
    public function __invoke(Notify_Payment_Request $notify_payment_request): void
    {
        $payment_request = $this->payment_request_provider->provide($notify_payment_request);
        $this->state_machine->apply($payment_request, Payment_Request_Transitions::GRAPH, Payment_Request_Transitions::TRANSITION_COMPLETE);
    }
}
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
namespace Sylius\Behat\Service\Payment_Request\Command_Provider\Offline;

use Sylius\Behat\Service\Payment_Request\Command\Offline\Notify_Payment_Request;
use Sylius\Bundle\Payment_Bundle\Command_Provider\Payment_Request_Command_Provider_Interface;
use Sylius\Component\Payment\Model\Payment_Request_Interface;
final class Notify_Payment_Request_Command_Provider implements Payment_Request_Command_Provider_Interface
{
    public function supports(Payment_Request_Interface $payment_request): bool
    {
        return $payment_request->get_action() === Payment_Request_Interface::ACTION_NOTIFY;
    }
    public function provide(Payment_Request_Interface $payment_request): \Sylius\Behat\Service\Payment_Request\Command\Offline\Notify_Payment_Request
    {
        return new Notify_Payment_Request($payment_request->get_id());
    }
}
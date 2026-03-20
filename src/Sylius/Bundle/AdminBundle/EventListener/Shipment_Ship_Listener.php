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
namespace Sylius\Bundle\Admin_Bundle\Event_Listener;

use Sylius\Bundle\Core_Bundle\Mailer\Shipment_Email_Manager_Interface;
use Sylius\Component\Core\Model\Shipment_Interface;
use Symfony\Component\Event_Dispatcher\Generic_Event;
use Webmozart\Assert\Assert;
final readonly class Shipment_Ship_Listener
{
    public function __construct(private Shipment_Email_Manager_Interface $shipment_email_manager)
    {
    }
    public function send_confirmation_email(Generic_Event $event): void
    {
        $shipment = $event->get_subject();
        Assert::is_instance_of($shipment, Shipment_Interface::class);
        $this->shipment_email_manager->send_confirmation_email($shipment);
    }
}
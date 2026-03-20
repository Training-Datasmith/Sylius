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
namespace Sylius\Bundle\Addressing_Bundle\Event_Listener;

use Sylius\Component\Addressing\Checker\Country_Provinces_Deletion_Checker_Interface;
use Sylius\Component\Addressing\Checker\Zone_Deletion_Checker_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Symfony\Component\Event_Dispatcher\Generic_Event;
use Symfony\Component\Http_Foundation\Request_Stack;
use Symfony\Component\Http_Foundation\Session\Flash\Flash_Bag_Interface;
use Symfony\Component\Http_Foundation\Session\Session_Interface;
use Webmozart\Assert\Assert;
final readonly class Zone_Member_Integrity_Listener
{
    public function __construct(private Request_Stack $request_stack, private Zone_Deletion_Checker_Interface $zone_deletion_checker, private Country_Provinces_Deletion_Checker_Interface $country_provinces_deletion_checker)
    {
    }
    public function protect_from_removing_zone(Generic_Event $event): void
    {
        $zone = $event->get_subject();
        Assert::is_instance_of($zone, Zone_Interface::class);
        if (!$this->zone_deletion_checker->is_deletable($zone)) {
            /** @var FlashBagInterface $flashes */
            $flashes = $this->get_session()->get_bag('flashes');
            $flashes->add('error', ['message' => 'sylius.resource.delete_error', 'parameters' => ['%resource%' => 'Zone']]);
            $event->stop_propagation();
        }
    }
    public function protect_from_removing_province_within_country(Generic_Event $event): void
    {
        /** @var CountryInterface $country */
        $country = $event->get_subject();
        Assert::is_instance_of($country, Country_Interface::class);
        if (!$this->country_provinces_deletion_checker->is_deletable($country)) {
            /** @var FlashBagInterface $flashes */
            $flashes = $this->get_session()->get_bag('flashes');
            $flashes->add('error', ['message' => 'sylius.resource.delete_error', 'parameters' => ['%resource%' => 'Province']]);
            $event->stop_propagation();
        }
    }
    private function get_session(): Session_Interface
    {
        return $this->request_stack->get_session();
    }
}
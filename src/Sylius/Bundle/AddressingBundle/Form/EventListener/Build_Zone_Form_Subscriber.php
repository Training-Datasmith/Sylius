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
namespace Sylius\Bundle\Addressing_Bundle\Form\Event_Listener;

use Sylius\Component\Addressing\Model\Zone_Interface;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Form\Form_Event;
use Symfony\Component\Form\Form_Events;
use Webmozart\Assert\Assert;
/** @internal */
final class Build_Zone_Form_Subscriber implements Event_Subscriber_Interface
{
    public static function get_subscribed_events(): array
    {
        return [Form_Events::PRE_SUBMIT => 'preSubmit'];
    }
    public function pre_submit(Form_Event $event): void
    {
        $data = $event->get_data();
        if (!isset($data['members'])) {
            return;
        }
        /** @var ZoneInterface $zone */
        $zone = $event->get_form()->get_data();
        Assert::is_instance_of($zone, Zone_Interface::class);
        $members_codes = $zone->get_members()->map(fn($member): string => $member->get_code())->get_values();
        $members = [];
        $newly_added_members = [];
        foreach ($data['members'] as $member) {
            if (!isset($member['code'])) {
                continue;
            }
            $existing_member_index = array_search($member['code'], $members_codes, true);
            if (false === $existing_member_index) {
                $newly_added_members[] = $member;
                continue;
            }
            $members[$existing_member_index] = $member;
        }
        array_push($members, ...$newly_added_members);
        $data['members'] = $members;
        $event->set_data($data);
    }
}
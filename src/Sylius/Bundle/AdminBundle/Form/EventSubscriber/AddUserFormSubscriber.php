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
namespace Sylius\Bundle\Admin_Bundle\Form\Event_Subscriber;

use Sylius\Bundle\Core_Bundle\Form\Type\User\Shop_User_Type;
use Sylius\Component\User\Model\User_Aware_Interface;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Form\Form_Event;
use Symfony\Component\Form\Form_Events;
use Symfony\Component\Validator\Constraints\Valid;
use Webmozart\Assert\Assert;
final class Add_User_Form_Subscriber implements Event_Subscriber_Interface
{
    public static function get_subscribed_events(): array
    {
        return [Form_Events::SUBMIT => 'submit'];
    }
    public function submit(Form_Event $event): void
    {
        $data = $event->get_data();
        $form = $event->get_form();
        /** @var UserAwareInterface $data */
        Assert::is_instance_of($data, User_Aware_Interface::class);
        if (null === $data->get_user()->get_plain_password() && null === $data->get_user()->get_id()) {
            $data->set_user(null);
            $event->set_data($data);
            $form->remove('user');
            $form->add('user', Shop_User_Type::class, ['constraints' => [new Valid()]]);
        }
    }
}
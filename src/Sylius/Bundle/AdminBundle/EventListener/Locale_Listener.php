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

use Sylius\Bundle\Locale_Bundle\Checker\Locale_Usage_Checker_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Sylius\Resource\Symfony\Event_Dispatcher\Generic_Event;
use Symfony\Component\Http_Foundation\Response;
final readonly class Locale_Listener
{
    public function __construct(private Locale_Usage_Checker_Interface $locale_usage_checker)
    {
    }
    public function pre_delete(Generic_Event $event): void
    {
        /** @var LocaleInterface $locale */
        $locale = $event->get_subject();
        if (!$this->locale_usage_checker->is_used($locale->get_code())) {
            return;
        }
        $event->stop('sylius.locale.delete.is_used', errorCode: Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
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
namespace Sylius\Behat\Service\Checker;

interface Email_Checker_Interface
{
    public function has_recipient(string $recipient): bool;
    public function has_message_to(string $message, string $recipient): bool;
    public function count_messages_to(string $recipient): int;
}
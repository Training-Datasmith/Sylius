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
namespace Sylius\Behat\Service;

use Sylius\Behat\Exception\Notification_Expectation_Mismatch_Exception;
use Sylius\Behat\Notification_Type;
interface Notification_Checker_Interface
{
    /**
     * @throws NotificationExpectationMismatchException
     */
    public function check_notification(string $message, Notification_Type $type): void;
}
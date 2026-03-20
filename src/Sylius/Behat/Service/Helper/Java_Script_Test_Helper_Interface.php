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
namespace Sylius\Behat\Service\Helper;

use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Service\Notification_Checker_Interface;
interface Java_Script_Test_Helper_Interface
{
    public function wait_until_assertion_passes(callable $assertion, ?int $timeout = null): void;
    public function wait_until_notification_popups(Notification_Checker_Interface $notification_checker, Notification_Type $type, string $message, ?int $timeout = null): void;
    public function wait_until_page_opens(Page_Interface $page, ?array $options = [], ?int $timeout = null): void;
}
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

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface;
use Friends_Of_Behat\Page_Object_Extension\Page\Unexpected_Page_Exception;
use Sylius\Behat\Exception\Notification_Expectation_Mismatch_Exception;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Service\Notification_Checker_Interface;
final readonly class Java_Script_Test_Helper implements Java_Script_Test_Helper_Interface
{
    public function __construct(private int $microseconds_interval, private int $default_timeout)
    {
    }
    public function wait_until_assertion_passes(callable $callable, ?int $timeout = null): void
    {
        $this->wait_until_exception_disappears($callable, \InvalidArgumentException::class, $timeout);
    }
    public function wait_until_notification_popups(Notification_Checker_Interface $notification_checker, Notification_Type $type, string $message, ?int $timeout = null): void
    {
        $callable = function () use ($notification_checker, $message, $type): void {
            $notification_checker->check_notification($message, $type);
        };
        $this->wait_until_exception_disappears($callable, Element_Not_Found_Exception::class, $timeout, $type);
    }
    public function wait_until_page_opens(Page_Interface $page, ?array $options = [], ?int $timeout = null): void
    {
        $callable = function () use ($page, $options): void {
            $page->open($options);
        };
        $this->wait_until_exception_disappears($callable, Unexpected_Page_Exception::class, $timeout);
    }
    private function wait_until_exception_disappears(callable $callable, string $exception_class, ?int $timeout = null, ?Notification_Type $type = null): void
    {
        $start = microtime(true);
        $timeout ??= $this->default_timeout;
        $end = $start + $timeout;
        do {
            try {
                $callable();
            } catch (Notification_Expectation_Mismatch_Exception $exception) {
                throw new Notification_Expectation_Mismatch_Exception($type, $exception->get_message());
            } catch (\Exception $exception) {
                if ($exception instanceof $exception_class) {
                    usleep($this->microseconds_interval);
                    continue;
                }
            }
            return;
        } while (microtime(true) < $end);
        throw new \InvalidArgumentException('Time has run out and the assertion has not passed yet.');
    }
}
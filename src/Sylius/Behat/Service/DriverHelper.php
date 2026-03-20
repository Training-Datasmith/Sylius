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

use Behat\Mink\Driver\Driver_Interface;
use Behat\Mink\Driver\Panther_Driver;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;
use D_More\Chrome_Driver\Chrome_Driver;
abstract class Driver_Helper
{
    public static function is_javascript(Driver_Interface $driver): bool
    {
        return $driver instanceof Selenium2Driver || $driver instanceof Chrome_Driver || $driver instanceof Panther_Driver;
    }
    public static function is_not_javascript(Driver_Interface $driver): bool
    {
        return !$driver instanceof Selenium2Driver && !$driver instanceof Chrome_Driver && !$driver instanceof Panther_Driver;
    }
    public static function wait_for_page_to_load(Session $session): void
    {
        if (self::is_javascript($session->get_driver())) {
            $session->wait(1000, "document.readyState === 'complete' && !document.querySelector('[data-live-is-loading]')");
        }
    }
    public static function wait_for_element(Session $session, string $selector, int $timeout = 5000): void
    {
        if (self::is_javascript($session->get_driver())) {
            $session->wait($timeout, sprintf('document.querySelector(%s) !== null', json_encode($selector)));
        }
    }
    public static function wait_for_asynchronous_actions_to_finish(Session $session): void
    {
        $session->wait(1000, "!document.querySelector('[data-live-is-loading]')");
    }
    public static function wait_for_form_to_stop_loading(Session $session, int $timeout = 1000): void
    {
        if (self::is_javascript($session->get_driver())) {
            $session->wait($timeout, "document.readyState === 'complete' && !document.querySelector('[data-live-is-loading]')");
        }
    }
}
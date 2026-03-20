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
namespace Sylius\Behat\Service\Setter;

use Behat\Mink\Driver\Panther_Driver;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;
use D_More\Chrome_Driver\Chrome_Driver;
use Friends_Of_Behat\Symfony_Extension\Driver\Symfony_Driver;
use Symfony\Component\Browser_Kit\Cookie;
final readonly class Cookie_Setter implements Cookie_Setter_Interface
{
    public function __construct(private Session $mink_session, private \ArrayAccess $mink_parameters)
    {
    }
    public function set_cookie(string $name, string $value): void
    {
        $driver = $this->mink_session->get_driver();
        $this->ensure_driver_started($driver);
        if ($driver instanceof Symfony_Driver) {
            $driver->get_client()->get_cookie_jar()->set(new Cookie($name, $value, null, null, parse_url((string) $this->mink_parameters['base_url'], \PHP_URL_HOST)));
            return;
        }
        $this->prepare_mink_session_if_needed($this->mink_session);
        $this->mink_session->set_cookie($name, $value);
    }
    private function ensure_driver_started(mixed $driver): void
    {
        if (($driver instanceof Chrome_Driver || $driver instanceof Panther_Driver) && !$driver->is_started()) {
            $driver->start();
        }
    }
    private function prepare_mink_session_if_needed(Session $session): void
    {
        if ($this->should_mink_session_be_prepared($session)) {
            $session->visit(rtrim((string) $this->mink_parameters['base_url'], '/') . '/');
        }
    }
    private function should_mink_session_be_prepared(Session $session): bool
    {
        $driver = $session->get_driver();
        if ($driver instanceof Symfony_Driver) {
            return false;
        }
        if ($driver instanceof Selenium2Driver) {
            if ($driver->get_web_driver_session() === null) {
                return true;
            }
            return $this->is_page_not_loaded($session->get_current_url());
        }
        if ($driver instanceof Chrome_Driver) {
            return $this->is_page_not_loaded($session->get_current_url());
        }
        return !str_contains($session->get_current_url(), (string) $this->mink_parameters['base_url']);
    }
    private function is_page_not_loaded(string $url): bool
    {
        return in_array($url, ['', 'about:blank', 'data:,'], true);
    }
}
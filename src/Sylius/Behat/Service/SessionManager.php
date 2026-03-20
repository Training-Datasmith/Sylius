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

use Behat\Mink\Mink;
use Symfony\Component\Security\Core\Exception\Token_Not_Found_Exception;
final readonly class Session_Manager implements Session_Manager_Interface
{
    private const SESSION_CHROME_HEADLESS_SECOND = 'chrome_headless_second_session';
    public function __construct(private Mink $mink, private Shared_Storage_Interface $shared_storage, private Security_Service_Interface $security_service)
    {
    }
    public function change_session(): void
    {
        $session_name = self::SESSION_CHROME_HEADLESS_SECOND;
        $this->save_and_restart_session($session_name);
        if ($this->shared_storage->has($this->get_key_for_token($session_name))) {
            $this->security_service->restore_token($this->shared_storage->get($this->get_key_for_token($session_name)));
        }
    }
    public function restore_previous_session(): void
    {
        if (!$this->shared_storage->has('behat_previous_session_name')) {
            return;
        }
        /** @var string $sessionName */
        $session_name = $this->shared_storage->get('behat_previous_session_name');
        $this->save_and_restart_session($session_name);
        if ($this->shared_storage->has($this->get_key_for_token($session_name))) {
            $this->security_service->restore_token($this->shared_storage->get($this->get_key_for_token($session_name)));
        }
    }
    private function save_and_restart_session(string $new_session_name): void
    {
        /** @var string $previousSessionName */
        $previous_session_name = $this->mink->get_default_session_name();
        $this->shared_storage->set('behat_previous_session_name', $previous_session_name);
        try {
            $token = $this->security_service->get_current_token();
            $this->shared_storage->set($this->get_key_for_token($previous_session_name), $token);
        } catch (Token_Not_Found_Exception) {
        }
        $this->mink->set_default_session_name($new_session_name);
        $this->mink->restart_sessions();
    }
    private function get_key_for_token(string $session_name): string
    {
        return sprintf('behat_previous_session_token_%s', $session_name);
    }
}
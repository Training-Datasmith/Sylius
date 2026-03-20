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

use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\User\Model\User_Interface;
use Symfony\Component\Security\Core\Exception\Token_Not_Found_Exception;
final readonly class Shared_Security_Service implements Shared_Security_Service_Interface
{
    public function __construct(private Security_Service_Interface $admin_security_service)
    {
    }
    public function perform_action_as_admin_user(Admin_User_Interface $admin_user, callable $action): void
    {
        $this->perform_action_as($this->admin_security_service, $admin_user, $action);
    }
    private function perform_action_as(Security_Service_Interface $security_service, User_Interface $user, callable $action): void
    {
        try {
            $token = $security_service->get_current_token();
        } catch (Token_Not_Found_Exception) {
            $token = null;
        }
        $security_service->log_in($user);
        $action();
        if (null === $token) {
            $security_service->log_out();
            return;
        }
        $security_service->restore_token($token);
    }
}
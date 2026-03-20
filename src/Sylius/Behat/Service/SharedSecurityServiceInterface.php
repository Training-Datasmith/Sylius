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
interface Shared_Security_Service_Interface
{
    public function perform_action_as_admin_user(Admin_User_Interface $admin_user, callable $action);
}
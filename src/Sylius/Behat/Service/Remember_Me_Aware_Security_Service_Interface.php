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

use Sylius\Component\User\Model\User_Interface;
interface Remember_Me_Aware_Security_Service_Interface extends Security_Service_Interface
{
    public function log_in_with_remember_me(User_Interface $user): void;
}
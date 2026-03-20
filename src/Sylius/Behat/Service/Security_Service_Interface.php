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
use Symfony\Component\Security\Core\Authentication\Token\Token_Interface;
use Symfony\Component\Security\Core\Exception\Token_Not_Found_Exception;
interface Security_Service_Interface
{
    /**
     * @throws \InvalidArgumentException
     */
    public function log_in(User_Interface $user): void;
    public function log_out(): void;
    /**
     * @throws TokenNotFoundException
     */
    public function get_current_token(): Token_Interface;
    public function restore_token(Token_Interface $token): void;
}
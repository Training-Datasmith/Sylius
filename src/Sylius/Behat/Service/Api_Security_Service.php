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

use Lexik\Bundle\Jwt_Authentication_Bundle\Security\Authenticator\Token\Jwt_Post_Authentication_Token;
use Lexik\Bundle\Jwt_Authentication_Bundle\Services\Jwt_Token_Manager_Interface;
use Sylius\Component\User\Model\User_Interface;
use Symfony\Component\Security\Core\Authentication\Token\Token_Interface;
final readonly class Api_Security_Service implements Security_Service_Interface
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Jwt_Token_Manager_Interface $jwt_token_manager, private string $firewall_name)
    {
    }
    public function log_in(User_Interface $user): void
    {
        $this->shared_storage->set('token', $this->jwt_token_manager->create($user));
        $this->shared_storage->set('user', $user);
    }
    public function log_out(): void
    {
        $this->shared_storage->set('token', null);
        $this->shared_storage->set('user', null);
    }
    public function get_current_token(): Token_Interface
    {
        /** @var UserInterface $user */
        $user = $this->shared_storage->get('user');
        /** @var string $token */
        $token = $this->shared_storage->get('token');
        return new Jwt_Post_Authentication_Token($user, $this->firewall_name, $user->get_roles(), $token);
    }
    public function restore_token(Token_Interface $token): void
    {
        $this->shared_storage->set('token', (string) $token);
    }
}
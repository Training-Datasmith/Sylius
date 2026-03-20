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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Lexik\Bundle\Jwt_Authentication_Bundle\Services\Jwt_Token_Manager_Interface;
use Sylius\Behat\Service\Security_Service_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Core_Bundle\Fixture\Factory\Example_Factory_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\User\Model\User_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
final readonly class Shop_Security_Context implements Context
{
    /** @param UserRepositoryInterface<ShopUserInterface> $userRepository */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Security_Service_Interface $security_service, private Example_Factory_Interface $user_factory, private User_Repository_Interface $user_repository, private Jwt_Token_Manager_Interface $jwt_token_manager)
    {
    }
    #[Given('I am logged in as :email')]
    #[Given('I logged in as :email')]
    #[Given('the customer logged in as :email')]
    public function i_am_logged_in_as(string $email): void
    {
        $user = $this->user_repository->find_one_by_email($email);
        if ($user === null) {
            $user = $this->user_factory->create(['email' => $email, 'password' => 'sylius', 'enabled' => true]);
            $this->user_repository->add($user);
        }
        $this->security_service->log_in($user);
        $this->shared_storage->set('user', $user);
        $this->store_user_token($user);
    }
    #[Given('I am a logged in customer')]
    #[Given('I am a logged in customer with name :fullName')]
    #[Given('the customer logged in')]
    #[Given('I logged in')]
    public function i_am_logged_in_customer(?string $full_name = null): void
    {
        $user_data = ['email' => 'shop@example.com', 'password' => 'sylius', 'enabled' => true];
        if ($full_name !== null) {
            $names = explode(' ', $full_name);
            $user_data['first_name'] = $names[0];
            $user_data['last_name'] = $names[1];
        }
        $user = $this->user_factory->create($user_data);
        $this->user_repository->add($user);
        $this->security_service->log_in($user);
        $this->shared_storage->set('user', $user);
        $this->store_user_token($user);
    }
    #[Given('I am a logged in customer by using remember me option')]
    public function i_am_logged_in_customer_by_using_remember_me_option(): void
    {
        $user_data = ['email' => 'sylius@example.com', 'password' => 'sylius', 'enabled' => true];
        $user = $this->user_factory->create($user_data);
        $this->user_repository->add($user);
        $this->security_service->log_in_with_remember_me($user);
        $this->store_user_token($user);
    }
    /** Set the token in the shared storage to have access both in UI and API to resources created in the setup context */
    private function store_user_token(User_Interface $user): void
    {
        $this->shared_storage->set('token', $this->jwt_token_manager->create($user));
    }
}
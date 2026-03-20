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
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Api_Bundle\Command\Account\Change_Shop_User_Password;
use Sylius\Bundle\Core_Bundle\Fixture\Factory\Example_Factory_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\User\Model\User_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Symfony\Component\Messenger\Message_Bus_Interface;
final readonly class User_Context implements Context
{
    use Secure_Password_Trait;
    public function __construct(private Shared_Storage_Interface $shared_storage, private User_Repository_Interface $user_repository, private Example_Factory_Interface $user_factory, private Object_Manager $user_manager, private Message_Bus_Interface $message_bus, private string $password_reset_token_ttl)
    {
    }
    #[Given('there is a user :email identified by :password')]
    #[Given('there was account of :email with password :password')]
    #[Given('there is a user :email')]
    public function there_is_user_identified_by(string $email, string $password = 'sylius'): void
    {
        /** @var ShopUserInterface $user */
        $user = $this->user_factory->create(['email' => $email, 'password' => $this->replace_with_secure_password($password), 'enabled' => true]);
        $this->shared_storage->set('user', $user);
        $this->user_repository->add($user);
    }
    #[Given('there is a disabled user :email identified by :password')]
    #[Given('there was disabled account of :email with password :password')]
    #[Given('there is a disabled user :email')]
    public function there_is_disabled_user_identified_by($email, $password = 'sylius'): void
    {
        $user = $this->user_factory->create(['email' => $email, 'password' => $password, 'enabled' => false]);
        $this->shared_storage->set('user', $user);
        $this->user_repository->add($user);
    }
    #[Given('I registered with previously used :email email and :password password')]
    #[Given('I have already registered :email account')]
    public function the_customer_created_account_with_password(string $email, string $password = 'sylius'): void
    {
        /** @var ShopUserInterface $user */
        $user = $this->user_factory->create(['email' => $email, 'password' => $this->replace_with_secure_password($password), 'enabled' => true]);
        $user->set_customer($this->shared_storage->get('customer'));
        $this->shared_storage->set('user', $user);
        $this->user_repository->add($user);
    }
    #[Given('the account of :email was deleted')]
    #[Given('my account :email was deleted')]
    public function account_was_deleted(string $email): void
    {
        /** @var ShopUserInterface $user */
        $user = $this->user_repository->find_one_by_email($email);
        $this->shared_storage->set('customer', $user->get_customer());
        $this->user_repository->remove($user);
    }
    #[Given('its account was deleted')]
    public function his_account_was_deleted(): void
    {
        $user = $this->shared_storage->get('user');
        $this->user_repository->remove($user);
        $this->user_manager->clear();
    }
    #[Given('/^(this user) is not verified$/')]
    #[Given('/^(I) have not verified my account (?:yet)$/')]
    public function account_is_not_verified(User_Interface $user): void
    {
        $user->set_verified_at(null);
        $this->user_manager->flush();
    }
    #[Given('/^(?:(I) have|(this user) has) already received a verification email$/')]
    public function i_have_received_verification_email(User_Interface $user): void
    {
        $this->prepare_user_verification($user);
    }
    #[Given('a verification email has already been sent to :email')]
    public function a_verification_email_has_been_sent_to(string $email): void
    {
        $user = $this->user_repository->find_one_by_email($email);
        $this->prepare_user_verification($user);
    }
    #[Given('/^(I) have already verified my account$/')]
    public function i_have_already_verified_my_account(User_Interface $user): void
    {
        $user->set_verified_at(new \DateTime());
        $this->user_manager->flush();
    }
    #[Given('/^(?:(I) have|(this user) has) already received a resetting password email$/')]
    public function i_have_received_resetting_password_email(User_Interface $user): void
    {
        $this->prepare_user_password_reset_token($user);
    }
    private function prepare_user_verification(User_Interface $user): void
    {
        $token = 'marryhadalittlelamb';
        $this->shared_storage->set('verification_token', $token);
        $user->set_email_verification_token($token);
        $this->user_manager->flush();
    }
    private function prepare_user_password_reset_token(User_Interface $user): void
    {
        $token = 'itotallyforgotmypassword';
        $user->set_password_reset_token($token);
        $user->set_password_requested_at(new \DateTime());
        $this->user_manager->flush();
    }
    #[Given('/^(I) waited too long, and the token expired$/')]
    public function i_waited_too_long_and_the_token_expired(User_Interface $user): void
    {
        /** @var \DateTime $passwordRequestedAt */
        $password_requested_at = $user->get_password_requested_at();
        // Subtracting the ttl twice because date operations tend to be wobbly
        // and might result in random fails due to skip years, daylight saving
        // time date changes, etc
        $interval = new \DateInterval($this->password_reset_token_ttl);
        $password_requested_at->sub($interval);
        $password_requested_at->sub($interval);
        $user->set_password_requested_at($password_requested_at);
        $this->user_manager->flush();
    }
    #[Given('/^(I)\'ve changed my password from "([^"]+)" to "([^"]+)"$/')]
    public function ive_changed_my_password_from_to(User_Interface $user, string $current_password, string $new_password): void
    {
        $current_password = $this->retrieve_secure_password($current_password);
        $change_shop_user_password = new Change_Shop_User_Password(newPassword: $this->replace_with_secure_password($new_password), confirmNewPassword: $this->confirm_secure_password($new_password), currentPassword: $current_password, shopUserId: $user->get_id());
        $this->message_bus->dispatch($change_shop_user_password);
    }
}
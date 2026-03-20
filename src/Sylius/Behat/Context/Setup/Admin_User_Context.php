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
use Behat\Step\When;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Core_Bundle\Fixture\Factory\Example_Factory_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\Core\Model\Avatar_Image_Interface;
use Sylius\Component\Core\Uploader\Image_Uploader_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
use Symfony\Component\Http_Foundation\File\Uploaded_File;
final readonly class Admin_User_Context implements Context
{
    use Secure_Password_Trait;
    /**
     * @param UserRepositoryInterface<AdminUserInterface> $userRepository
     * @param FactoryInterface<AvatarImageInterface> $avatarImageFactory
     */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Example_Factory_Interface $user_factory, private User_Repository_Interface $user_repository, private Image_Uploader_Interface $image_uploader, private Object_Manager $object_manager, private \ArrayAccess $mink_parameters, private Factory_Interface $avatar_image_factory)
    {
    }
    #[Given('there is an administrator :email identified by :password')]
    #[Given('/^there is(?:| also) an administrator "([^"]+)"$/')]
    public function there_is_an_administrator_identified_by($email, string $password = 'sylius'): void
    {
        /** @var AdminUserInterface $adminUser */
        $admin_user = $this->user_factory->create(['email' => $email, 'password' => $this->replace_with_secure_password($password), 'enabled' => true, 'api' => true]);
        $this->user_repository->add($admin_user);
        $this->shared_storage->set('administrator', $admin_user);
    }
    #[Given('there is an administrator with name :username')]
    public function there_is_an_administrator_with_name(?string $username): void
    {
        /** @var AdminUserInterface $adminUser */
        $admin_user = $this->user_factory->create(['username' => $username]);
        $admin_user->set_username($username);
        $this->user_repository->add($admin_user);
        $this->shared_storage->set('administrator', $admin_user);
    }
    #[Given('/^(this administrator) has the "([^"]*)" image as avatar$/')]
    public function this_administrator_has_the_image_as_avatar(Admin_User_Interface $administrator, string $avatar_path): void
    {
        $this->i_have_the_image_as_my_avatar($avatar_path, $administrator);
    }
    #[Given('/^(this administrator) account is disabled$/')]
    #[When('/^(this administrator) account becomes disabled$/')]
    public function this_account_is_disabled(Admin_User_Interface $administrator): void
    {
        $administrator->set_enabled(false);
        $this->object_manager->flush();
    }
    #[Given('/^(this administrator) is using ("[^"]+" locale)$/')]
    #[Given('/^(I) am using ("[^"]+" locale) for my panel$/')]
    public function this_administrator_is_using_locale(Admin_User_Interface $admin_user, ?string $locale_code): void
    {
        $admin_user->set_locale_code($locale_code);
        $this->user_repository->add($admin_user);
        $this->shared_storage->set('administrator', $admin_user);
    }
    #[Given('/^I have the "([^"]*)" image as (my) avatar$/')]
    public function i_have_the_image_as_my_avatar(string $avatar_path, Admin_User_Interface $administrator): void
    {
        $files_path = $this->mink_parameters['files_path'];
        $avatar = $this->avatar_image_factory->create_new();
        $avatar->set_file(new Uploaded_File($files_path . $avatar_path, basename($avatar_path)));
        $this->image_uploader->upload($avatar);
        $administrator->set_avatar($avatar);
        $this->object_manager->flush();
        $this->shared_storage->set($avatar_path, $avatar->get_path());
    }
    #[Given('/^(I) have already received an administrator\'s password resetting email$/')]
    public function i_have_already_received_an_administrators_password_resetting_email(Admin_User_Interface $administrator): void
    {
        $administrator->set_password_reset_token('token');
        $administrator->set_password_requested_at(new \DateTime());
        $this->object_manager->flush();
    }
    #[Given('/^(my) password reset token has already expired$/')]
    public function my_password_reset_token_has_already_expired(Admin_User_Interface $administrator): void
    {
        $administrator->set_password_requested_at(new \DateTime('-1 year'));
        $this->object_manager->flush();
    }
}
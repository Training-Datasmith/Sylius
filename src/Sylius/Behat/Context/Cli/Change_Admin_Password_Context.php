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
namespace Sylius\Behat\Context\Cli;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Symfony\Bundle\Framework_Bundle\Console\Application;
use Symfony\Component\Console\Tester\Command_Tester;
use Symfony\Component\Http_Kernel\Kernel_Interface;
use Symfony\Component\Password_Hasher\Hasher\User_Password_Hasher_Interface;
use Webmozart\Assert\Assert;
final class Change_Admin_Password_Context implements Context
{
    use Secure_Password_Trait;
    private const ADMIN_USER_CHANGE_PASSWORD = 'sylius:admin-user:change-password';
    private Application $application;
    private ?Command_Tester $command_tester = null;
    /** @var array<string, string> */
    private array $input = [];
    /** @param UserRepositoryInterface<AdminUserInterface> $adminUserRepository */
    public function __construct(Kernel_Interface $kernel, private readonly User_Repository_Interface $admin_user_repository, private readonly User_Password_Hasher_Interface $user_password_hasher, private readonly Shared_Storage_Interface $shared_storage)
    {
        $this->application = new Application($kernel);
    }
    #[When('I want to change password')]
    public function i_want_to_change_password(): void
    {
        $command = $this->application->find(self::ADMIN_USER_CHANGE_PASSWORD);
        $this->command_tester = new Command_Tester($command);
    }
    #[When('I specify email as :email')]
    public function i_specify_email_as(string $email = ''): void
    {
        $this->input['email'] = $email;
    }
    #[When('I specify my new password as :password')]
    public function i_specify_my_new_password(string $password = ''): void
    {
        $this->input['password'] = $this->replace_with_secure_password($password);
    }
    #[When('I run command')]
    public function i_run_command(): void
    {
        $this->command_tester->set_inputs($this->input);
        $this->command_tester->execute(['command' => self::ADMIN_USER_CHANGE_PASSWORD]);
    }
    #[Then('I should be informed that password has been changed successfully')]
    public function i_should_be_informed_that_password_has_been_changed_successfully(): void
    {
        Assert::contains($this->command_tester->get_display(), 'Admin user password has been changed successfully.');
    }
    #[Then('I should be able to log in as :email authenticated by :password password')]
    public function i_should_be_able_to_login_with_email_and_password(string $email = '', string $password = ''): void
    {
        /** @var AdminUserInterface|null $adminUser */
        $admin_user = $this->admin_user_repository->find_one_by_email($email);
        $admin_user->set_plain_password($this->retrieve_secure_password($password));
        Assert::same($admin_user->get_password(), $this->user_password_hasher->hash_password($admin_user, $admin_user->get_plain_password()));
    }
}
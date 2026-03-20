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
namespace Sylius\Bundle\Admin_Bundle\Console\Command;

use Sylius\Bundle\Admin_Bundle\Console\Command\Factory\Question_Factory_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Sylius\Component\User\Security\Password_Updater_Interface;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Console\Style\Symfony_Style;
#[As_Command(name: 'sylius:admin-user:change-password', description: 'Change password of admin user')]
final class Change_Admin_User_Password_Command extends Command
{
    protected Symfony_Style $io;
    /** @param UserRepositoryInterface<AdminUserInterface> $adminUserRepository */
    public function __construct(private readonly User_Repository_Interface $admin_user_repository, private readonly Password_Updater_Interface $password_updater, private readonly Question_Factory_Interface $question_factory)
    {
        parent::__construct();
    }
    protected function initialize(Input_Interface $input, Output_Interface $output): void
    {
        $this->io = new Symfony_Style($input, $output);
    }
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        if (!$input->is_interactive()) {
            $this->io->error('This command must be run interactively.');
            return Command::FAILURE;
        }
        $this->io->title('Change admin user password');
        $email = $this->io->ask_question($this->question_factory->create_email());
        /** @var AdminUserInterface|null $adminUser */
        $admin_user = $this->admin_user_repository->find_one_by_email($email);
        if ($admin_user === null) {
            $this->io->error(sprintf('Admin user with email address %s not found!', $email));
            return Command::INVALID;
        }
        $password = $this->io->ask_question($this->question_factory->create_with_not_null_validator('New password', true));
        $admin_user->set_plain_password($password);
        $this->password_updater->update_password($admin_user);
        $this->admin_user_repository->add($admin_user);
        $this->io->success('Admin user password has been changed successfully.');
        return Command::SUCCESS;
    }
}
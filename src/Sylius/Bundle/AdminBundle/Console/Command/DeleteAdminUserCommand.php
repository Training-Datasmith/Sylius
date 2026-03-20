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

use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Console\Style\Symfony_Style;
#[As_Command(name: 'sylius:admin-user:delete', description: 'Deletes an admin user account by the given email')]
final class Delete_Admin_User_Command extends Command
{
    protected Symfony_Style $io;
    /** @param UserRepositoryInterface<AdminUserInterface> $userRepository */
    public function __construct(private readonly User_Repository_Interface $user_repository)
    {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->set_description('Deletes an admin user')->add_argument('email', Input_Argument::REQUIRED, 'The email of the admin user to delete');
    }
    protected function initialize(Input_Interface $input, Output_Interface $output): void
    {
        $this->io = new Symfony_Style($input, $output);
    }
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $this->io->title('Delete admin user');
        $email = $input->get_argument('email');
        $admin_user = $this->user_repository->find_one_by(['email' => $email]);
        if ($admin_user === null) {
            $this->io->error(sprintf('Admin Account with the email "%s" does not exist', $email));
            return Command::FAILURE;
        }
        if ($input->is_interactive() && !$this->io->confirm(sprintf('Are you sure you want to delete the admin user "%s"?', $email), false)) {
            return Command::FAILURE;
        }
        $this->user_repository->remove($admin_user);
        $this->io->success(sprintf('Admin Account with the email "%s" has been deleted successfully', $email));
        return Command::SUCCESS;
    }
}
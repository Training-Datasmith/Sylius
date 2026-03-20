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

use Doctrine\ORM\Entity_Manager_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Console\Style\Symfony_Style;
#[As_Command(name: 'sylius:admin-user:list', description: 'Displays a list of all admin users along with their details.')]
final class List_Admin_Users_Command extends Command
{
    protected Symfony_Style $io;
    /** @param UserRepositoryInterface<AdminUserInterface> $adminUserRepository */
    public function __construct(private readonly User_Repository_Interface $admin_user_repository, private readonly Entity_Manager_Interface $entity_manager)
    {
        parent::__construct();
    }
    protected function initialize(Input_Interface $input, Output_Interface $output): void
    {
        $this->io = new Symfony_Style($input, $output);
    }
    protected function configure(): void
    {
        $this->set_aliases(['sylius:admin-users:list']);
        $this->add_option('search', 's', Input_Option::VALUE_OPTIONAL, 'Filter users based on a search term. If not provided, all users will be displayed.');
    }
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $this->io->title('List available admin users');
        $search_term = $input->get_option('search');
        if ($search_term === null || $search_term === '') {
            $this->render_users_table($output, $this->admin_user_repository->find_all());
            return Command::SUCCESS;
        }
        $users = $this->search_users($search_term);
        if (empty($users)) {
            $this->io->warning(sprintf('No users found matching "%s"', $search_term));
            return Command::SUCCESS;
        }
        $this->io->success(sprintf('Found %d user(s) matching "%s"', count($users), $search_term));
        $this->render_users_table($output, $users);
        return Command::SUCCESS;
    }
    /**
     * @return array<AdminUserInterface>
     */
    private function search_users(string $search_term): array
    {
        $criteria = '%' . mb_strtolower($search_term) . '%';
        return $this->entity_manager->create_query_builder()->from(Admin_User_Interface::class, 'u')->select('u')->where('LOWER(u.email) LIKE :criteria
                OR LOWER(u.username) LIKE :criteria
                OR LOWER(u.firstName) LIKE :criteria
                OR LOWER(u.lastName) LIKE :criteria')->set_parameter('criteria', $criteria)->order_by('u.createdAt', 'DESC')->get_query()->get_result();
    }
    /**
     * @param array<AdminUserInterface> $adminUsers
     */
    private function render_users_table(Output_Interface $output, array $admin_users): void
    {
        $table = new Table($output);
        $table->set_headers(['ID', 'E-Mail', 'Username', 'First name', 'Last name', 'Locale', 'Enabled', 'Roles']);
        foreach ($admin_users as $admin_user) {
            $table->add_row([$admin_user->get_id(), $admin_user->get_email(), $admin_user->get_username(), $admin_user->get_first_name() ?? '', $admin_user->get_last_name() ?? '', $admin_user->get_locale_code(), $admin_user->is_enabled() ? '✔' : '✘', $this->format_roles($admin_user->get_roles())]);
        }
        $table->render();
    }
    /**
     * @param array<string> $roles
     */
    private function format_roles(array $roles): string
    {
        if ($roles === []) {
            return 'No roles';
        }
        $formatted = array_map(static fn(string $role): string => str_replace('ROLE_', '', $role), $roles);
        return implode(",\n", $formatted);
    }
}
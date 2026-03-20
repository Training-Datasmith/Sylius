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

use Sylius\Bundle\Admin_Bundle\Command\Create_Admin_User;
use Sylius\Bundle\Admin_Bundle\Console\Command\Factory\Question_Factory_Interface;
use Sylius\Bundle\Admin_Bundle\Exception\Create_Admin_User_Failed_Exception;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Console\Style\Symfony_Style;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Messenger\Exception\Handler_Failed_Exception;
use Symfony\Component\Messenger\Handle_Trait;
use Symfony\Component\Messenger\Message_Bus_Interface;
#[As_Command(name: 'sylius:admin-user:create', description: 'Create a new admin user')]
final class Create_Admin_User_Command extends Command
{
    use Handle_Trait;
    protected Symfony_Style $io;
    public function __construct(Message_Bus_Interface $message_bus, private string $default_locale_code, private Question_Factory_Interface $question_factory)
    {
        $this->message_bus = $message_bus;
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
        $this->io->title('Admin user creation');
        $admin_user_data = $this->ask_admin_user_data();
        $this->show_summary($admin_user_data);
        if (!$this->admin_creation_confirmed()) {
            $this->io->error('Admin user creation has been aborted.');
            return Command::INVALID;
        }
        try {
            $this->handle(new Create_Admin_User(...array_values($admin_user_data)));
        } catch (Handler_Failed_Exception $exception) {
            $exceptions = $exception->get_wrapped_exceptions(Create_Admin_User_Failed_Exception::class);
            if ($wrapped_exception = reset($exceptions)) {
                $this->io->error($wrapped_exception->get_message());
            }
            return Command::FAILURE;
        }
        $this->io->success('Admin user has been successfully created.');
        return Command::SUCCESS;
    }
    /** @return array<array-key, mixed> */
    private function ask_admin_user_data(): array
    {
        $admin_user_data = [];
        $admin_user_data['email'] = $this->io->ask_question($this->question_factory->create_email());
        $admin_user_data['username'] = $this->io->ask_question($this->question_factory->create_with_not_null_validator('Username'));
        $admin_user_data['first_name'] = $this->io->ask('First name');
        $admin_user_data['last_name'] = $this->io->ask('Last name');
        $admin_user_data['plain_password'] = $this->io->ask_question($this->question_factory->create_with_not_null_validator('Password', true));
        $locale_codes = Locales::get_names();
        $admin_user_data['locale_code'] = $this->io->choice('Locale code', $locale_codes, $this->default_locale_code);
        $admin_user_data['enabled'] = $this->io->confirm('Do you want to enable this admin user?', true);
        return $admin_user_data;
    }
    /** @param array<array-key, mixed> $adminUserData */
    private function show_summary(array $admin_user_data): void
    {
        $this->io->writeln('The following admin user will be created:');
        $this->io->table(['Email', 'Username', 'First name', 'Last name', 'Locale code', 'Enabled'], [[$admin_user_data['email'], $admin_user_data['username'], $admin_user_data['first_name'], $admin_user_data['last_name'], $admin_user_data['locale_code'], $admin_user_data['enabled'] ? 'Yes' : 'No']]);
    }
    private function admin_creation_confirmed(): bool
    {
        return $this->io->confirm('Do you want to save this admin user?');
    }
}
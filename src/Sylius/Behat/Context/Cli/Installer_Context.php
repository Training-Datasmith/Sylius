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
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\ORM\Entity_Manager_Interface;
use Sylius\Bundle\Core_Bundle\Console\Command\Install_Sample_Data_Command;
use Sylius\Bundle\Core_Bundle\Console\Command\Setup_Command;
use Sylius\Bundle\Core_Bundle\Installer\Checker\Command_Directory_Checker;
use Sylius\Bundle\Core_Bundle\Installer\Setup\Channel_Setup_Interface;
use Sylius\Bundle\Core_Bundle\Installer\Setup\Currency_Setup_Interface;
use Sylius\Bundle\Core_Bundle\Installer\Setup\Locale_Setup_Interface;
use Sylius\Component\Resource\Factory\Factory_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Symfony\Bundle\Framework_Bundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\Command_Tester;
use Symfony\Component\Http_Kernel\Kernel_Interface;
use Symfony\Component\Validator\Validator\Validator_Interface;
use Webmozart\Assert\Assert;
final class Installer_Context implements Context
{
    private ?Application $application = null;
    private ?Command_Tester $tester = null;
    private ?Command $command = null;
    private array $input_choices = ['currency' => 'USD', 'locale' => 'en_US', 'e-mail' => 'test@email.com', 'username' => 'test', 'firstName' => '', 'lastName' => '', 'password' => 'pswd', 'confirmation' => 'pswd'];
    public function __construct(private readonly Kernel_Interface $kernel, private readonly Entity_Manager_Interface $entity_manager, private readonly Command_Directory_Checker $command_directory_checker, private readonly Currency_Setup_Interface $currency_setup, private readonly Locale_Setup_Interface $locale_setup, private readonly Channel_Setup_Interface $channel_setup, private readonly Factory_Interface $admin_user_factory, private readonly User_Repository_Interface $admin_user_repository, private readonly Validator_Interface $validator, private readonly string $public_dir)
    {
    }
    #[When('I run Sylius CLI installer')]
    public function i_run_sylius_command_line_installer(): void
    {
        $this->application = new Application($this->kernel);
        $this->application->add(new Setup_Command($this->entity_manager, $this->command_directory_checker, $this->currency_setup, $this->locale_setup, $this->channel_setup, $this->admin_user_factory, $this->admin_user_repository, $this->validator));
        $this->command = $this->application->find('sylius:install:setup');
        $this->tester = new Command_Tester($this->command);
        $this->i_execute_command_with_input_choices('sylius:install:setup');
    }
    #[Given('I run Sylius Install Load Sample Data command')]
    public function i_run_sylius_install_sample_data_command(): void
    {
        $this->application = new Application($this->kernel);
        $this->application->add(new Install_Sample_Data_Command($this->entity_manager, $this->command_directory_checker, $this->public_dir));
        $this->command = $this->application->find('sylius:install:sample-data');
        $this->tester = new Command_Tester($this->command);
    }
    #[Given('I confirm loading sample data')]
    public function i_confirm_loading_data(): void
    {
        $this->i_execute_command_and_confirm('sylius:install:sample-data');
    }
    #[Then('the command should finish successfully')]
    public function command_success(): void
    {
        Assert::same($this->tester->get_status_code(), 0);
    }
    #[Then('I should see output :text')]
    public function i_should_see_output(string $text): void
    {
        Assert::contains($this->tester->get_display(), $text);
    }
    #[Given('I do not provide an email')]
    public function i_do_not_provide_email(): void
    {
        $this->input_choices['e-mail'] = '';
    }
    #[Given('I do not provide a correct email')]
    public function i_do_not_provide_correct_email(): void
    {
        $this->input_choices['e-mail'] = 'janusz';
    }
    #[Given('I provide full administrator data')]
    public function i_provide_full_administrator_data(): void
    {
        $this->input_choices['e-mail'] = 'test@admin.com';
        $this->input_choices['username'] = 'test';
        $this->input_choices['firstName'] = 'John';
        $this->input_choices['lastName'] = 'Doe';
        $this->input_choices['password'] = 'pswd1$';
        $this->input_choices['confirmation'] = $this->input_choices['password'];
    }
    private function i_execute_command_with_input_choices(string $name): void
    {
        try {
            $this->tester->set_inputs($this->input_choices);
            $this->tester->execute(['command' => $name]);
        } catch (\Exception) {
        }
    }
    private function i_execute_command_and_confirm(string $name): void
    {
        try {
            $this->tester->set_inputs(['y']);
            $this->tester->execute(['command' => $name]);
        } catch (\Exception) {
        }
    }
}
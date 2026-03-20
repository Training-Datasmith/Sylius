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
namespace Sylius\Bundle\Admin_Bundle\Command_Handler;

use Sylius\Bundle\Admin_Bundle\Command\Create_Admin_User;
use Sylius\Bundle\Admin_Bundle\Exception\Create_Admin_User_Failed_Exception;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\User\Canonicalizer\Canonicalizer_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
use Symfony\Component\Messenger\Attribute\As_Message_Handler;
use Symfony\Component\Validator\Constraint_Violation_List_Interface;
use Symfony\Component\Validator\Validator\Validator_Interface;
#[As_Message_Handler]
final readonly class Create_Admin_User_Handler
{
    /**
     * @param UserRepositoryInterface<AdminUserInterface> $adminUserRepository
     * @param FactoryInterface<AdminUserInterface> $adminUserFactory
     * @param array<array-key, string> $validationGroups
     */
    public function __construct(private User_Repository_Interface $admin_user_repository, private Factory_Interface $admin_user_factory, private Canonicalizer_Interface $canonicalizer, private Validator_Interface $validator, private array $validation_groups)
    {
    }
    public function __invoke(Create_Admin_User $command): void
    {
        $admin_user = $this->set_up_admin_user($command);
        $constraint_violation_list = $this->validator->validate($admin_user, null, $this->validation_groups);
        if ($constraint_violation_list->count()) {
            $violation_messages = $this->get_violation_messages($constraint_violation_list);
            throw new Create_Admin_User_Failed_Exception(implode(\PHP_EOL, [...$violation_messages]));
        }
        $this->admin_user_repository->add($admin_user);
    }
    private function set_up_admin_user(Create_Admin_User $command): Admin_User_Interface
    {
        /** @var AdminUserInterface $adminUser */
        $admin_user = $this->admin_user_factory->create_new();
        $admin_user->set_email($this->canonicalizer->canonicalize($command->get_email()));
        $admin_user->set_username($command->get_username());
        $admin_user->set_plain_password($command->get_plain_password());
        $admin_user->set_first_name($command->get_first_name());
        $admin_user->set_last_name($command->get_last_name());
        $admin_user->set_locale_code($command->get_locale_code());
        $admin_user->set_enabled($command->is_enabled());
        return $admin_user;
    }
    /** @return iterable<string> */
    private function get_violation_messages(Constraint_Violation_List_Interface $constraint_violation_list): iterable
    {
        foreach ($constraint_violation_list as $violation) {
            yield $violation->get_message();
        }
    }
}
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
use Sylius\Behat\Service\Security_Service_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Core_Bundle\Fixture\Factory\Example_Factory_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Admin_Security_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Security_Service_Interface $security_service, private Example_Factory_Interface $user_factory, private User_Repository_Interface $user_repository)
    {
    }
    #[Given('I am logged in as an administrator')]
    #[Given('there is logged in the administrator')]
    public function i_am_logged_in_as_an_administrator(): void
    {
        $user = $this->user_factory->create(['email' => 'sylius@example.com', 'password' => 'sylius', 'api' => true]);
        $this->user_repository->add($user);
        $this->security_service->log_in($user);
        $this->shared_storage->set('administrator', $user);
    }
    #[Given('/^I am logged in as "([^"]+)" administrator$/')]
    public function i_am_logged_in_as_administrator(string $email): void
    {
        $user = $this->user_repository->find_one_by_email($email);
        Assert::not_null($user);
        $this->security_service->log_in($user);
        $this->shared_storage->set('administrator', $user);
    }
    #[Given('I have been logged out from administration')]
    public function i_have_been_logged_out_from_administration(): void
    {
        $this->security_service->log_out();
        $this->shared_storage->set('administrator', null);
    }
}
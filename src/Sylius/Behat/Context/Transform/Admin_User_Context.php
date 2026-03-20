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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Admin_User_Context implements Context
{
    public function __construct(private Repository_Interface $admin_user_repository, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Transform(':adminUser')]
    public function get_admin_user_by_email(string $email): Admin_User_Interface
    {
        $admin_user = $this->admin_user_repository->find_one_by(['email' => $email]);
        Assert::not_null($admin_user, sprintf('Administrator with email "%s" does not exist', $email));
        return $admin_user;
    }
    #[Transform('/^(I|my)$/')]
    public function get_logged_admin_user()
    {
        return $this->shared_storage->get('administrator');
    }
}
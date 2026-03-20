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
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\User\Repository\User_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Shop_User_Context implements Context
{
    public function __construct(private User_Repository_Interface $shop_user_repository)
    {
    }
    #[Transform(':shopUser')]
    public function get_shop_user_by_email(string $email): Shop_User_Interface
    {
        $shop_user = $this->shop_user_repository->find_one_by_email($email);
        Assert::not_null($shop_user, sprintf('Shop User with email "%s" does not exist', $email));
        return $shop_user;
    }
}
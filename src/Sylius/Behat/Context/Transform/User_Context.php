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
class User_Context implements Context
{
    public function __construct(private readonly Shared_Storage_Interface $shared_storage)
    {
    }
    #[Transform('/^(I|my|he|his|she|her|"this user")$/')]
    public function get_logged_user()
    {
        return $this->shared_storage->get('user');
    }
}
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
namespace Sylius\Behat\Page\Shop\Account;

use Sylius\Behat\Page\Sylius_Page;
class Verification_Page extends Sylius_Page implements Verification_Page_Interface
{
    public function verify_account(string $token): void
    {
        $this->try_to_open(['token' => $token]);
    }
    public function get_route_name(): string
    {
        return 'sylius_shop_user_verification';
    }
}
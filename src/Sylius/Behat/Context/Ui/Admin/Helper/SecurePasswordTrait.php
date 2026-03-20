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
namespace Sylius\Behat\Context\Ui\Admin\Helper;

trait Secure_Password_Trait
{
    private function replace_with_secure_password(string $password): string
    {
        $this->shared_storage->set('scenario_setup_password', $password);
        // If the password is empty or less than 4 characters, use the provided password to satisfy input validation
        $new_password = empty($password) || strlen($password) < 4 ? $password : bin2hex(random_bytes(16));
        $this->shared_storage->set('password', $new_password);
        return $new_password;
    }
    private function confirm_secure_password(string $password): string
    {
        return $password === $this->shared_storage->get('scenario_setup_password') ? $this->shared_storage->get('password') : $password;
    }
    private function retrieve_secure_password(string $password): string
    {
        $scenario_setup_password = $this->shared_storage->get('scenario_setup_password');
        // If the provided password matches the scenario setup password,
        // use the secure password generated earlier; otherwise, return the scenario setup password to cause the test to fail
        return $scenario_setup_password === $password ? $this->shared_storage->get('password') : $scenario_setup_password;
    }
}
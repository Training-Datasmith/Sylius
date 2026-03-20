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
namespace Sylius\Bundle\Admin_Bundle\Form\Model;

class Password_Reset
{
    private ?string $password = null;
    public function get_password(): ?string
    {
        return $this->password;
    }
    public function set_password(?string $password): void
    {
        $this->password = $password;
    }
}
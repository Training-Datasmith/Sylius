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

class Password_Reset_Request
{
    private ?string $email = null;
    public function get_email(): ?string
    {
        return $this->email;
    }
    public function set_email(?string $email): void
    {
        $this->email = $email;
    }
}
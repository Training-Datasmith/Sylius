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
namespace Sylius\Behat\Element\Admin;

interface Top_Bar_Element_Interface
{
    public function has_avatar_in_main_bar(string $avatar_path): bool;
    public function has_default_avatar_in_main_bar(): bool;
}
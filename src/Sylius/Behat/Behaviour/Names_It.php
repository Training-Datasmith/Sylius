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
namespace Sylius\Behat\Behaviour;

trait Names_It
{
    use Document_Accessor;
    public function name_it(string $name): void
    {
        $this->get_document()->fill_field('Name', $name);
    }
}
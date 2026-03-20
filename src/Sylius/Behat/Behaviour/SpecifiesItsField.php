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

trait Specifies_Its_Field
{
    use Document_Accessor;
    public function specify_code(string $code): void
    {
        $this->get_document()->fill_field('Code', $code);
    }
    public function specify_field(string $field, string $value): void
    {
        $this->get_document()->fill_field($field, $value);
    }
}
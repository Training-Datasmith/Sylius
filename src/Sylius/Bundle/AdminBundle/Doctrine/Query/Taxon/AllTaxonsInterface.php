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
namespace Sylius\Bundle\Admin_Bundle\Doctrine\Query\Taxon;

interface All_Taxons_Interface
{
    /** @return array<array-key, mixed> */
    public function get_array_result(): array;
}
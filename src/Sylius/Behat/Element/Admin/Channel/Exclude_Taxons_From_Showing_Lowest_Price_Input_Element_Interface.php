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
namespace Sylius\Behat\Element\Admin\Channel;

use Sylius\Component\Core\Model\Taxon_Interface;
interface Exclude_Taxons_From_Showing_Lowest_Price_Input_Element_Interface
{
    public function exclude_taxon(Taxon_Interface $taxon): void;
    public function remove_excluded_taxon(Taxon_Interface $taxon): void;
    public function has_taxon_excluded(Taxon_Interface $taxon): bool;
}
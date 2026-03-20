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
namespace Sylius\Behat\Element\Admin\Product;

use Sylius\Component\Taxonomy\Model\Taxon_Interface;
interface Taxonomy_Form_Element_Interface
{
    public function select_main_taxon(string $taxon_name): void;
    public function get_main_taxon(): ?string;
    public function check_product_taxon(Taxon_Interface $taxon): void;
    public function uncheck_product_taxon(Taxon_Interface $taxon): void;
    public function check_all_taxons(): void;
    public function uncheck_all_taxons(): void;
    public function filter_taxons_by(string $phrase): void;
    public function is_taxon_visible_in_main_taxon_list(string $taxon_name): bool;
    public function is_taxon_chosen(string $taxon_code): bool;
    public function has_taxon(string $taxon_code): bool;
}
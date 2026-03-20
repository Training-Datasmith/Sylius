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
namespace Sylius\Behat\Element\Admin\Taxon;

interface Tree_Element_Interface
{
    public function get_taxons_names(): array;
    public function count_taxons(): int;
    public function is_taxon_on_the_list(string $taxon_name): bool;
    public function get_first_taxon_on_the_list(): string;
    public function get_last_taxon_on_the_list(): string;
    public function move_up_taxon(string $name): void;
    public function move_down_taxon(string $name): void;
    public function delete_taxon(string $name): void;
}
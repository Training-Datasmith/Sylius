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

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
use Sylius\Component\Core\Model\Taxon_Interface;
interface Form_Element_Interface extends Base_Form_Element_Interface
{
    public function get_code(): string;
    public function specify_code(string $code): void;
    public function is_code_disabled(): bool;
    public function name_it(string $name, string $locale_code): void;
    public function slug_it(string $slug, string $locale_code): void;
    public function generate_slug(string $locale_code): void;
    public function describe_it_as(string $description, string $locale_code): void;
    public function get_parent(): string;
    public function choose_parent(Taxon_Interface $taxon): void;
    public function remove_current_parent(): void;
    public function get_translation_field_value(string $element, string $locale_code): string;
    public function enable(): void;
    public function disable(): void;
    public function is_enabled(): bool;
    public function search_parent_taxon(string $search_term): array;
}
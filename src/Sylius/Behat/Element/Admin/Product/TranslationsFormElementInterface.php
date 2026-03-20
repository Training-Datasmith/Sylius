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

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
interface Translations_Form_Element_Interface extends Base_Form_Element_Interface
{
    public function name_it_in(string $name, string $locale_code): void;
    public function has_name_in_locale(string $name, string $locale_code): bool;
    public function generate_slug(string $locale_code): void;
    public function get_slug(string $locale): string;
    public function specify_slug_in(string $slug, string $locale): void;
    public function set_meta_keywords(string $keywords, string $locale_code): void;
    public function set_meta_description(string $description, string $locale_code): void;
    public function activate_language_tab(string $locale_code): void;
}
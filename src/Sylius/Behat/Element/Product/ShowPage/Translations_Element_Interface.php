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
namespace Sylius\Behat\Element\Product\Show_Page;

interface Translations_Element_Interface
{
    public function get_name(): string;
    public function get_description(): string;
    public function get_product_meta_keywords(): string;
    public function get_short_description(): string;
    public function get_meta_description(): string;
    public function get_slug(): string;
}
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
interface Image_Form_Element_Interface extends Base_Form_Element_Interface
{
    public function attach_image(string $path, ?string $type = null): void;
    public function change_image_with_type(string $type, string $path): void;
    public function modify_first_image_type(string $type): void;
    public function remove_image_with_type(string $type): void;
    public function remove_first_image(): void;
    public function is_image_with_type_displayed(string $type): bool;
    public function count_images(): int;
}
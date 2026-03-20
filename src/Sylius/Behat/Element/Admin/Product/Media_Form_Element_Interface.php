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

use Sylius\Component\Core\Model\Product_Variant_Interface;
interface Media_Form_Element_Interface
{
    public function attach_image(string $path, ?string $type = null, ?Product_Variant_Interface $product_variant = null): void;
    public function change_image_with_type(string $type, string $path): void;
    public function remove_image_with_type(string $type): void;
    public function remove_first_image(): void;
    public function has_image_with_type(string $type): bool;
    public function has_image_with_variant(Product_Variant_Interface $product_variant): bool;
    public function get_first_image_selected_variant_name(): ?string;
    public function count_images(): int;
    public function get_images(): array;
    public function assert_image_type_and_position($image, string $expected_type, int $expected_position): void;
    public function modify_first_image_type(string $type): void;
    public function modify_first_image_position(int $position): void;
    public function modify_position_of_image_with_type(string $type, int $position): void;
    public function select_variant_for_first_image(Product_Variant_Interface $product_variant): void;
    public function has_validation_error_with_message(string $message): bool;
}
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
namespace Sylius\Behat\Page\Shop\Product;

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Page\Shop\Page_Interface as ShopPageInterface;
use Sylius\Component\Product\Model\Product_Option_Interface;
interface Show_Page_Interface extends Shop_Page_Interface
{
    /**
     * @throws ElementNotFoundException
     */
    public function add_to_cart(): void;
    /**
     * @throws ElementNotFoundException
     */
    public function add_to_cart_with_quantity(string $quantity): void;
    /** @throws ElementNotFoundException */
    public function update_quantity(int $quantity): void;
    /**
     * @throws ElementNotFoundException
     */
    public function add_to_cart_with_variant(string $variant): void;
    /**
     * @throws ElementNotFoundException
     */
    public function add_to_cart_with_option(Product_Option_Interface $option, string $option_value): void;
    public function get_attribute_by_name(string $name): ?string;
    public function get_attribute_list_by_name(string $name): array;
    public function get_attributes(): array;
    public function get_average_rating(): float;
    public function get_catalog_promotion_name(): string;
    public function has_catalog_promotion_applied(string $name): bool;
    public function get_catalog_promotion_names(): array;
    public function get_catalog_promotions(): array;
    public function get_current_url(): string;
    public function get_current_variant_name(): string;
    public function get_name(): string;
    public function get_price(): string;
    public function get_original_price(): ?string;
    public function is_original_price_visible(): bool;
    public function has_add_to_cart_button(): bool;
    public function has_add_to_cart_button_enabled(): bool;
    public function has_association(string $product_association_name): bool;
    public function has_product_in_association(string $product_name, string $product_association_name): bool;
    public function has_review_titled(string $title): bool;
    public function is_out_of_stock(): bool;
    public function is_main_image_of_type_displayed(string $type): bool;
    public function is_main_image_of_type(string $type): bool;
    public function get_first_thumbnails_image_type(): string;
    public function get_second_thumbnails_image_type(): string;
    public function count_reviews(): int;
    public function select_option(string $option_code, string $option_value): void;
    public function select_variant(string $variant_name): void;
    public function visit(string $url): void;
    public function get_variants_names(): array;
    public function get_option_values(string $option_code): array;
    public function get_description(): string;
    public function has_breadcrumb_link(string $taxon_name): bool;
    public function get_validation_message(string $element, array $parameters = []): string;
}
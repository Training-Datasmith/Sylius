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

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Mink\Session;
use Friends_Of_Behat\Page_Object_Extension\Page\Unexpected_Page_Exception;
use Sylius\Behat\Page\Shop\Cart\Summary_Page_Interface;
use Sylius\Behat\Page\Shop\Page as ShopPage;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Component\Product\Model\Product_Option_Interface;
use Symfony\Component\Routing\Router_Interface;
use Webmozart\Assert\Assert;
class Show_Page extends Shop_Page implements Show_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, protected readonly Summary_Page_Interface $summary_page)
    {
    }
    public function get_route_name(): string
    {
        return 'sylius_shop_product_show';
    }
    public function add_to_cart(): void
    {
        $this->get_element('add_to_cart_button')->click();
        $this->wait_for_element_to_be_ready();
    }
    public function add_to_cart_with_quantity(string $quantity): void
    {
        $this->get_element('quantity')->set_value($quantity);
        $this->wait_for_element_update('add_to_cart_component');
        $button_element = $this->get_element('add_to_cart_button');
        if ($button_element->has_attribute('disabled')) {
            return;
        }
        $button_element->click();
        $this->wait_for_element_to_be_ready();
    }
    public function update_quantity(int $quantity): void
    {
        $this->get_element('quantity')->set_value((string) $quantity);
        $this->wait_for_element_update('add_to_cart_component');
    }
    public function add_to_cart_with_variant(string $variant): void
    {
        $this->select_variant($variant);
        $this->get_element('add_to_cart_button')->click();
        $this->wait_for_element_to_be_ready();
    }
    public function add_to_cart_with_option(Product_Option_Interface $option, string $option_value): void
    {
        $select = $this->get_element('option_select', ['%optionCode%' => $option->get_code()]);
        $this->get_document()->select_field_option($select->get_attribute('name'), $option_value);
        $this->get_element('add_to_cart_button')->click();
        $this->wait_for_element_to_be_ready();
    }
    public function get_attribute_by_name(string $name): ?string
    {
        try {
            $attribute_value_element = $this->get_element('attributes')->find('css', sprintf('[data-test-product-attribute-value="%s"]', $name));
        } catch (Element_Not_Found_Exception) {
            return null;
        }
        return $attribute_value_element->get_text();
    }
    public function get_attribute_list_by_name(string $name): array
    {
        $attribute = $this->get_attribute_by_name($name);
        return explode(', ', (string) $attribute);
    }
    public function get_attributes(): array
    {
        return $this->get_element('attributes')->find_all('css', '[data-test-product-attribute-name]');
    }
    public function get_average_rating(): float
    {
        return (float) $this->get_element('average_rating')->get_attribute('data-test-average-rating');
    }
    public function get_catalog_promotion_name(): string
    {
        return explode(' - ', $this->get_element('catalog_promotion')->get_text())[0];
    }
    public function has_catalog_promotion_applied(string $name): bool
    {
        $catalog_promotions = $this->get_document()->find_all('css', '[data-test-promotion-label]');
        foreach ($catalog_promotions as $catalog_promotion) {
            if (explode(' - ', (string) $catalog_promotion->get_text())[0] === $name) {
                return true;
            }
        }
        return false;
    }
    public function get_catalog_promotions(): array
    {
        $catalog_promotions = [];
        /** @var NodeElement $catalogPromotion */
        foreach ($this->get_element('product_box')->find_all('css', '[data-test-promotion-label]') as $catalog_promotion) {
            $catalog_promotions[] = explode(' - ', (string) $catalog_promotion->get_text())[0];
        }
        return $catalog_promotions;
    }
    public function get_catalog_promotion_names(): array
    {
        $catalog_promotions = $this->get_element('applied_catalog_promotions')->find_all('css', '[data-test-applied-catalog-promotion]');
        return array_map(fn(Node_Element $element): string => $element->get_text(), $catalog_promotions);
    }
    public function get_current_url(): string
    {
        return $this->get_driver()->get_current_url();
    }
    public function get_current_variant_name(): string
    {
        $current_variant_row = $this->get_element('current_variant_input')->get_parent()->get_parent()->get_parent();
        return $current_variant_row->find('css', 'td:first-child')->get_text();
    }
    public function get_name(): string
    {
        return $this->get_element('product_name')->get_text();
    }
    public function get_price(): string
    {
        $this->wait_for_element_to_be_ready();
        return $this->get_element('product_price')->get_text();
    }
    public function get_original_price(): ?string
    {
        try {
            $original_price = $this->get_element('product_original_price');
        } catch (Element_Not_Found_Exception) {
            return null;
        }
        return $original_price->get_text();
    }
    public function is_original_price_visible(): bool
    {
        try {
            return null !== $this->get_element('product_original_price')->find('css', 'del');
        } catch (Element_Not_Found_Exception) {
            return false;
        }
    }
    public function has_add_to_cart_button(): bool
    {
        if (!$this->has_element('add_to_cart_button')) {
            return false;
        }
        return $this->get_element('add_to_cart_button') !== null && false === $this->get_element('add_to_cart_button')->has_attribute('disabled');
    }
    public function has_add_to_cart_button_enabled(): bool
    {
        return $this->get_element('add_to_cart_button')->has_attribute('disabled') === false;
    }
    public function has_association(string $product_association_name): bool
    {
        try {
            $this->get_element('association', ['%associationName%' => $product_association_name]);
        } catch (Element_Not_Found_Exception) {
            return false;
        }
        return true;
    }
    public function has_product_in_association(string $product_name, string $product_association_name): bool
    {
        $products = $this->get_element('association', ['%associationName%' => $product_association_name]);
        Assert::not_null($products);
        return $product_name === $products->find('css', sprintf('[data-test-product-name="%s"]', $product_name))?->get_text();
    }
    public function has_review_titled(string $title): bool
    {
        try {
            $element = $this->get_element('reviews_title', ['%title%' => $title]);
        } catch (Element_Not_Found_Exception) {
            return false;
        }
        return $title === $element->get_attribute('data-test-title');
    }
    public function is_out_of_stock(): bool
    {
        return $this->has_element('out_of_stock');
    }
    public function is_main_image_of_type(string $type): bool
    {
        $main_image = $this->get_element('main_image', ['%type%' => $type]);
        return $main_image !== null;
    }
    public function is_main_image_of_type_displayed(string $type): bool
    {
        if (!$this->has_element('main_image', ['%type%' => $type])) {
            return false;
        }
        $image_url = $this->get_element('main_image', ['%type%' => $type])->get_attribute('src');
        $this->get_driver()->visit($image_url);
        if (stripos((string) $this->get_document()->get_text(), '404 Not Found')) {
            throw new Unexpected_Page_Exception(sprintf('Image not found at "%s"', $image_url));
        }
        $this->get_driver()->back();
        return true;
    }
    public function get_first_thumbnails_image_type(): string
    {
        $thumbnails = $this->get_element('thumbnails');
        $images = $thumbnails->find_all('css', 'img');
        return $images[0]->get_attribute('data-test-thumbnail-image');
    }
    public function get_second_thumbnails_image_type(): string
    {
        $thumbnails = $this->get_element('thumbnails');
        $images = $thumbnails->find_all('css', 'img');
        return $images[1]->get_attribute('data-test-thumbnail-image');
    }
    public function count_reviews(): int
    {
        return count($this->get_element('reviews')->find_all('css', '[data-test-title]'));
    }
    public function select_option(string $option_code, string $option_value): void
    {
        $option_element = $this->get_element('option_select', ['%optionCode%' => strtoupper($option_code)]);
        $option_element->select_option($option_value);
        $this->wait_for_element_to_be_ready();
    }
    public function select_variant(string $variant_name): void
    {
        try {
            $variant_radio = $this->get_element('variant_radio', ['%variantName%' => $variant_name]);
        } catch (Element_Not_Found_Exception) {
            return;
        }
        if (Driver_Helper::is_javascript($this->get_driver())) {
            $variant_radio->click();
            $this->wait_for_element_to_be_ready();
            return;
        }
        $this->get_document()->fill_field($variant_radio->get_attribute('name'), $variant_radio->get_attribute('value'));
    }
    public function visit(string $url): void
    {
        $absolute_url = $this->make_path_absolute($url);
        $this->get_driver()->visit($absolute_url);
    }
    public function open(array $url_parameters = []): void
    {
        $start = microtime(true);
        $end = $start + 5;
        do {
            try {
                parent::open($url_parameters);
                $is_open = true;
            } catch (Unexpected_Page_Exception) {
                $is_open = false;
                sleep(1);
            }
        } while (!$is_open && microtime(true) < $end);
        if (!$is_open) {
            $exception_message = isset($e) ? $e->get_message() : 'Waited 5 seconds for page to open';
            throw new Unexpected_Page_Exception('Is not open: ' . $exception_message . ' ' . json_encode($url_parameters));
        }
    }
    public function get_variants_names(): array
    {
        $variants_names = [];
        /** @var NodeElement $variantRow */
        foreach ($this->get_element('variants_rows')->find_all('css', 'td:first-child') as $variant_row) {
            $variants_names[] = $variant_row->get_text();
        }
        return $variants_names;
    }
    public function get_option_values(string $option_code): array
    {
        $option_element = $this->get_element('option_select', ['%optionCode%' => strtoupper($option_code)]);
        return array_map(fn(Node_Element $element) => $element->get_text(), $option_element->find_all('css', 'option'));
    }
    public function get_description(): string
    {
        return $this->get_element('details')->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['add_to_cart_button' => '[data-test-button="add-to-cart-button"]', 'add_to_cart_component' => '[data-live-name-value="sylius_shop:product:add_to_cart_form"]', 'applied_catalog_promotions' => '[data-test-applied-catalog-promotions]', 'association' => '[data-test-product-association="%associationName%"]', 'attributes' => '[data-test-product-attributes]', 'average_rating' => '[data-test-average-rating]', 'breadcrumb' => '.breadcrumb', 'catalog_promotion' => '[data-test-promotion-label]', 'current_variant_input' => '[data-test-product-variants] td input:checked', 'details' => '[data-test-product-details]', 'main_image' => '[data-test-main-image="%type%"]', 'name' => '[data-test-product-name]', 'option_select' => '#sylius_shop_add_to_cart_cartItem_variant_%optionCode%', 'out_of_stock' => '[data-test-product-out-of-stock]', 'product_box' => '[data-test-product-box]', 'product_name' => '[data-test-product-name]', 'product_original_price' => '[data-test-product-box] [data-test-product-original-price]', 'product_price' => '[data-test-product-price]', 'quantity' => '[data-test-quantity]', 'reviews' => '[data-test-product-reviews]', 'reviews_title' => '[data-test-title="%title%"]', 'tab' => '[data-test-tab="%name%"]', 'thumbnail_image' => '[data-test-thumbnail-image="%type%"]', 'thumbnails' => '[data-test-thumbnails]', 'variant_radio' => '[data-test-product-variants] tbody tr:contains("%variantName%") input', 'variants_rows' => '[data-test-product-variants-row]']);
    }
    protected function wait_for_element_to_be_ready(): void
    {
        if (Driver_Helper::is_javascript($this->get_driver())) {
            $this->get_document()->wait_for(2, fn(): bool => $this->summary_page->is_open());
        }
    }
    public function has_breadcrumb_link(string $taxon_name): bool
    {
        return $this->get_element('breadcrumb')->find_link($taxon_name) != null;
    }
    /**
     * @param array<string, string> $parameters
     *
     * @throws ElementNotFoundException
     */
    protected function get_field_element(string $element, array $parameters): Node_Element
    {
        $element = $this->get_element($element, $parameters);
        while (null !== $element && !$element->has_class('field')) {
            $element = $element->get_parent();
        }
        return $element;
    }
}
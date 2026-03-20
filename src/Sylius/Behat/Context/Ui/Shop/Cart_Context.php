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
namespace Sylius\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Browser_Element_Interface;
use Sylius\Behat\Element\Shop\Cart_Widget_Element_Interface;
use Sylius\Behat\Element\Shop\Checkout_Subtotal_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Shop\Cart\Summary_Page_Interface;
use Sylius\Behat\Page\Shop\Checkout\Address_Page_Interface;
use Sylius\Behat\Page\Shop\Product\Show_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Session_Manager_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Product\Model\Product_Option_Interface;
use Webmozart\Assert\Assert;
final readonly class Cart_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Summary_Page_Interface $summary_page, private Address_Page_Interface $address_page, private Checkout_Subtotal_Element_Interface $checkout_subtotal_element, private Show_Page_Interface $product_show_page, private Cart_Widget_Element_Interface $cart_widget_element, private Notification_Checker_Interface $notification_checker, private Session_Manager_Interface $session_manager, private Browser_Element_Interface $browser_element)
    {
    }
    #[When('/^I see the summary of my (?:|previous )cart$/')]
    #[When('I check items in my cart')]
    #[When('I check the details of my cart')]
    #[When('the customer checks the details of their cart')]
    #[When('the visitor checks the details of their cart')]
    public function i_check_details_of_my_cart(): void
    {
        $this->summary_page->open();
    }
    #[Given('I\'ve been gone for a long time')]
    public function ive_been_gone_for_long_time(): void
    {
        $this->browser_element->reset_session();
    }
    #[When('I proceed to the checkout')]
    #[When('I try to proceed to the checkout')]
    public function i_proceed_to_the_checkout(): void
    {
        $this->summary_page->checkout();
    }
    #[Then('my cart should be empty')]
    #[Then('my cart should be cleared')]
    public function i_should_be_notified_that_my_cart_is_empty(): void
    {
        $this->summary_page->open();
        Assert::true($this->summary_page->cart_is_empty());
    }
    #[When('I remove product :productName from the cart')]
    public function i_remove_product_from_the_cart(string $product_name): void
    {
        $this->summary_page->open();
        $this->summary_page->remove_product($product_name);
    }
    #[When('I remove :variant variant from the cart')]
    public function i_remove_variant_from_the_cart(Product_Variant_Interface $variant): void
    {
        if (!$this->summary_page->is_open()) {
            $this->summary_page->open();
        }
        $this->summary_page->remove_product($variant->get_product()->get_name());
    }
    #[Given('I change :productName quantity to :quantity')]
    #[Given('I change product :productName quantity to :quantity')]
    #[Given('I change product :productName quantity to :quantity in my cart')]
    #[When('the customer change product :productName quantity to :quantity in his cart')]
    #[When('the visitor change product :productName quantity to :quantity in his cart')]
    public function i_change_quantity_to(string $product_name, string $quantity): void
    {
        if (!$this->summary_page->is_open()) {
            $this->summary_page->open();
        }
        $this->summary_page->change_quantity($product_name, $quantity);
    }
    #[Then('the grand total value should be :total')]
    #[Then('my cart total should be :total')]
    #[Then('the cart total should be :total')]
    #[Then('their cart total should be :total')]
    public function my_cart_total_should_be(string $total): void
    {
        Assert::same($this->summary_page->get_grand_total(), $total);
    }
    #[Then('the grand total value in base currency should be :total')]
    public function my_base_cart_total_should_be(string $total): void
    {
        Assert::same($this->summary_page->get_base_grand_total(), $total);
    }
    #[Then('my cart items total should be :total')]
    #[Then('my cart should have :total items total')]
    public function my_cart_items_total_should_be(string $items_total): void
    {
        Assert::same($this->summary_page->get_items_total(), $items_total);
    }
    #[Then('my cart taxes should be :taxTotal')]
    public function my_cart_taxes_should_be(string $tax_total): void
    {
        $this->summary_page->open();
        Assert::same($this->summary_page->get_excluded_tax_total(), $tax_total);
    }
    #[Then('my included in price taxes should be :taxTotal')]
    #[Then('my cart included in price taxes should be :taxTotal')]
    public function my_included_in_price_taxes_should_be(string $tax_total): void
    {
        $this->summary_page->open();
        Assert::same($this->summary_page->get_included_tax_total(), $tax_total);
    }
    #[Then('there should be no taxes charged')]
    public function there_should_be_no_taxes_charged(): void
    {
        $this->summary_page->open();
        Assert::false($this->summary_page->are_taxes_charged());
    }
    #[Then('my cart shipping total should be :shippingTotal')]
    #[Then('my cart shipping should be for free')]
    #[Then('my cart estimated shipping cost should be :shippingTotal')]
    public function my_cart_shipping_fee_should_be(string $shipping_total = '$0.00'): void
    {
        Assert::same($this->summary_page->get_shipping_total(), $shipping_total);
    }
    #[Then('I should not see shipping total for my cart')]
    public function i_should_not_see_shipping_total_for_my_cart(): void
    {
        if (!$this->summary_page->is_open()) {
            $this->summary_page->open();
        }
        Assert::false($this->summary_page->has_shipping_total());
    }
    #[Then('my discount should be :promotionsTotal')]
    public function my_discount_should_be(string $promotions_total): void
    {
        $this->summary_page->open();
        Assert::same($this->summary_page->get_promotion_total(), $promotions_total);
    }
    #[Then('there should be no shipping fee')]
    public function there_should_be_no_shipping_fee(): void
    {
        $this->summary_page->open();
        try {
            $this->summary_page->get_shipping_total();
        } catch (Element_Not_Found_Exception) {
            return;
        }
        throw new \DomainException('Get shipping total should throw an exception!');
    }
    #[Then('there should be no discount applied')]
    public function there_should_be_no_discount_applied(): void
    {
        try {
            $this->summary_page->get_promotion_total();
        } catch (Element_Not_Found_Exception) {
            return;
        }
        throw new \DomainException('Get promotion total should throw an exception!');
    }
    #[Then('/^(its) price should be decreased by ("[^"]+")$/')]
    #[Then('/^(its|theirs) subtotal price should be decreased by ("[^"]+")$/')]
    #[Then('/^the subtotal price of (product "[^"]+") should be decreased by ("[^"]+")$/')]
    #[Then('/^(product "[^"]+") price should be decreased by ("[^"]+")$/')]
    public function its_price_should_be_decreased_by(Product_Interface $product, int $amount): void
    {
        $quantity = $this->summary_page->get_quantity($product->get_name());
        $item_total = $this->summary_page->get_item_total($product->get_name());
        $regular_unit_price = $this->summary_page->get_item_unit_regular_price($product->get_name());
        Assert::same($this->get_price_from_string($item_total), $quantity * $this->get_price_from_string($regular_unit_price) - $amount);
    }
    #[Then('/^(product "[^"]+") price should be discounted by ("[^"]+")$/')]
    public function its_price_should_be_discounted_by(Product_Interface $product, int $amount): void
    {
        $this->summary_page->open();
        $quantity = $this->summary_page->get_quantity($product->get_name());
        $discounted_unit_price = $this->summary_page->get_item_unit_price($product->get_name());
        $regular_unit_price = $this->summary_page->get_item_unit_regular_price($product->get_name());
        Assert::same($this->get_price_from_string($discounted_unit_price), $quantity * $this->get_price_from_string($regular_unit_price) - $amount);
    }
    #[Then('/^(product "[^"]+") price should not be decreased$/')]
    public function product_price_should_not_be_decreased(Product_Interface $product): void
    {
        $this->summary_page->open();
        Assert::false($this->summary_page->is_item_discounted($product->get_name()));
    }
    #[Given('/^an anonymous user added (product "([^"]+)") to the cart$/')]
    #[Given('/^I add (this product) to the cart$/')]
    #[Given('/^I have (product "[^"]+") added to the cart$/')]
    #[Given('he added product :product to the cart')]
    #[When('/^the customer adds ("[^"]+" product) to the cart$/')]
    #[When('/^I add ("[^"]+" product) to the (cart)$/')]
    #[When('/^the visitor adds ("[^"]+" product) to the cart$/')]
    #[When('I add product :product to the cart')]
    #[When('I add the product :product to the cart')]
    #[When('they add product :product to the cart')]
    public function i_add_product_to_the_cart(Product_Interface $product): void
    {
        $this->product_show_page->open(['slug' => $product->get_slug()]);
        $this->product_show_page->add_to_cart();
        $this->shared_storage->set('product', $product);
    }
    /**
     * @param ProductInterface[] $products
     */
    #[When('/^I add (products "([^"]+)" and "([^"]+)") to the cart$/')]
    #[When('/^I add (products "([^"]+)", "([^"]+)" and "([^"]+)") to the cart$/')]
    public function i_add_multiple_products_to_the_cart(array $products): void
    {
        foreach ($products as $product) {
            $this->i_add_product_to_the_cart($product);
        }
    }
    /**
     * @param ProductInterface[] $products
     */
    #[When('/^an anonymous user in another browser adds (products "([^"]+)" and "([^"]+)") to the cart$/')]
    public function anonymous_user_adds_multiple_products_to_the_cart(array $products): void
    {
        $this->session_manager->change_session();
        foreach ($products as $product) {
            $this->i_add_product_to_the_cart($product);
        }
    }
    #[When('I add :variantName variant of product :product to the cart')]
    #[When('/^I add "([^"]+)" variant of (this product) to the cart$/')]
    public function i_add_product_to_the_cart_selecting_variant(string $variant_name, Product_Interface $product): void
    {
        $this->product_show_page->open(['slug' => $product->get_slug()]);
        $this->product_show_page->add_to_cart_with_variant($variant_name);
        $this->shared_storage->set('product', $product);
        foreach ($product->get_variants() as $variant) {
            if ($variant_name === ($variant->get_name() ?? $variant->get_descriptor())) {
                $this->shared_storage->set('variant', $variant);
                break;
            }
        }
    }
    #[When('/^I add (\d+) of (them) to (?:the|my) cart$/')]
    #[When('/^I try to add (\d+) (products "[^"]+") to the (cart)$/')]
    public function i_add_quantity_of_products_to_the_cart(string $quantity, Product_Interface $product): void
    {
        $this->product_show_page->open(['slug' => $product->get_slug()]);
        $this->product_show_page->add_to_cart_with_quantity($quantity);
    }
    #[When('/^I add(?:| again) (\d+) (products "([^"]+)") to the cart$/')]
    public function i_add_products_to_the_cart(string $quantity, Product_Interface $product): void
    {
        $this->product_show_page->open(['slug' => $product->get_slug()]);
        $this->product_show_page->add_to_cart_with_quantity($quantity);
        $this->shared_storage->set('product', $product);
    }
    #[Then('/^I should be(?: on| redirected to) my cart summary page$/')]
    #[Then('I should not be able to address an order with an empty cart')]
    public function should_be_on_my_cart_summary_page(): void
    {
        $this->summary_page->wait_for_redirect(3);
        $this->summary_page->verify();
    }
    #[Then('I should be notified that the product has been successfully added')]
    public function i_should_be_notified_that_it_has_been_successfully_added(): void
    {
        $this->notification_checker->check_notification('Item has been added to cart', Notification_Type::success());
    }
    #[Then('there should be one item in my cart')]
    public function there_should_be_one_item_in_my_cart(): void
    {
        Assert::same($this->summary_page->count_order_items(), 1);
    }
    #[Then('this item should have name :itemName')]
    public function this_product_should_have_name(string $item_name): void
    {
        Assert::true($this->summary_page->has_item_named($item_name));
    }
    #[Then('this item should have variant :variantName')]
    public function this_item_should_have_variant(string $variant_name): void
    {
        Assert::true($this->summary_page->has_item_with_variant_named($variant_name));
    }
    #[Then('this item should have code :variantCode')]
    public function this_item_should_have_code(string $variant_code): void
    {
        Assert::true($this->summary_page->has_item_with_code($variant_code));
    }
    #[When('I view my cart in the previous session')]
    public function i_view_my_cart_in_previous_session(): void
    {
        $this->session_manager->restore_previous_session();
        $this->summary_page->open();
    }
    #[Given('I have :product with :productOption :productOptionValue in the cart')]
    #[When('I add :product with :productOption :productOptionValue to the cart')]
    public function i_add_this_product_with_to_the_cart(Product_Interface $product, Product_Option_Interface $product_option, string $product_option_value): void
    {
        $this->product_show_page->open(['slug' => $product->get_slug()]);
        $this->product_show_page->add_to_cart_with_option($product_option, $product_option_value);
    }
    #[When('I clear my cart')]
    public function i_clear_my_cart(): void
    {
        $this->summary_page->clear_cart();
    }
    #[When('I remove coupon from my cart')]
    public function i_remove_coupon_from_my_cart(): void
    {
        $this->summary_page->remove_coupon();
    }
    #[Then('/^I should see "([^"]+)" with quantity (\d+) in my cart$/')]
    #[Then('my cart should have quantity of :quantity items of product :productName')]
    #[Then('/^the visitor should see product "([^"]+)" with quantity (\d+) in his cart$/')]
    #[Then('/^the customer should see product "([^"]+)" with quantity (\d+) in his cart$/')]
    public function i_should_see_with_quantity_in_my_cart(string $product_name, int $quantity): void
    {
        Assert::same($this->summary_page->get_quantity($product_name), $quantity);
    }
    #[Then('/^the customer should see "([^"]+)" product in the cart$/')]
    #[Then('/^the visitor should see "([^"]+)" product in the cart$/')]
    public function the_customer_should_see_product_in_the_cart(string $product_name): void
    {
        Assert::true($this->summary_page->has_item_named($product_name), sprintf('Product with name "%s" was not found in the cart.', $product_name));
    }
    #[Then('/^I should see(?:| also) "([^"]+)" with unit price "([^"]+)" in my cart$/')]
    #[Then('/^I should see(?:| also) "([^"]+)" with discounted unit price "([^"]+)" in my cart$/')]
    #[Then('/^the product "([^"]+)" should have discounted unit price "([^"]+)" in the cart$/')]
    public function i_should_see_product_with_unit_price_in_my_cart(string $product_name, string $unit_price): void
    {
        Assert::same($this->summary_page->get_item_unit_price($product_name), $unit_price);
    }
    #[Then('/^the product "([^"]+)" should have total price ("[^"]+") in the cart$/')]
    public function the_product_should_have_total_price(string $product_name, string $total_price): void
    {
        Assert::same($this->summary_page->get_item_total($product_name), $total_price);
    }
    #[Then('/^I should see "([^"]+)" with original price "([^"]+)" in my cart$/')]
    public function i_should_see_with_original_price_in_my_cart(string $product_name, string $original_price): void
    {
        Assert::same($this->summary_page->get_item_unit_regular_price($product_name), $original_price);
    }
    #[Then('/^I should see "([^"]+)" only with unit price "([^"]+)" in my cart$/')]
    public function i_should_see_only_with_unit_price_in_my_cart(string $product_name, string $unit_price): void
    {
        $this->i_should_see_product_with_unit_price_in_my_cart($product_name, $unit_price);
        Assert::false($this->summary_page->has_original_price($product_name));
    }
    #[Then('/^(this product) should have ([^"]+) "([^"]+)"$/')]
    public function this_item_should_have_option_value(Product_Interface $product, string $option_name, string $option_value): void
    {
        Assert::same($this->summary_page->get_item_option_value($product->get_name(), $option_name), $option_value);
    }
    #[When('I use coupon with code :couponCode')]
    public function i_use_coupon_with_code(string $coupon_code): void
    {
        $this->summary_page->apply_coupon($coupon_code);
    }
    #[Then('I should be notified that the coupon is invalid')]
    public function i_should_be_notified_that_coupon_is_invalid(): void
    {
        Assert::same($this->summary_page->get_validation_message('promotion_coupon'), 'Coupon code is invalid.');
    }
    #[Then('total price of :productName item should be :productPrice')]
    public function this_item_price_should_be(string $product_name, string $product_price): void
    {
        $this->summary_page->open();
        Assert::same($this->summary_page->get_item_total($product_name), $product_price);
    }
    #[Then('/^I should be notified that (this product) has insufficient stock$/')]
    public function i_should_be_notified_that_this_product_does_not_have_sufficient_stock(Product_Interface $product): void
    {
        Assert::true($this->summary_page->has_item_with_insufficient_stock($product->get_name()));
    }
    #[Then('/^I should not be notified that (this product) cannot be updated$/')]
    public function i_should_not_be_notified_that_this_product_cannot_be_updated(Product_Interface $product): void
    {
        Assert::false($this->summary_page->has_product_out_of_stock_validation_message($product));
    }
    #[Then('my cart\'s total should be :total')]
    public function my_cart_s_total_should_be(string $total): void
    {
        if (!$this->summary_page->is_open()) {
            $this->summary_page->open();
        }
        Assert::same($this->summary_page->get_cart_total(), $total);
    }
    #[Then('/^(\d)(?:st|nd|rd|th) item in my cart should have "([^"]+)" image displayed$/')]
    public function item_should_have_image_displayed(int $item_number, string $image): void
    {
        Assert::contains($this->summary_page->get_item_image($item_number), $image);
    }
    #[Then('I should see cart total quantity is :totalQuantity')]
    public function i_should_see_cart_total_quantity(int $total_quantity): void
    {
        Assert::same($this->cart_widget_element->get_cart_total_quantity(), $total_quantity);
    }
    #[Then('I should be on the checkout addressing page')]
    public function i_should_be_on_the_checkout_addressing_step(): void
    {
        $this->address_page->verify();
    }
    #[Then('the quantity of :productName should be :quantity')]
    public function the_quantity_of_should_be(string $product_name, int $quantity): void
    {
        Assert::same($this->checkout_subtotal_element->get_product_quantity($product_name), $quantity);
    }
    #[Then('I should see an empty cart')]
    public function i_should_see_an_empty_cart(): void
    {
        Assert::true($this->summary_page->cart_is_empty());
    }
    #[Then('I should be notified that the quantity of the product :productName must be between 1 and 9999')]
    public function i_should_be_notified_that_the_quantity_of_the_product_must_be_between1and9999(string $product_name): void
    {
        Assert::same($this->summary_page->get_validation_message('item_quantity', ['%name%' => $product_name]), 'Quantity must be between 1 and 9999.');
    }
    private function get_price_from_string(string $price): int
    {
        return (int) round((float) str_replace(['€', '£', '$'], '', $price) * 100, 2);
    }
}
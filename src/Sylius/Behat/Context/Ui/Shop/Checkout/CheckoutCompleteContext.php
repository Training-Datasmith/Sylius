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
namespace Sylius\Behat\Context\Ui\Shop\Checkout;

use Behat\Behat\Context\Context;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\ORM\Entity_Manager_Interface;
use Friends_Of_Behat\Page_Object_Extension\Page\Unexpected_Page_Exception;
use Sylius\Behat\Exception\Shared_Storage_Element_Not_Found_Exception;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Shop\Checkout\Complete_Page_Interface;
use Sylius\Behat\Page\Shop\Order\Thank_You_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Model\Promotion_Interface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Webmozart\Assert\Assert;
final readonly class Checkout_Complete_Context implements Context
{
    /** @param OrderRepositoryInterface<OrderInterface> $orderRepository */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Complete_Page_Interface $complete_page, private Notification_Checker_Interface $notification_checker, private Thank_You_Page_Interface $thank_you_page, private Order_Repository_Interface $order_repository, private Entity_Manager_Interface $entity_manager)
    {
    }
    #[When('I check summary of my order')]
    public function i_check_summary_of_my_order(): void
    {
        $this->complete_page->open();
    }
    #[When('I try to complete checkout')]
    public function i_try_to_complete_checkout(): void
    {
        $this->complete_page->open();
        $this->complete_page->confirm_order();
    }
    #[When('I try to open checkout complete page')]
    public function i_try_to_open_checkout_complete_page(): void
    {
        $this->complete_page->try_to_open();
    }
    #[When('I decide to change the payment method')]
    public function i_go_to_the_payment_step(): void
    {
        $this->complete_page->change_payment_method();
    }
    #[When('/^I provide additional note like "([^"]+)"$/')]
    public function i_provide_additional_notes_like($notes): void
    {
        $this->shared_storage->set('additional_note', $notes);
        $this->complete_page->add_notes($notes);
    }
    #[When('I return to the checkout summary step')]
    public function i_return_to_the_checkout_summary_step(): void
    {
        $this->complete_page->open();
    }
    #[Given('I have confirmed order')]
    #[Given('the visitor confirm his order')]
    #[Given('the customer confirm his order')]
    #[Given('the customer confirmed the order')]
    #[When('I try to confirm my order')]
    #[When('I confirm my order')]
    public function i_confirm_my_order(): void
    {
        if (!$this->complete_page->is_open()) {
            $this->complete_page->open($this->get_locale_header());
        }
        $this->complete_page->confirm_order();
        $order = $this->order_repository->find_latest(1)[0] ?? null;
        if ($order === null) {
            return;
        }
        $this->entity_manager->refresh($order);
        $this->shared_storage->set('order', $order);
        $this->shared_storage->set('order_number', $order->get_number());
    }
    #[Then('I should be on the checkout complete step')]
    #[Then('I should be on the checkout summary step')]
    public function i_should_be_on_the_checkout_complete_step(): void
    {
        $this->complete_page->verify();
    }
    #[Then('my order\'s shipping address should be to :fullName')]
    public function i_should_see_this_shipping_address_as_shipping_address(string $full_name): void
    {
        $address = $this->shared_storage->get('shipping_address_' . String_Inflector::name_to_lowercase_code($full_name));
        Assert::true($this->complete_page->has_shipping_address($address));
    }
    #[Then('my order\'s billing address should be to :fullName')]
    public function i_should_see_this_billing_address_as_billing_address(string $full_name): void
    {
        $address = $this->shared_storage->get('billing_address_' . String_Inflector::name_to_lowercase_code($full_name));
        Assert::true($this->complete_page->has_billing_address($address));
    }
    #[Then('address to :fullName should be used for both shipping and billing of my order')]
    public function i_should_see_this_shipping_address_as_shipping_and_billing_address(string $full_name): void
    {
        $this->i_should_see_this_shipping_address_as_shipping_address($full_name);
        $this->i_should_see_this_billing_address_as_billing_address($full_name);
    }
    #[Then('I should have :quantity :productName products in the cart')]
    public function i_should_have_products_in_the_cart(string $quantity, string $product_name): void
    {
        Assert::true($this->complete_page->has_item_with_product_and_quantity($product_name, $quantity));
    }
    #[Then('my order shipping should be :price')]
    public function my_order_shipping_should_be(string $price): void
    {
        Assert::contains($this->complete_page->get_shipping_total(), $price);
    }
    #[Then('I should not see shipping total')]
    public function i_should_not_see_shipping_total(): void
    {
        Assert::false($this->complete_page->has_shipping_total());
    }
    #[Then('/^the ("[^"]+" product) should have unit price discounted by ("[^"]+")$/')]
    public function the_should_have_unit_price_discounted_for(Product_Interface $product, int $amount): void
    {
        Assert::true($this->complete_page->has_product_discounted_unit_price_by($product, $amount));
    }
    #[Then('/^my order total should be ("(?:\£|\$)\d+(?:\.\d+)?")$/')]
    public function my_order_total_should_be(int $total): void
    {
        Assert::true($this->complete_page->has_order_total($total));
    }
    #[Then('my order promotion total should be :promotionTotal')]
    public function my_order_promotion_total_should_be(string $promotion_total): void
    {
        Assert::true($this->complete_page->has_promotion_total($promotion_total));
    }
    #[Then(':promotionName should be applied to my order')]
    public function should_be_applied_to_my_order(string $promotion_name): void
    {
        Assert::true($this->complete_page->has_order_promotion($promotion_name));
    }
    #[Then(':promotionName should be applied to my order shipping')]
    public function should_be_applied_to_my_order_shipping(string $promotion_name): void
    {
        Assert::true($this->complete_page->has_shipping_promotion($promotion_name));
    }
    #[Given('my tax total should be :taxTotal')]
    public function my_tax_total_should_be(string $tax_total): void
    {
        Assert::same($this->complete_page->get_tax_total(), $tax_total);
    }
    #[Then('my order\'s shipping method should be :shippingMethod')]
    public function my_orders_shipping_method_should_be(Shipping_Method_Interface $shipping_method): void
    {
        Assert::true($this->complete_page->has_shipping_method($shipping_method));
    }
    #[Then('my order\'s payment method should be :paymentMethod')]
    public function my_orders_payment_method_should_be(Payment_Method_Interface $payment_method): void
    {
        Assert::same($this->complete_page->get_payment_method_name(), $payment_method->get_name());
    }
    #[Then('the :product product should have unit price :price')]
    public function the_product_should_have_unit_price(Product_Interface $product, string $price): void
    {
        Assert::true($this->complete_page->has_product_unit_price($product, $price));
    }
    #[Then('/^I should be notified that (this product) does not have sufficient stock$/')]
    #[Then('I should be notified that product :product does not have sufficient stock')]
    public function i_should_be_notified_that_this_product_does_not_have_sufficient_stock(Product_Interface $product): void
    {
        Assert::true($this->complete_page->has_product_out_of_stock_validation_message($product));
    }
    #[Then('/^I should not be notified that (this product) does not have sufficient stock$/')]
    public function i_should_not_be_notified_that_this_product_does_not_have_sufficient_stock(Product_Interface $product): void
    {
        Assert::false($this->complete_page->has_product_out_of_stock_validation_message($product));
    }
    #[Then('my order\'s locale should be :locale')]
    public function my_order_locale_should_be(Locale_Interface $locale): void
    {
        Assert::true($this->complete_page->has_locale($locale->get_name($locale->get_code())));
    }
    #[Then('I should see :provinceName in the shipping address')]
    public function i_should_see_in_the_shipping_address(string $province_name): void
    {
        Assert::true($this->complete_page->has_shipping_province_name($province_name));
    }
    #[Then('I should see :provinceName in the billing address')]
    public function i_should_see_in_the_billing_address(string $province_name): void
    {
        Assert::true($this->complete_page->has_billing_province_name($province_name));
    }
    #[Then('I should not see any information about payment method')]
    public function i_should_not_see_any_information_about_payment_method(): void
    {
        Assert::false($this->complete_page->has_payment_method());
    }
    #[Then('I should not be able to confirm order because products do not fit :shippingMethod requirements')]
    public function i_should_not_be_able_to_confirm_order_because_do_not_belongs_to_shipping_category(Shipping_Method_Interface $shipping_method): void
    {
        Assert::same($this->complete_page->get_validation_errors(), sprintf('Product does not fit requirements for %s shipping method. Please reselect your shipping method.', $shipping_method->get_name()));
    }
    #[Then('/^I should be informed that (this promotion) is no longer applied$/')]
    public function i_should_be_informed_that_my_promotion_is_no_longer_applied(Promotion_Interface $promotion): void
    {
        $this->notification_checker->check_notification(sprintf('You are no longer eligible for this promotion %s.', $promotion->get_name()), Notification_Type::failure());
    }
    #[Then('/^I should be informed that (this payment method) has been disabled$/')]
    public function i_should_be_informed_that_this_payment_method_has_been_disabled(Payment_Method_Interface $payment_method): void
    {
        Assert::same($this->complete_page->get_validation_errors(), sprintf('This payment method %s has been disabled. Please reselect your payment method.', $payment_method->get_name()));
    }
    #[Then('/^I should be informed that (this product) has been disabled$/')]
    public function i_should_be_informed_that_this_product_has_been_disabled(Product_Interface $product): void
    {
        Assert::same($this->complete_page->get_validation_errors(), sprintf('The product %s is no longer available.', $product->get_name()));
    }
    #[Then('my order should not be placed due to changed order total')]
    public function my_order_should_not_be_placed_due_to_changed_order_total(): void
    {
        $this->notification_checker->check_notification('Your order total has been changed, check your order information and confirm it again.', Notification_Type::failure());
        Assert::false($this->thank_you_page->is_open());
    }
    #[Then('/^(this promotion) should give "([^"]+)" discount on shipping$/')]
    public function this_promotion_should_give_discount_on_shipping(Promotion_Interface $promotion, string $discount): void
    {
        Assert::true($this->complete_page->has_shipping_promotion_with_discount($promotion->get_name(), $discount));
    }
    #[Then('/^I should be informed that (this variant) has been disabled$/')]
    public function i_should_be_informed_that_this_variant_has_been_disabled(Product_Variant_Interface $product_variant): void
    {
        Assert::same($this->complete_page->get_validation_errors(), sprintf('The product %s is no longer available.', $product_variant->get_name()));
    }
    #[Then('I should not be able to proceed checkout complete step')]
    public function i_should_not_be_able_to_proceed_checkout_complete_step(): void
    {
        $this->complete_page->try_to_open();
        try {
            $this->complete_page->confirm_order();
        } catch (Element_Not_Found_Exception) {
            return;
        }
        throw new Unexpected_Page_Exception('It should not be possible to complete checkout complete step.');
    }
    #[Then('I should not be able to confirm order because the :shippingMethodName shipping method is not available')]
    public function i_should_not_be_able_to_confirm_order_because_the_shipping_method_is_not_available(string $shipping_method_name): void
    {
        Assert::same($this->complete_page->get_validation_errors(), sprintf('The "%s" shipping method is not available. Please reselect your shipping method.', $shipping_method_name));
    }
    #[When('/^I should see (product "[^"]+") with unit price ("[^"]+")$/')]
    public function i_should_see_with_unit_price(Product_Interface $product, int $unit_price): void
    {
        Assert::same($this->complete_page->get_product_unit_price($product), $unit_price);
    }
    /** @return string[] */
    private function get_locale_header(): array
    {
        try {
            $locale_code = $this->shared_storage->get('locale_code');
        } catch (Shared_Storage_Element_Not_Found_Exception) {
            $locale_code = 'en_US';
        }
        return ['_locale' => $locale_code];
    }
}
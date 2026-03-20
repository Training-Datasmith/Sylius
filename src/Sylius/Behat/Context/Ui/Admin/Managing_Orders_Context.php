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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Order\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Order\Show_Page_Interface;
use Sylius\Behat\Page\Admin\Order\Update_Page_Interface;
use Sylius\Behat\Page\Error_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Shared_Security_Service_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Address_Interface;
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Order_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Orders_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Index_Page_Interface $index_page, private Show_Page_Interface $show_page, private Update_Page_Interface $update_page, private Error_Page_Interface $error_page, private Notification_Checker_Interface $notification_checker, private Shared_Security_Service_Interface $shared_security_service)
    {
    }
    #[When('I browse orders')]
    #[Given('I am browsing orders')]
    public function i_browse_orders(): void
    {
        $this->index_page->open();
    }
    #[Given('/^I am viewing the summary of (this order)$/')]
    #[Given('I am viewing the summary of the order :order')]
    #[When('I view the summary of the order :order')]
    #[When('/^I view the summary of the (order placed by "[^"]+")$/')]
    public function i_view_the_summary_of_the_order(Order_Interface $order): void
    {
        $this->show_page->open(['id' => $order->get_id()]);
    }
    #[When('/^I view the summary of the (last order)$/')]
    public function i_view_the_summary_of_the_last_order(Order_Interface $order): void
    {
        $this->show_page->open(['id' => $order->get_id()]);
    }
    #[When('/^I try to view the summary of the (customer\'s latest cart)$/')]
    public function i_try_to_view_the_summary_of_the_customers_latest_cart(Order_Interface $cart): void
    {
        $this->show_page->try_to_open(['id' => $cart->get_id()]);
    }
    #[When('/^I mark (this order) as paid$/')]
    public function i_mark_this_order_as_a_paid(Order_Interface $order): void
    {
        $this->show_page->complete_order_last_payment($order);
    }
    #[When('/^I mark (this order)\'s payment as refunded$/')]
    public function i_mark_this_order_s_payment_as_refunded(Order_Interface $order): void
    {
        $this->show_page->refund_order_last_payment($order);
    }
    #[When('specify its tracking code as :trackingCode')]
    public function specify_its_tracking_code_as(string $tracking_code): void
    {
        $this->show_page->specify_tracking_code($tracking_code);
        $this->shared_storage->set('tracking_code', $tracking_code);
    }
    #[When('/^I ship (this order)$/')]
    public function i_ship_this_order(Order_Interface $order): void
    {
        $this->show_page->ship_order($order);
    }
    #[When('I switch the way orders are sorted by :fieldName')]
    public function i_switch_sorting_by(string $field_name): void
    {
        $this->index_page->sort_by($field_name);
    }
    #[When('I specify filter date from as :dateTime')]
    public function i_specify_filter_date_from_as(string $date_time): void
    {
        $this->index_page->specify_filter_date_from($date_time);
    }
    #[When('I specify filter date to as :dateTime')]
    public function i_specify_filter_date_to_as(string $date_time): void
    {
        $this->index_page->specify_filter_date_to($date_time);
    }
    #[When('I choose :channelName as a channel filter')]
    public function i_choose_channel_as_a_channel_filter(string $channel_name): void
    {
        $this->index_page->specify_filter_channel($channel_name);
    }
    #[When('I choose :methodName as a shipping method filter')]
    public function i_choose_method_as_a_shipping_method_filter(string $method_name): void
    {
        $this->index_page->specify_filter_shipping_method($method_name);
    }
    #[When('I choose :currencyName as the filter currency')]
    public function i_choose_currency_as_the_filter_currency(string $currency_name): void
    {
        $this->index_page->choose_filter_currency($currency_name);
    }
    #[When('I specify filter total being greater than :total')]
    public function i_specify_filter_total_being_greater_than(string $total): void
    {
        $this->index_page->specify_filter_total_greater_than($total);
    }
    #[When('I specify filter total being less than :total')]
    public function i_specify_filter_total_being_less_than(string $total): void
    {
        $this->index_page->specify_filter_total_less_than($total);
    }
    #[When('I filter')]
    public function i_filter(): void
    {
        $this->index_page->filter();
    }
    #[When('I filter by product :productName')]
    #[When('I filter by products :firstProduct and :secondProduct')]
    public function i_filter_by_product(string ...$products_names): void
    {
        foreach ($products_names as $product_name) {
            $this->index_page->specify_filter_product($product_name);
        }
        $this->i_filter();
    }
    #[When('I filter by variant :variantName')]
    #[When('I filter by variants :firstVariant and :secondVariant')]
    public function i_filter_by_variant(string ...$variants_names): void
    {
        foreach ($variants_names as $variant_name) {
            $this->index_page->specify_filter_variant($variant_name);
        }
        $this->i_filter();
    }
    #[When('I filter by customer :customer')]
    public function i_filter_by_customer(Customer_Interface $customer): void
    {
        $this->index_page->specify_filter_customer($customer->get_full_name());
        $this->i_filter();
    }
    #[When('I resend the order confirmation email')]
    public function i_resend_the_order_confirmation_email(): void
    {
        $this->show_page->resend_order_confirmation_email();
    }
    #[When('I resend the shipment confirmation email')]
    public function i_resend_the_shipment_confirmation_email(): void
    {
        $this->show_page->resend_shipment_confirmation_email();
    }
    #[When('I change the :addressType country to :country')]
    public function i_change_the_country_to(string $address_type, string $country): void
    {
        match ($address_type) {
            'shipping' => $this->update_page->change_shipping_country($country),
            'billing' => $this->update_page->change_billing_country($country),
            default => throw new \InvalidArgumentException(sprintf('Address type "%s" is not supported.', $address_type)),
        };
    }
    #[Then('I should see a single order from customer :customer')]
    public function i_should_see_a_single_order_from_customer(Customer_Interface $customer): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['customer' => $customer->get_email()]));
    }
    #[Then('I should not be able to resend the shipment confirmation email')]
    public function i_should_not_be_able_to_resend_the_shipment_confirmation_email(): void
    {
        Assert::false($this->show_page->is_resend_shipment_confirmation_email_button_visible(), 'Resend shipment confirmation email button should not be visible.');
    }
    #[Then('I should see :amount orders in the list')]
    #[Then('I should see a single order in the list')]
    public function i_should_see_a_single_order_in_the_list(int $count = 1): void
    {
        Assert::same($this->index_page->count_items(), $count);
    }
    #[Then('it should have been placed by the customer :customerEmail')]
    public function it_should_be_placed_by_customer(string $customer_email): void
    {
        Assert::true($this->show_page->has_customer($customer_email));
    }
    #[Then('it should be shipped to :customerName, :street, :postcode, :city, :countryName')]
    public function it_should_be_shipped_to_customer_at_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): void
    {
        $this->it_should_be_shipped_to(null, $customer_name, $street, $postcode, $city, $country_name);
    }
    #[Then('/^(this order) should (?:|still )be shipped to "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)"$/')]
    public function it_should_be_shipped_to(?Order_Interface $order, string $customer_name, string $street, string $postcode, string $city, string $country_name): void
    {
        if (null !== $order) {
            $this->i_view_the_summary_of_the_order($order);
        }
        Assert::true($this->show_page->has_shipping_address($customer_name, $street, $postcode, $city, $country_name));
    }
    #[Then('it should be billed to :customerName, :street, :postcode, :city, :countryName')]
    #[Then('the order should be billed to :customerName, :street, :postcode, :city, :countryName')]
    public function it_should_be_billed_to_customer_at_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): void
    {
        Assert::true($this->show_page->has_billing_address($customer_name, $street, $postcode, $city, $country_name));
    }
    #[Then('/^(?:it|this order) should(?:| still) have "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" as its(?:| new) billing address$/')]
    public function it_should_have_as_its_billing_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): void
    {
        $this->i_view_the_summary_of_the_order($this->shared_storage->get('order'));
        Assert::true($this->show_page->has_billing_address($customer_name, $street, $postcode, $city, $country_name));
    }
    #[Then('I should be able to choose the :province province for the :addressType address')]
    public function i_should_be_able_to_choose_the_province_for_the_address_type(Province_Interface $province, string $address_type): void
    {
        Assert::in_array($province->get_name(), match ($address_type) {
            'billing' => $this->update_page->get_available_provinces_for_billing_address(),
            'shipping' => $this->update_page->get_available_provinces_for_shipping_address(),
            default => [],
        });
    }
    #[Then('it should have no shipping address set')]
    public function it_should_have_no_shipping_address_set(): void
    {
        Assert::false($this->show_page->has_shipping_address_visible());
    }
    #[Then('it should be shipped via the :shippingMethodName shipping method')]
    public function it_should_be_shipped_via_shipping_method(string $shipping_method_name): void
    {
        Assert::true($this->show_page->has_shipment($shipping_method_name));
    }
    #[Then('it should be paid with :paymentMethodName')]
    public function it_should_be_paid_with(string $payment_method_name): void
    {
        Assert::true($this->show_page->has_payment($payment_method_name));
    }
    #[Then('/^it should have (\d+) items$/')]
    public function it_should_have_amount_of_items(int $amount = 1): void
    {
        Assert::same($this->show_page->count_items(), $amount);
    }
    #[Then('the product named :productName should be in the items list')]
    public function the_product_should_be_in_the_items_list(string $product_name): void
    {
        Assert::true($this->show_page->is_product_in_the_list($product_name));
    }
    #[Then('the order\'s items total should be :itemsTotal')]
    public function the_orders_items_total_should_be(string $items_total): void
    {
        Assert::eq($this->show_page->get_items_total(), $items_total);
    }
    #[Then('/^the order\'s total should(?:| still) be "([^"]+)"$/')]
    public function the_orders_total_should_be(string $total): void
    {
        Assert::eq($this->show_page->get_total(), $total);
    }
    #[Then('there should be a shipping charge :shippingCharge for :shippingMethodName method')]
    public function there_should_be_a_shipping_charge_for_method(string $shipping_charge, string $shipping_method_name): void
    {
        Assert::true($this->show_page->has_shipping_charge($shipping_charge, $shipping_method_name));
    }
    #[Then('there should be a shipping tax :shippingTax for :shippingMethodName method')]
    public function there_should_be_a_shipping_tax_for_method(string $shipping_tax, string $shipping_method_name): void
    {
        Assert::true($this->show_page->has_shipping_tax($shipping_tax, $shipping_method_name));
    }
    #[Then('the order\'s shipping total should be :shippingTotal')]
    public function the_orders_shipping_total_should_be(string $shipping_total): void
    {
        Assert::eq($this->show_page->get_shipping_total(), $shipping_total);
    }
    #[Then('the order\'s payment should (also) be :paymentAmount')]
    public function the_orders_payment_should_be(string $payment_amount): void
    {
        Assert::eq($this->show_page->get_payment_amount(), $payment_amount);
    }
    #[Then('the order should have tax :tax')]
    public function the_order_should_have_tax(string $tax): void
    {
        Assert::true($this->show_page->has_tax($tax));
    }
    #[Then('/^the order\'s tax total should(?:| still) be "([^"]+)"$/')]
    public function the_orders_tax_total_should_be(string $tax_total): void
    {
        Assert::eq($this->show_page->get_tax_total(), $tax_total);
    }
    #[Then('the order\'s promotion discount should be :promotionAmount from :promotionName promotion')]
    public function the_orders_promotion_discount_should_be_from_promotion(string $promotion_amount, string $promotion_name): void
    {
        Assert::true($this->show_page->has_promotion_discount($promotion_name, $promotion_amount));
    }
    #[Then('the order\'s shipping promotion should be :promotion')]
    public function the_orders_shipping_promotion_discount_should_be(string $promotion_data): void
    {
        Assert::same($this->show_page->get_shipping_promotion_data(), $promotion_data);
    }
    #[Then('/^the order\'s promotion total should(?:| still) be "([^"]+)"$/')]
    public function the_orders_promotion_total_should_be(string $promotion_total): void
    {
        Assert::same($this->show_page->get_order_promotion_total(), $promotion_total);
    }
    #[When('I check :itemName data')]
    public function i_check_data(string $item_name): void
    {
        $this->shared_storage->set('item', $item_name);
    }
    #[Then('/^(its) code should be "([^"]+)"$/')]
    public function item_code_should_be(string $item_name, string $code): void
    {
        Assert::same($this->show_page->get_item_code($item_name), $code);
    }
    #[Then('/^(its) unit price should be ([^"]+)$/')]
    public function item_unit_price_should_be(string $item_name, string $unit_price): void
    {
        Assert::eq($this->show_page->get_item_unit_price($item_name), $unit_price);
    }
    #[Then('/^(its) discounted unit price should be ([^"]+)$/')]
    public function item_discounted_unit_price_should_be(string $item_name, string $discounted_unit_price): void
    {
        Assert::eq($this->show_page->get_item_discounted_unit_price($item_name), $discounted_unit_price);
    }
    #[Then('/^(its) quantity should be ([^"]+)$/')]
    public function item_quantity_should_be(string $item_name, int $quantity): void
    {
        Assert::eq($this->show_page->get_item_quantity($item_name), $quantity);
    }
    #[Then('/^(its) subtotal should be ([^"]+)$/')]
    public function item_subtotal_should_be(string $item_name, string $subtotal): void
    {
        Assert::eq($this->show_page->get_item_subtotal($item_name), $subtotal);
    }
    #[Then('/^(its) discount should be ([^"]+)$/')]
    public function the_item_should_have_discount(string $item_name, string $discount): void
    {
        Assert::eq($this->show_page->get_item_discount($item_name), $discount);
    }
    #[Then('/^(its) tax should be ([^"]+)$/')]
    public function item_tax_should_be(string $item_name, string $tax): void
    {
        Assert::eq($this->show_page->get_item_tax($item_name), $tax);
    }
    #[Then('/^(its) tax included in price should be ([^"]+)$/')]
    public function its_tax_included_in_price_should_be(string $item_name, string $tax): void
    {
        Assert::same($this->show_page->get_item_tax_included_in_price($item_name), $tax);
    }
    #[Then('/^(its) total should be ([^"]+)$/')]
    public function item_total_should_be(string $item_name, string $total): void
    {
        Assert::eq($this->show_page->get_item_total($item_name), $total);
    }
    #[Then('I should be notified that the order\'s payment has been successfully completed')]
    public function i_should_be_notified_that_the_order_s_payment_has_been_successfully_completed(): void
    {
        $this->notification_checker->check_notification('Payment has been successfully updated.', Notification_Type::success());
    }
    #[Then('I should be notified that the order\'s payment could not be finalized due to insufficient stock')]
    public function i_should_be_notified_that_the_orders_payment_could_not_be_finalized_due_to_insufficient_stock(): void
    {
        $this->notification_checker->check_notification('The payment cannot be completed due to insufficient stock of the', Notification_Type::failure());
    }
    #[Then('I should be notified that the order\'s payment has been successfully refunded')]
    public function i_should_be_notified_that_the_order_s_payment_has_been_successfully_refunded(): void
    {
        $this->notification_checker->check_notification('Payment has been successfully refunded.', Notification_Type::success());
    }
    #[Then('it should have payment state :paymentState')]
    #[Then('it should have payment with state :paymentState')]
    public function it_should_have_payment_state(string $payment_state): void
    {
        Assert::true($this->show_page->has_payment_with_state($payment_state));
    }
    #[Then('it should have order\'s payment state :orderPaymentState')]
    public function it_should_have_order_payment_state(string $order_payment_state): void
    {
        Assert::same($this->show_page->get_payment_state(), $order_payment_state);
    }
    #[Then('it should have order\'s shipping state :orderShippingState')]
    public function it_should_have_order_shipping_state(string $order_shipping_state): void
    {
        Assert::same($this->show_page->get_shipping_state(), $order_shipping_state);
    }
    #[Then('its payment state should be refunded')]
    public function its_payment_state_should_be_refunded(): void
    {
        Assert::same($this->show_page->get_payment_state(), 'Refunded');
    }
    #[Then('/^I should not be able to mark (this order) as paid again$/')]
    public function i_should_not_be_able_to_finalize_its_payment(Order_Interface $order): void
    {
        Assert::false($this->show_page->can_complete_order_last_payment($order));
    }
    #[Then('I should be notified that the order has been successfully shipped')]
    public function i_should_be_notified_that_the_order_has_been_successfully_shipped(): void
    {
        $this->notification_checker->check_notification('Shipment has been successfully updated.', Notification_Type::success());
    }
    #[Then('/^I should not be able to ship (this order)$/')]
    public function i_should_not_be_able_to_ship_this_order(Order_Interface $order): void
    {
        Assert::false($this->show_page->can_ship_order($order));
    }
    #[When('I cancel this order')]
    public function i_cancel_this_order(): void
    {
        $this->show_page->cancel_order();
    }
    #[Then('I should be notified that it has been successfully updated')]
    public function i_should_be_notified_about_it_has_been_successfully_canceled(): void
    {
        $this->notification_checker->check_notification('Order has been successfully updated.', Notification_Type::success());
    }
    #[Then('I should not be able to cancel this order')]
    public function i_should_not_be_able_to_cancel_this_order(): void
    {
        Assert::false($this->show_page->has_cancel_button());
    }
    #[Then('this order should have state :state')]
    #[Then('its state should be :state')]
    public function its_state_should_be(string $state): void
    {
        Assert::same($this->show_page->get_order_state(), $state);
    }
    #[Then('it should( still) have a :state state')]
    public function it_should_have_state(string $state): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['state' => $state]));
    }
    #[Then('/^(the administrator) should know about (this additional note) for (this order made by "[^"]+")$/')]
    public function the_customer_service_should_know_about_this_additional_notes(Admin_User_Interface $user, string $note, Order_Interface $order): void
    {
        $this->shared_security_service->perform_action_as_admin_user($user, function () use ($note, $order): void {
            $this->show_page->open(['id' => $order->get_id()]);
            Assert::true($this->show_page->has_note($note));
        });
    }
    #[Then('I should see an order with :orderNumber number')]
    public function i_should_see_order_with_number(string $order_number): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['number' => $order_number]));
    }
    #[Then('I should not see an order with :orderNumber number')]
    public function i_should_not_see_order_with_number(string $order_number): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['number' => $order_number]));
    }
    #[Then('I should not see any orders with currency :currencyCode')]
    public function i_should_not_see_any_order_with_currency(string $currency_code): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['currencyCode' => $currency_code]));
    }
    #[Then('the first order should have number :number')]
    public function the_first_order_should_have_number(string $number): void
    {
        Assert::eq($this->index_page->get_column_fields('number')[0], $number);
    }
    #[Then('it should have shipment in state :shipmentState')]
    public function it_should_have_shipment_state(string $shipment_state): void
    {
        Assert::true($this->show_page->has_shipment_with_state($shipment_state));
    }
    #[Then('order :orderNumber should have shipment state :shippingState')]
    public function this_order_shipment_state_should_be(string $shipping_state): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['shippingState' => $shipping_state]));
    }
    #[Then('the order :order should have order payment state :orderPaymentState')]
    #[Then('/^(this order) should have order payment state "([^"]+)"$/')]
    public function the_order_should_have_payment_state(Order_Interface $order, string $order_payment_state): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['paymentState' => $order_payment_state]));
    }
    #[Then('the last order should have order payment state :orderPaymentState')]
    public function the_last_order_should_have_payment_state(string $order_payment_state): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['paymentState' => $order_payment_state]));
    }
    #[Then('the order :order should have order shipping state :orderShippingState')]
    #[Then('/^(this order) should have order shipping state "([^"]+)"$/')]
    public function the_order_should_have_shipping_state(Order_Interface $order, string $order_shipping_state): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['shippingState' => $order_shipping_state]));
    }
    #[Then('/^there should be(?:| only) (\d+) payments?$/')]
    public function the_order_should_have_number_of_payments(string $number): void
    {
        Assert::same($this->show_page->get_payments_count(), (int) $number);
    }
    #[Then('I should see the order :orderNumber with total :total')]
    public function i_should_see_the_order_with_total(string $order_number, string $total): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['number' => $order_number, 'total' => $total]));
    }
    #[When('/^I want to modify a customer\'s (?:billing|shipping) address of (this order)$/')]
    public function i_want_to_modify_a_customer_s_shipping_address(Order_Interface $order): void
    {
        $this->update_page->open(['id' => $order->get_id()]);
    }
    #[When('/^I specify their (?:|new )shipping (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    public function i_specify_their_shipping_address_as_for(Address_Interface $address): void
    {
        $this->update_page->specify_shipping_address($address);
    }
    #[When('/^I specify their (?:|new )billing (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)" for "([^"]+)")$/')]
    public function i_specify_their_billing_address_as_for(Address_Interface $address): void
    {
        $this->update_page->specify_billing_address($address);
    }
    #[Then('/^I should be notified that all mandatory (shipping|billing) address details are incomplete$/')]
    public function i_should_be_notified_that_all_mandatory_address_details_are_incomplete(string $type): void
    {
        /** @var array<int, string> $mandatoryAddressFields */
        $mandatory_address_fields = ['first name', 'last name', 'street', 'city', 'postcode'];
        foreach ($mandatory_address_fields as $mandatory_address_field) {
            $this->assert_element_validation_message($type, $mandatory_address_field, sprintf('Please enter %s.', $mandatory_address_field));
        }
        $this->assert_element_validation_message($type, 'country', 'Please select country.');
    }
    #[Then('I should see :provinceName as province in the shipping address')]
    public function i_should_see_as_province_in_the_shipping_address(string $province_name): void
    {
        Assert::true($this->show_page->has_shipping_province_name($province_name));
    }
    #[Then('I should see :provinceName as province in the billing address')]
    public function i_should_see_ad_province_in_the_billing_address(string $province_name): void
    {
        Assert::true($this->show_page->has_billing_province_name($province_name));
    }
    #[Then('I should see this customer\'s IP address')]
    public function i_should_see_customers_ip_address(): void
    {
        Assert::not_empty($this->show_page->get_ip_address_assigned());
    }
    #[When('/^I (clear the billing address) information$/')]
    public function i_clear_the_billing_address_information(Address_Interface $address): void
    {
        $this->update_page->specify_billing_address($address);
    }
    #[When('/^I (clear the shipping address) information$/')]
    public function i_clear_the_shipping_address_information(Address_Interface $address): void
    {
        $this->update_page->specify_shipping_address($address);
    }
    #[When('/^I do not specify new information$/')]
    public function i_do_not_specify_new_information(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[Then('/^(the administrator) should see that (order placed by "[^"]+") has "([^"]+)" currency$/')]
    public function the_administrator_should_see_that_this_order_has_been_placed_in(Admin_User_Interface $user, Order_Interface $order, string $currency): void
    {
        $this->shared_security_service->perform_action_as_admin_user($user, function () use ($order, $currency): void {
            $this->show_page->open(['id' => $order->get_id()]);
            Assert::same($this->show_page->get_order_currency(), $currency);
        });
    }
    #[Then('/^(the administrator) should see the order with total "([^"]+)" in order list$/')]
    public function the_administrator_should_see_the_order_with_total_in_order_list(Admin_User_Interface $user, string $total): void
    {
        $this->shared_security_service->perform_action_as_admin_user($user, function () use ($total): void {
            $this->index_page->open();
            Assert::true($this->index_page->is_single_resource_on_page(['total' => $total]));
        });
    }
    #[Then('I should not be able to refund this payment')]
    public function i_should_not_be_able_to_refund_this_payment(): void
    {
        Assert::false($this->show_page->has_refund_button());
    }
    #[Then('I should not see information about shipments')]
    public function i_should_not_see_information_about_shipments(): void
    {
        Assert::same($this->show_page->get_shipments_count(), 0);
    }
    #[Then('the :productName product\'s unit price should be :price')]
    public function product_unit_price_should_be(string $product_name, string $price): void
    {
        Assert::same($this->show_page->get_item_unit_price($product_name), $price);
    }
    #[Then('the :productName product\'s item discount should be :price')]
    public function product_item_discount_should_be(string $product_name, string $price): void
    {
        Assert::same($this->show_page->get_item_discount($product_name), $price);
    }
    #[Then('the :productName product\'s order discount should be :price')]
    public function product_order_discount_should_be(string $product_name, string $price): void
    {
        Assert::same($this->show_page->get_item_order_discount($product_name), $price);
    }
    #[Then('the :productName product\'s quantity should be :quantity')]
    public function product_quantity_should_be(string $product_name, string $quantity): void
    {
        Assert::same($this->show_page->get_item_quantity($product_name), $quantity);
    }
    #[Then('the :productName product\'s subtotal should be :subTotal')]
    public function product_subtotal_should_be(string $product_name, string $sub_total): void
    {
        Assert::same($this->show_page->get_item_subtotal($product_name), $sub_total);
    }
    #[Then('the :productName product\'s discounted unit price should be :price')]
    public function product_discounted_unit_price_should_be(string $product_name, string $price): void
    {
        Assert::same($this->show_page->get_item_discounted_unit_price($product_name), $price);
    }
    #[Then('I should be informed that there are no payments')]
    public function i_should_see_information_about_no_payments(): void
    {
        Assert::same($this->show_page->get_payments_count(), 0);
        Assert::true($this->show_page->has_information_about_no_payment());
    }
    #[Then('/^I should be notified that the (order|shipment) confirmation email has been successfully resent to the customer$/')]
    public function i_should_be_notified_that_the_order_confirmation_email_has_been_successfully_resent_to_the_customer(string $type): void
    {
        $this->notification_checker->check_notification(sprintf('%s confirmation has been successfully resent to the customer.', ucfirst($type)), Notification_Type::success());
    }
    #[Then('I should not be able to resend the order confirmation email')]
    public function i_should_not_be_able_to_resend_the_order_confirmation_email(): void
    {
        Assert::false($this->show_page->is_resend_order_confirmation_email_button_visible(), 'Resend order confirmation email button should not be visible.');
    }
    #[Then('I should see the shipping date as :dateTime')]
    public function i_should_see_the_shipping_date_as(string $date_time): void
    {
        Assert::same($this->show_page->get_shipped_at_date(), $date_time);
    }
    #[Then('I should be informed that the order does not exist')]
    public function i_should_be_informed_that_the_order_does_not_exist(): void
    {
        Assert::same($this->error_page->get_code(), 404);
    }
    /**
     * @throws \InvalidArgumentException
     */
    private function assert_element_validation_message(string $type, string $element, string $expected_message): void
    {
        $element = sprintf('%s_%s', $type, str_replace(' ', '_', $element));
        Assert::true($this->update_page->check_validation_message_for($element, $expected_message));
    }
}
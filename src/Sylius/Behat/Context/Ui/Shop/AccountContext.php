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
use Friends_Of_Behat\Page_Object_Extension\Page\Unexpected_Page_Exception;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Shop\Account\Change_Password_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Dashboard_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Login_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Order\Index_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Order\Show_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Profile_Update_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Order_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Webmozart\Assert\Assert;
final readonly class Account_Context implements Context
{
    public function __construct(private Dashboard_Page_Interface $dashboard_page, private Profile_Update_Page_Interface $profile_update_page, private Change_Password_Page_Interface $change_password_page, private Index_Page_Interface $order_index_page, private Show_Page_Interface $order_show_page, private Login_Page_Interface $login_page, private Notification_Checker_Interface $notification_checker, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I want to modify my profile')]
    public function i_want_to_modify_my_profile(): void
    {
        $this->profile_update_page->open();
    }
    #[When('I specify the first name as :firstName')]
    #[When('I remove the first name')]
    public function i_specify_the_first_name(?string $first_name = null): void
    {
        $this->profile_update_page->specify_first_name($first_name);
    }
    #[When('I specify the phone number as huge value')]
    public function i_specify_the_phone_number_as_huge_value(): void
    {
        $this->profile_update_page->specify_phone_number(str_repeat('1', 256));
    }
    #[When('I specify the last name as :lastName')]
    #[When('I remove the last name')]
    public function i_specify_the_last_name(?string $last_name = null): void
    {
        $this->profile_update_page->specify_last_name($last_name);
    }
    #[When('I specify the customer email as :email')]
    #[When('I remove the customer email')]
    public function i_specify_customer_the_email(?string $email = null): void
    {
        $this->profile_update_page->specify_email($email);
    }
    #[Then('I should be notified that it has been successfully edited')]
    public function i_should_be_notified_that_it_has_been_successfully_edited(): void
    {
        $this->notification_checker->check_notification('has been successfully updated.', Notification_Type::success());
    }
    #[Then('I should be notified that I can no longer change payment method of this order')]
    public function i_should_be_notified_that_i_can_no_longer_change_payment_method_of_this_order(): void
    {
        Assert::true($this->order_index_page->has_flash_message('You can no longer change payment method of this order'));
    }
    #[Then('my name should be :name')]
    #[Then('my name should still be :name')]
    public function my_name_should_be(string $name): void
    {
        $this->dashboard_page->open();
        Assert::true($this->dashboard_page->has_customer_name($name));
    }
    #[Then('my phone number should still be :phoneNumber')]
    public function my_phone_number_should_be(string $phone_number): void
    {
        $this->profile_update_page->open();
        Assert::same($this->profile_update_page->get_phone_number(), $phone_number, 'Phone number should be equal to %s, but is not.');
    }
    #[Then('my email should be :email')]
    #[Then('my email should still be :email')]
    public function my_email_should_be(string $email): void
    {
        $this->dashboard_page->open();
        Assert::true($this->dashboard_page->has_customer_email($email));
    }
    #[Then('/^I should be notified that the (email|password|city|street|first name|last name) is required$/')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        Assert::true($this->profile_update_page->check_validation_message_for(String_Inflector::name_to_code($element), sprintf('Please enter your %s.', $element)));
    }
    #[Then('I should be notified that the phone number is too long')]
    public function i_should_be_notified_that_phone_number_is_too_long(): void
    {
        Assert::true($this->profile_update_page->check_validation_message_for('phone_number', 'Phone number must not be longer than 255 characters.'));
    }
    #[Then('/^I should be notified that the (email) is invalid$/')]
    public function i_should_be_notified_that_element_is_invalid(string $element): void
    {
        Assert::true($this->profile_update_page->check_validation_message_for(String_Inflector::name_to_code($element), sprintf('This %s is invalid.', $element)));
    }
    #[Then('I should be notified that the email is already used')]
    public function i_should_be_notified_that_the_email_is_already_used(): void
    {
        Assert::true($this->profile_update_page->check_validation_message_for('email', 'This email is already used.'));
    }
    #[When('/^I want to change my password$/')]
    public function i_want_to_change_my_password(): void
    {
        $this->change_password_page->open();
    }
    #[Given('I change password from :oldPassword to :newPassword')]
    public function i_change_password_to($old_password, $new_password): void
    {
        $this->i_specify_the_current_password_as($old_password);
        $this->i_specify_the_new_password_as($new_password);
        $this->i_specify_the_confirmation_password_as($new_password);
    }
    #[Given('I am changing this order\'s payment method')]
    public function i_want_to_change_this_orders_payment_method(): void
    {
        $this->i_browse_my_orders();
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $this->order_index_page->change_payment_method($order);
    }
    #[Then('I should be notified that my password has been successfully changed')]
    public function i_should_be_notified_that_my_password_has_been_successfully_changed(): void
    {
        $this->notification_checker->check_notification('has been changed successfully!', Notification_Type::success());
    }
    #[Given('I specify the current password as :password')]
    public function i_specify_the_current_password_as(string $password): void
    {
        $this->change_password_page->specify_current_password($password);
    }
    #[Given('I specify the new password as :password')]
    public function i_specify_the_new_password_as(string $password): void
    {
        $this->change_password_page->specify_new_password($password);
    }
    #[Given('I confirm this password as :password')]
    public function i_specify_the_confirmation_password_as(string $password): void
    {
        $this->change_password_page->specify_confirmation_password($password);
    }
    #[Then('I should be notified that provided password is different than the current one')]
    public function i_should_be_notified_that_provided_password_is_different_than_the_current_one(): void
    {
        Assert::true($this->change_password_page->check_validation_message_for('current_password', 'Provided password is different than the current one.'));
    }
    #[Then('I should be notified that the entered passwords do not match')]
    public function i_should_be_notified_that_the_entered_passwords_do_not_match(): void
    {
        Assert::true($this->change_password_page->check_validation_message_for('new_password', 'The entered passwords don\'t match'));
    }
    #[Then('I should be notified that the password should be at least :length characters long')]
    public function i_should_be_notified_that_the_password_should_be_at_least_characters_long(int $length): void
    {
        Assert::true($this->change_password_page->check_validation_message_for('new_password', sprintf('Password must be at least %s characters long.', $length)));
    }
    #[Given('I am browsing my orders')]
    #[When('I browse my orders')]
    public function i_browse_my_orders(): void
    {
        $this->order_index_page->open();
    }
    #[When('I try to browse my orders')]
    public function i_try_to_browse_my_orders(): void
    {
        $this->order_index_page->try_to_open();
    }
    #[When('I change my payment method to :paymentMethod')]
    public function i_change_my_payment_method_to(Payment_Method_Interface $payment_method): void
    {
        $this->order_show_page->choose_payment_method($payment_method);
        $this->order_show_page->pay();
    }
    #[When('I try to change my payment method to :paymentMethod')]
    public function i_choose_payment_method(Payment_Method_Interface $payment_method): void
    {
        try {
            $this->order_show_page->choose_payment_method($payment_method);
        } catch (Element_Not_Found_Exception) {
            return;
        }
        throw new \InvalidArgumentException('The payment method has been changed, but it should not be the case.');
    }
    #[Then('I should see a single order in the list')]
    public function i_should_see_a_single_order_in_the_list(): void
    {
        Assert::same($this->order_index_page->count_orders(), 1);
    }
    #[Then('this order should have :order number')]
    public function this_order_should_have_number(Order_Interface $order): void
    {
        Assert::true($this->order_index_page->is_order_with_number_in_the_list($order->get_number()));
    }
    #[When('I view the summary of the order :order')]
    #[When('I view the summary of my order :order')]
    public function i_view_the_summary_of_the_order(Order_Interface $order): void
    {
        $this->order_show_page->open(['number' => $order->get_number()]);
    }
    #[When('I am viewing the summary of my last order')]
    public function i_viewing_the_summary_of_my_last_order(): void
    {
        $this->order_index_page->open();
        $this->order_index_page->open_last_order_page();
    }
    #[When('I log in as :email with :password password')]
    public function i_log_in_as_with_password(string $email, string $password): void
    {
        $this->login_page->open();
        $this->login_page->specify_username($email);
        $this->login_page->specify_password($password);
        $this->login_page->log_in();
    }
    #[Then('it should has number :orderNumber')]
    #[Then('it should have the number :orderNumber')]
    public function it_should_has_number(string $order_number): void
    {
        Assert::same($this->order_show_page->get_number(), $order_number);
    }
    #[Then('I should see :customerName, :street, :postcode, :city, :countryName as shipping address')]
    public function i_should_see_as_shipping_address(string $customer_name, string $street, string $postcode, string $city, string $country_name): void
    {
        Assert::true($this->order_show_page->has_shipping_address($customer_name, $street, $postcode, $city, $country_name));
    }
    #[Then('I should see :customerName, :street, :postcode, :city, :countryName as billing address')]
    public function it_should_be_shipped_to(string $customer_name, string $street, string $postcode, string $city, string $country_name): void
    {
        Assert::true($this->order_show_page->has_billing_address($customer_name, $street, $postcode, $city, $country_name));
    }
    #[Then('I should see :total as order\'s total')]
    public function i_should_see_as_order_s_total($total): void
    {
        Assert::same($this->order_show_page->get_total(), $total);
    }
    #[Then('I should see :itemsTotal as order\'s subtotal')]
    public function i_should_see_as_order_s_subtotal($subtotal): void
    {
        Assert::same($this->order_show_page->get_subtotal(), $subtotal);
    }
    #[Then('I should see that I have to pay :paymentAmount for this order')]
    #[Then('I should see :paymentTotal as payment total')]
    public function i_should_see_i_have_to_pay_for_this_order($payment_amount): void
    {
        Assert::same($this->order_show_page->get_payment_price(), $payment_amount);
    }
    #[Then('I should see :numberOfItems items in the list')]
    public function i_should_see_items_in_the_list($number_of_items): void
    {
        Assert::same($this->order_show_page->count_items(), (int) $number_of_items);
    }
    #[Then('the product named :productName should be in the items list')]
    public function the_product_should_be_in_the_items_list(string $product_name): void
    {
        Assert::true($this->order_show_page->is_product_in_the_list($product_name));
    }
    #[Then('I should have :paymentMethod payment method on my order')]
    public function i_should_have_payment_method_on_my_order(Payment_Method_Interface $payment_method): void
    {
        /** @var OrderInterface $order */
        $order = $this->shared_storage->get('order');
        $this->order_index_page->open();
        $this->order_index_page->change_payment_method($order);
        Assert::same($this->order_show_page->get_chosen_payment_method(), $payment_method->get_name());
    }
    #[Then('I should see :itemPrice as item price')]
    public function i_should_see_as_item_price($item_price): void
    {
        Assert::same($this->order_show_page->get_item_price(), $item_price);
    }
    #[When('I subscribe to the newsletter')]
    public function i_subscribe_to_the_newsletter(): void
    {
        $this->profile_update_page->subscribe_to_the_newsletter();
    }
    #[Then('I should be subscribed to the newsletter')]
    public function i_should_be_subscribed_to_the_newsletter(): void
    {
        Assert::true($this->profile_update_page->is_subscribed_to_the_newsletter());
    }
    #[Then('I should see :provinceName as province in the shipping address')]
    public function i_should_see_as_province_in_the_shipping_address(string $province_name): void
    {
        Assert::true($this->order_show_page->has_shipping_province_name($province_name));
    }
    #[Then('I should see :provinceName as province in the billing address')]
    public function i_should_see_as_province_in_the_billing_address(string $province_name): void
    {
        Assert::true($this->order_show_page->has_billing_province_name($province_name));
    }
    #[Then('I should be redirected to my account dashboard')]
    public function i_should_be_redirected_to_my_account_dashboard(): void
    {
        Assert::true($this->dashboard_page->is_open(), 'User should be on the account panel dashboard page but they are not.');
    }
    #[When('I want to log in')]
    public function i_want_to_log_in(): void
    {
        $this->login_page->try_to_open();
    }
    #[Then('I should see its payment state as :paymentState')]
    public function should_see_payment_status(string $payment_state): void
    {
        Assert::same($this->order_show_page->get_payment_state(), $payment_state);
    }
    #[Then('I should see its order\'s payment status as :orderPaymentState')]
    public function i_should_see_its_order_s_payment_status_as(string $order_payment_state): void
    {
        Assert::same($this->order_show_page->get_order_payment_status(), $order_payment_state);
    }
    #[Then('the order\'s shipment state should be :orderShipmentStatus')]
    public function the_order_shipment_state_should_be(string $order_shipment_status): void
    {
        Assert::same($this->order_show_page->get_order_shipment_state(), $order_shipment_status);
    }
    #[Then('the shipment state should be :shipmentStatus')]
    public function the_shipment_status_should_be(string $shipment_status): void
    {
        Assert::same($this->order_show_page->get_shipment_status(), $shipment_status);
    }
    #[Then('I should be notified that the verification email has been sent')]
    public function i_should_be_notified_that_the_verification_email_has_been_sent(): void
    {
        $this->notification_checker->check_notification('An email with the verification link has been sent to your email address.', Notification_Type::success());
    }
    #[Then('/^(?:my|his|her) account should not be verified$/')]
    public function my_account_should_not_be_verified(): void
    {
        $this->dashboard_page->open();
        Assert::false($this->dashboard_page->is_verified());
    }
    #[Then('I should not be logged in')]
    public function i_should_not_be_logged_in(): void
    {
        try {
            $this->dashboard_page->open();
        } catch (Unexpected_Page_Exception) {
            return;
        }
        throw new \InvalidArgumentException('Dashboard has been openned, but it shouldn\'t as customer should not be logged in');
    }
    #[Then('I should not see my orders')]
    public function i_should_not_see_my_orders(): void
    {
        Assert::false($this->order_index_page->is_open());
    }
    #[Then('I should be on the login page')]
    #[Then('I should be denied an access to order list')]
    public function i_should_be_on_the_login_page(): void
    {
        Assert::true($this->login_page->is_open());
    }
}
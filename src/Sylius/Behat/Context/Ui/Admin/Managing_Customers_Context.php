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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Admin\Customer\Form_Element_Interface;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Behat\Page\Admin\Customer\Index_Page_Interface as CustomerIndexPageInterface;
use Sylius\Behat\Page\Admin\Customer\Show_Page_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Customers_Context implements Context
{
    /**
     * @param CustomerIndexPageInterface $indexPage
     */
    public function __construct(private Create_Page_Interface $create_page, private Index_Page_Interface $index_page, private Update_Page_Interface $update_page, private Show_Page_Interface $show_page, private Index_Page_Interface $orders_index_page, private Current_Page_Resolver_Interface $current_page_resolver, private Form_Element_Interface $form_element)
    {
    }
    #[When('I want to create a new customer')]
    #[When('I want to create a new customer account')]
    public function i_want_to_create_a_new_customer(): void
    {
        $this->create_page->open();
    }
    #[When('/^I specify (?:their|his) first name as "([^"]*)"$/')]
    public function i_specify_its_first_name_as(string $name): void
    {
        $this->form_element->specify_first_name($name);
    }
    #[When('/^I specify (?:their|his) last name as "([^"]*)"$/')]
    public function i_specify_its_last_name_as(string $name): void
    {
        $this->form_element->specify_last_name($name);
    }
    #[When('I specify their email as :name')]
    #[When('I do not specify their email')]
    public function i_specify_its_email_as($email = null): void
    {
        $this->form_element->specify_email($email ?? '');
    }
    #[When('I change their email to :email')]
    #[When('I remove its email')]
    public function i_change_their_email_to($email = null): void
    {
        $this->form_element->specify_email($email ?? '');
    }
    #[When('I add them')]
    #[When('I try to add them')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I filter by group :groupName')]
    #[When('I filter by groups :firstGroup and :secondGroup')]
    public function i_filter_by_group(string ...$groups_names): void
    {
        foreach ($groups_names as $group_name) {
            $this->index_page->set_filter_group($group_name);
        }
        $this->index_page->filter();
    }
    #[Then('the customer :customer should appear in the store')]
    #[Then('the customer :customer should still have this email')]
    public function the_customer_should(Customer_Interface $customer): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['email' => $customer->get_email()]));
    }
    #[When('I select :gender as its gender')]
    public function i_select_gender(string $gender): void
    {
        $this->form_element->choose_gender($gender);
    }
    #[When('I select :group as their group')]
    public function i_select_group(string $group): void
    {
        $this->form_element->choose_group($group);
    }
    #[When('I specify its birthday as :birthday')]
    public function i_specify_its_birthday_as(string $birthday): void
    {
        $this->form_element->specify_birthday($birthday);
    }
    #[When('/^I want to edit (this customer)$/')]
    public function i_want_to_edit_this_customer(Customer_Interface $customer): void
    {
        $this->update_page->open(['id' => $customer->get_id()]);
    }
    #[When('I verify it')]
    public function i_try_to_verify_it(): void
    {
        $this->form_element->verify_user();
    }
    #[Then('/^(this customer) should be verified$/')]
    public function this_customer_should_be_verified(Customer_Interface $customer): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_customer_verified($customer));
    }
    #[Then('/^(this customer) with name "([^"]*)" should appear in the store$/')]
    public function the_customer_with_name_should_appear_in_the_registry(Customer_Interface $customer, $name): void
    {
        $this->update_page->open(['id' => $customer->get_id()]);
        Assert::same($this->form_element->get_full_name(), $name);
    }
    #[When('I want to see all customers in store')]
    public function i_want_to_see_all_customers_in_store(): void
    {
        $this->index_page->open();
    }
    #[When('/^I sort customers by (ascending|descending) registration date$/')]
    public function i_sort_customers_by_registration_date(string $order): void
    {
        $this->sort_by($order, 'createdAt');
    }
    #[When('/^I sort customers by (ascending|descending) (email|first name|last name)$/')]
    public function i_sort_customers_by_field(string $order, string $field): void
    {
        $this->sort_by($order, String_Inflector::name_to_camel_case($field));
    }
    #[Then('/^I should see (\d+) customers (?:in|on) the list$/')]
    #[Then('/^I should see a single customer on the list$/')]
    public function i_should_see_customers_in_the_list($amount_of_customers = 1): void
    {
        Assert::same($this->index_page->count_items(), (int) $amount_of_customers);
    }
    #[Then('I should see the customer :email in the list')]
    #[Then('I should see the customer :email on the list')]
    public function i_should_see_the_customer_in_the_list($email): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['email' => $email]));
    }
    #[Then('/^the (first|last) customer should be "([^"]+)"$/')]
    public function the_first_last_customer_should_be(string $placement, string $email): void
    {
        $index = 'first' === $placement ? 0 : $this->index_page->count_items() - 1;
        Assert::same($this->index_page->get_column_fields('email')[$index], $email);
    }
    #[Then('/^I should be notified that ([^"]+) is required$/')]
    public function i_should_be_notified_that_first_name_is_required(string $element_name): void
    {
        Assert::same($this->form_element->get_validation_message(String_Inflector::name_to_lowercase_code($element_name)), sprintf('Please enter your %s.', $element_name));
    }
    #[Then('/^I should be notified that ([^"]+) should be ([^"]+)$/')]
    public function i_should_be_notified_that_the_element_should_be(string $element_name, $validation_message): void
    {
        Assert::same($this->form_element->get_validation_message(String_Inflector::name_to_lowercase_code($element_name)), sprintf('%s must be %s.', ucfirst($element_name), $validation_message));
    }
    #[Then('the customer with email :email should not appear in the store')]
    public function the_customer_should_not_appear_in_the_store($email): void
    {
        $this->index_page->open();
        Assert::false($this->index_page->is_single_resource_on_page(['email' => $email]));
    }
    #[When('I remove its first name')]
    public function i_remove_its_first_name(): void
    {
        $this->form_element->specify_first_name('');
    }
    #[Then('/^(this customer) should have an empty first name$/')]
    #[Then('the customer :customer should still have an empty first name')]
    public function the_customer_should_still_have_an_empty_first_name(Customer_Interface $customer): void
    {
        $this->update_page->open(['id' => $customer->get_id()]);
        Assert::eq($this->form_element->get_first_name(), '');
    }
    #[When('I remove its last name')]
    public function i_remove_its_last_name(): void
    {
        $this->form_element->specify_last_name('');
    }
    #[Then('/^(this customer) should have an empty last name$/')]
    #[Then('the customer :customer should still have an empty last name')]
    public function the_customer_should_still_have_an_empty_last_name(Customer_Interface $customer): void
    {
        $this->update_page->open(['id' => $customer->get_id()]);
        Assert::eq($this->form_element->get_last_name(), '');
    }
    #[Then('I should be notified that email is not valid')]
    public function i_should_be_notified_that_email_is_not_valid(): void
    {
        Assert::same($this->form_element->get_validation_message('email'), 'This email is invalid.');
    }
    #[Then('I should be notified that email must be unique')]
    public function i_should_be_notified_that_email_must_be_unique(): void
    {
        Assert::same($this->form_element->get_validation_message('email'), 'This email is already used.');
    }
    #[Then('there should still be only one customer with email :email')]
    public function there_should_still_be_only_one_customer_with_email($email): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['email' => $email]));
    }
    #[When('I want to enable :customer')]
    #[When('I want to disable :customer')]
    #[When('I want to verify :customer')]
    public function i_want_to_change_status_of(Customer_Interface $customer): void
    {
        $this->update_page->open(['id' => $customer->get_id()]);
    }
    #[When('I enable their account')]
    public function i_enable_it(): void
    {
        $this->form_element->enable();
    }
    #[When('I disable their account')]
    public function i_disable_it(): void
    {
        $this->form_element->disable();
    }
    #[Then('/^(this customer) should be enabled$/')]
    public function this_customer_should_be_enabled(Customer_Interface $customer): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_customer_enabled($customer), true);
    }
    #[Then('/^(this customer) should be disabled$/')]
    public function this_customer_should_be_disabled(Customer_Interface $customer): void
    {
        $this->index_page->open();
        Assert::eq($this->index_page->is_customer_enabled($customer), false);
    }
    #[When('I specify their password as :password')]
    public function i_specify_its_password_as(string $password): void
    {
        $this->form_element->specify_password($password);
    }
    #[When('I browse orders of a customer :customer')]
    public function i_browse_orders_of_a_customer(Customer_Interface $customer): void
    {
        $this->orders_index_page->open(['id' => $customer->get_id()]);
    }
    #[When('I sort the orders :sortType by :field')]
    public function i_sort_the_order_by_field(string $field): void
    {
        $this->orders_index_page->sort(ucfirst($field));
    }
    #[Then('the customer :customer should have an account created')]
    #[Then('/^(this customer) should have an account created$/')]
    public function they_should_have_an_account_created(Customer_Interface $customer): void
    {
        Assert::not_null($customer->get_user()->get_password(), 'Customer should have an account, but they do not.');
    }
    #[When('I view details of the customer :customer')]
    #[When('/^I view (their) details$/')]
    public function i_view_details_of_the_customer(Customer_Interface $customer): void
    {
        $this->show_page->open(['id' => $customer->get_id()]);
    }
    #[Then('/^(?:their|his) name should be "([^"]+)"$/')]
    public function his_name_should_be(string $name): void
    {
        Assert::same($this->show_page->get_customer_name(), $name);
    }
    #[Then('he should be registered since :registrationDate')]
    public function his_registration_date_should_be($registration_date): void
    {
        Assert::eq($this->show_page->get_registration_date(), new \DateTime($registration_date));
    }
    #[Then('/^(?:their|his) email should be "([^"]+)"$/')]
    public function his_email_should_be(string $email): void
    {
        Assert::same($this->show_page->get_customer_email(), $email);
    }
    #[Then('/^(?:their|his) phone number should be "([^"]+)"$/')]
    public function his_phone_number_should_be(string $phone_number): void
    {
        Assert::same($this->show_page->get_customer_phone_number(), $phone_number);
    }
    #[Then('their default address should be :firstName :lastName, :street, :postcode :city, :country')]
    public function their_s_default_address_should_be(string $first_name, string $last_name, string $street, string $postcode, string $city, string $country): void
    {
        Assert::same($this->show_page->get_default_address(), sprintf('%s %s, %s, %s %s, %s', $first_name, $last_name, $street, $postcode, $city, ucwords($country)));
    }
    #[Then('I should see information about no existing account for this customer')]
    public function i_should_see_information_about_no_existing_account_for_this_customer(): void
    {
        Assert::false($this->show_page->has_account());
    }
    #[Then('I should see that this customer is subscribed to the newsletter')]
    public function i_should_see_that_this_customer_is_subscribed_to_the_newsletter(): void
    {
        Assert::true($this->show_page->is_subscribed_to_newsletter());
    }
    #[Then('I should not see information about email verification')]
    public function i_should_see_information_about_email_verification(): void
    {
        Assert::true($this->show_page->has_email_verification_information());
    }
    #[When('I make them subscribed to the newsletter')]
    public function i_make_them_subscribed_to_the_newsletter(): void
    {
        $this->form_element->subscribe_to_the_newsletter();
    }
    #[When('I change the password of user :customer to :newPassword')]
    public function i_change_the_password_of_user_to(Customer_Interface $customer, string $new_password): void
    {
        $this->update_page->open(['id' => $customer->get_id()]);
        $this->form_element->specify_password($new_password);
        $this->update_page->save_changes();
    }
    #[Then('this customer should be subscribed to the newsletter')]
    public function this_customer_should_be_subscribed_to_the_newsletter(): void
    {
        Assert::true($this->form_element->is_subscribed_to_the_newsletter());
    }
    #[Then('the province in the default address should be :provinceName')]
    public function the_province_in_the_default_address_should_be(string $province_name): void
    {
        Assert::true($this->show_page->has_default_address_province_name($province_name));
    }
    #[Then('this customer should have :groupName as their group')]
    public function this_customer_should_have_as_their_group($group_name): void
    {
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->update_page, $this->show_page]);
        if ($current_page instanceof Show_Page_Interface) {
            Assert::same($current_page->get_group_name(), $group_name);
        } else {
            Assert::same($this->form_element->get_group_name(), $group_name);
        }
    }
    #[Then('I should see that this customer has verified the email')]
    public function i_should_see_that_this_customer_has_verified_the_email(): void
    {
        Assert::true($this->show_page->has_verified_email());
    }
    #[Then('I should see a single order in the list')]
    public function i_should_see_a_single_order_in_the_list(): void
    {
        Assert::same($this->orders_index_page->count_items(), 1);
    }
    #[Then('I should see the order with number :orderNumber in the list')]
    public function i_should_see_a_single_order_from_customer($order_number): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['number' => $order_number]));
    }
    #[Then('I should not see the order with number :orderNumber in the list')]
    public function i_should_not_see_a_single_order_from_customer($order_number): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['number' => $order_number]));
    }
    #[When('I do not specify any information')]
    public function i_do_not_specify_any_information(): void
    {
        // Intentionally left blank.
    }
    #[Then('I should still be on the customer creation page')]
    public function i_should_be_on_the_customer_creation_page(): void
    {
        $this->create_page->verify();
    }
    #[When('I do not choose create account option')]
    public function i_do_not_choose_create_account_option(): void
    {
        // Intentionally left blank.
    }
    #[Then('/^I should be notified that the password must be at least (\d+) characters long$/')]
    public function i_should_be_notified_that_the_password_must_be_at_least_characters_long($amount_of_characters): void
    {
        Assert::same($this->form_element->get_validation_message('password'), sprintf('Password must be at least %d characters long.', $amount_of_characters));
    }
    #[Then('I should see the customer has not placed any orders yet')]
    public function i_should_see_the_customer_has_not_yet_placed_any_orders(): void
    {
        Assert::false($this->show_page->has_customer_placed_any_orders());
    }
    #[Then('/^I should see that they have placed (\d+) orders? in the ("[^"]+" channel)$/')]
    public function i_should_see_that_they_have_placed_orders_in_the_channel($orders_count, Channel_Interface $channel): void
    {
        Assert::same($this->show_page->get_orders_count_in_channel($channel->get_code()), (int) $orders_count);
    }
    #[Then('/^I should see that the overall total value of all their orders in the ("[^"]+" channel) is "([^"]+)"$/')]
    public function i_should_see_that_the_overall_total_value_of_all_their_orders_in_the_channel_is(Channel_Interface $channel, $orders_value): void
    {
        Assert::same($this->show_page->get_orders_total_in_channel($channel->get_code()), $orders_value);
    }
    #[Then('/^I should see that the average total value of their order in the ("[^"]+" channel) is "([^"]+)"$/')]
    public function i_should_see_that_the_average_total_value_of_their_order_in_the_channel_is(Channel_Interface $channel, $orders_value): void
    {
        Assert::same($this->show_page->get_average_total_in_channel($channel->get_code()), $orders_value);
    }
    private function sort_by(string $order, string $field): void
    {
        $this->index_page->sort_by($field, str_starts_with($order, 'de') ? 'desc' : 'asc');
    }
}
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
namespace Sylius\Behat\Context\Api\Admin;

use Api_Platform\Metadata\Iri_Converter_Interface;
use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Shop_User_Interface;
use Sylius\Component\Customer\Model\Customer_Group_Interface;
use Webmozart\Assert\Assert;
final class Managing_Customers_Context implements Context
{
    use Secure_Password_Trait;
    public const SORT_TYPES = ['ascending' => 'asc', 'descending' => 'desc'];
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Iri_Converter_Interface $iri_converter, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I want to create a new customer')]
    #[When('I want to create a new customer account')]
    public function i_want_to_create_a_new_customer(): void
    {
        $this->client->build_create_request(Resources::CUSTOMERS);
    }
    #[When('/^I want to edit (this customer)$/')]
    #[When('I want to enable :customer')]
    #[When('I want to disable :customer')]
    #[When('I want to verify :customer')]
    public function i_want_to_edit_this_customer(Customer_Interface $customer): void
    {
        $this->client->build_update_request(Resources::CUSTOMERS, (string) $customer->get_id());
    }
    #[When('I browse orders of a customer :customer')]
    public function i_browse_orders_of_a_customer(Customer_Interface $customer): void
    {
        $this->client->index(Resources::ORDERS);
        $this->client->add_filter('customer.id', $customer->get_id());
        $this->client->filter();
    }
    #[When('I specify their email as :email')]
    #[When('I do not specify their email')]
    #[When('I change their email to :email')]
    #[When('I remove its email')]
    public function i_change_their_email_to(?string $email = null): void
    {
        $this->client->add_request_data('email', (string) $email);
    }
    #[When('/^I specify (?:their|his) first name as "([^"]*)"$/')]
    #[When('I remove its first name')]
    public function i_specify_their_first_name_as(?string $name = null): void
    {
        $this->client->add_request_data('firstName', $name);
    }
    #[When('/^I specify (?:their|his) last name as "([^"]*)"$/')]
    #[When('I remove its last name')]
    public function i_specify_their_last_name_as(?string $name = null): void
    {
        $this->client->add_request_data('lastName', $name);
    }
    #[When('I specify its birthday as :birthday')]
    public function i_specify_its_birthday_as(string $birthday): void
    {
        $this->client->add_request_data('birthday', $birthday);
    }
    #[When('I select :gender as its gender')]
    public function i_select_gender(string $gender): void
    {
        $this->client->add_request_data('gender', strtolower(substr($gender, 0, 1)));
    }
    #[When('I select :customerGroup as their group')]
    public function i_select_group(Customer_Group_Interface $customer_group): void
    {
        $this->client->add_request_data('group', $this->iri_converter->get_iri_from_resource($customer_group));
    }
    #[When('I make them subscribed to the newsletter')]
    public function i_make_them_subscribed_to_the_newsletter(): void
    {
        $this->client->add_request_data('subscribedToNewsletter', true);
    }
    #[When('I choose create account option')]
    public function i_choose_create_account_option(): void
    {
        $this->client->add_request_data('user', []);
    }
    #[When('I specify their password as :password')]
    public function i_specify_its_password_as(string $password): void
    {
        $this->client->add_request_data('user', ['plainPassword' => $this->replace_with_secure_password($password)]);
    }
    #[When('/^I (enable|disable) their account$/')]
    public function i_enable_it(string $toggle_action): void
    {
        $this->client->add_request_data('user', ['enabled' => 'enable' === $toggle_action]);
    }
    #[When('I verify it')]
    public function i_verify_it(): void
    {
        $this->client->add_request_data('user', ['verified' => true]);
    }
    #[When('I (try to) add them')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I want to see all customers in store')]
    public function i_want_to_see_all_customers_in_store(): void
    {
        $this->client->index(Resources::CUSTOMERS);
    }
    #[When('I view details of the customer :customer')]
    #[When('/^I view (their) details$/')]
    public function i_view_details_of_the_customer(Customer_Interface $customer): void
    {
        $this->client->show(Resources::CUSTOMERS, (string) $customer->get_id());
    }
    #[When('I filter by group :groupName')]
    #[When('I filter by groups :firstGroup and :secondGroup')]
    public function i_filter_by_group(string ...$groups_names): void
    {
        foreach ($groups_names as $group_name) {
            $this->client->add_filter('group.name[]', $group_name);
        }
        $this->client->filter();
    }
    #[When('I search by :phrase email')]
    public function i_search_by_email(string $phrase): void
    {
        $this->client->add_filter('email', $phrase);
        $this->client->filter();
    }
    #[When('I search by :phrase first name')]
    public function i_search_by_first_name(string $phrase): void
    {
        $this->client->add_filter('firstName', $phrase);
        $this->client->filter();
    }
    #[When('I search by :phrase last name')]
    public function i_search_by_last_name(string $phrase): void
    {
        $this->client->add_filter('lastName', $phrase);
        $this->client->filter();
    }
    #[When('I sort the orders :sortType by channel')]
    public function i_sort_them_by(string $sort_type = 'ascending'): void
    {
        $this->client->sort(['channel.code' => self::SORT_TYPES[$sort_type]]);
    }
    #[When('I sort customers by :sortType registration date')]
    public function i_sort_customers_by_registration_date(string $sort_type): void
    {
        $this->client->sort(['createdAt' => self::SORT_TYPES[$sort_type]]);
    }
    #[When('I sort customers by :sortType email')]
    public function i_sort_customers_by_email(string $sort_type): void
    {
        $this->client->sort(['email' => self::SORT_TYPES[$sort_type]]);
    }
    #[When('I sort customers by :sortType first name')]
    public function i_sort_customers_by_first_name(string $sort_type): void
    {
        $this->client->sort(['firstName' => self::SORT_TYPES[$sort_type]]);
    }
    #[When('I sort customers by :sortType last name')]
    public function i_sort_customers_by_last_name(string $sort_type): void
    {
        $this->client->sort(['lastName' => self::SORT_TYPES[$sort_type]]);
    }
    #[When('I change the password of user :customer to :newPassword')]
    public function i_change_the_password_of_user_to(Customer_Interface $customer, string $new_password): void
    {
        $this->i_want_to_edit_this_customer($customer);
        $this->i_specify_its_password_as($new_password);
        $this->client->update();
    }
    #[When('I delete the account of :shopUser user')]
    public function i_delete_account(Shop_User_Interface $shop_user): void
    {
        $this->shared_storage->set('customer', $shop_user->get_customer());
        $this->client->delete(sprintf('customers/%s', $shop_user->get_customer()->get_id()), 'user');
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Customer could not be created');
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Please enter your %s.', $element));
    }
    #[Then('I should be notified that email must be unique')]
    public function i_should_be_notified_that_email_must_be_unique(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'email: This email is already used.');
    }
    #[Then('/^I should be notified that ([^"]+) should be ([^"]+)$/')]
    public function i_should_be_notified_that_the_element_should_be(string $element_name, string $validation_message): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s must be %s.', ucfirst($element_name), $validation_message));
    }
    #[Then('I should be notified that email is not valid')]
    public function i_should_be_notified_that_email_is_not_valid(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'This email is invalid.');
    }
    #[Then('the customer :customer should appear in the store')]
    #[Then('the customer :customer should still have this email')]
    public function the_customer_should_appear_in_the_store(Customer_Interface $customer): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::CUSTOMERS), 'email', $customer->get_email()), sprintf('Customer with email %s does not exist', $customer->get_email()));
    }
    #[Then('the customer :customer should have an account created')]
    #[Then('/^(this customer) should have an account created$/')]
    public function they_should_have_an_account_created(Customer_Interface $customer): void
    {
        Assert::not_null($customer->get_user()->get_password(), 'Customer should have an account, but they do not.');
    }
    #[Then('I should see :count customers on the list')]
    #[Then('I should see a single customer on the list')]
    public function i_should_see_zones_in_the_list(int $count = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('I should see the customer :email in the list')]
    #[Then('I should see the customer :email on the list')]
    public function i_should_see_the_customer_in_the_list(string $email): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::CUSTOMERS), 'email', $email), sprintf('There is no customer with email "%s"', $email));
    }
    #[Then('I should see a single order in the list')]
    public function i_should_see_a_single_order_in_the_list(): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), 1);
    }
    #[Then('their name should be :name')]
    public function their_name_should_be(string $name): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'fullName', $name));
    }
    #[Then('he should be registered since :registrationDate')]
    public function his_registration_date_should_be(string $registration_date): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'createdAt', $registration_date));
    }
    #[Then('their email should be :email')]
    public function their_email_should_be(string $email): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'email', $email));
    }
    #[Then('their phone number should be :phoneNumber')]
    public function their_phone_number_should_be(string $phone_number): void
    {
        Assert::true($this->response_checker->has_value($this->client->get_last_response(), 'phoneNumber', $phone_number));
    }
    #[Then('their default address should be :firstName :lastName, :street, :postcode :city, :country')]
    public function their_s_default_address_should_be(string $first_name, string $last_name, string $street, string $postcode, string $city, Country_Interface $country): void
    {
        $this->client->show_by_iri($this->response_checker->get_value($this->client->get_last_response(), 'defaultAddress'));
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'firstName'), $first_name);
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'lastName'), $last_name);
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'street'), $street);
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'postcode'), $postcode);
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'city'), $city);
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'countryCode'), $country->get_code());
    }
    #[Then('the province in the default address should be :provinceName')]
    public function the_province_in_the_default_address_should_be(string $province_name): void
    {
        $this->client->show_by_iri($this->response_checker->get_value($this->client->get_last_response(), 'defaultAddress'));
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'provinceName'), $province_name);
    }
    #[Then('I should see information about no existing account for this customer')]
    #[Then('I should not see information about email verification')]
    public function i_should_see_information_about_no_existing_account_for_this_customer(): void
    {
        Assert::null($this->response_checker->get_value($this->client->get_last_response(), 'user'));
    }
    #[Then('I should see that this customer has verified the email')]
    public function i_should_see_that_this_customer_has_verified_the_email(): void
    {
        $user = $this->response_checker->get_value($this->client->get_last_response(), 'user');
        Assert::true($user['verified']);
    }
    #[Then('I should see the order with number :orderNumber in the list')]
    public function i_should_see_the_order_with_number_in_the_list(string $order_number): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->get_last_response(), 'number', $order_number));
    }
    #[Then('I should be notified that the password must be at least :amountOfCharacters characters long')]
    public function i_should_be_notified_that_the_password_must_be_at_least_characters_long(int $amount_of_characters): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Password must be at least %d characters long.', $amount_of_characters));
    }
    #[Then('I should not see the order with number :orderNumber in the list')]
    public function i_should_not_see_a_single_order_from_customer(string $order_number): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->get_last_response(), 'number', $order_number));
    }
    #[Then('/^(this customer) should be (enabled|disabled)$/')]
    public function this_customer_should_be_enabled(Customer_Interface $customer, string $toggle_action): void
    {
        $user = $this->response_checker->get_value($this->client->show(Resources::CUSTOMERS, (string) $customer->get_id()), 'user');
        Assert::same($user['enabled'], 'enabled' === $toggle_action);
    }
    #[Then('/^(this customer) should be verified$/')]
    public function this_customer_should_be_verified(Customer_Interface $customer): void
    {
        $user = $this->response_checker->get_value($this->client->show(Resources::CUSTOMERS, (string) $customer->get_id()), 'user');
        Assert::true($user['verified']);
    }
    #[Then('there should still be only one customer with email :email')]
    public function there_should_still_be_only_one_customer_with_email(string $email): void
    {
        Assert::count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::CUSTOMERS), 'email', $email), 1, sprintf('There is more than one customer with email %s', $email));
    }
    #[Then('/^(this customer) should have an empty first name$/')]
    #[Then('the customer :customer should still have an empty first name')]
    public function the_customer_should_still_have_an_empty_first_name(Customer_Interface $customer): void
    {
        Assert::null($this->response_checker->get_value($this->client->show(Resources::CUSTOMERS, (string) $customer->get_id()), 'firstName'));
    }
    #[Then('/^(this customer) should have an empty last name$/')]
    #[Then('the customer :customer should still have an empty last name')]
    public function the_customer_should_still_have_an_empty_last_name(Customer_Interface $customer): void
    {
        Assert::null($this->response_checker->get_value($this->client->show(Resources::CUSTOMERS, (string) $customer->get_id()), 'lastName'));
    }
    #[Then('the customer with email :email should not appear in the store')]
    public function the_customer_should_not_appear_in_the_store(string $email): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::CUSTOMERS), 'email', $email));
    }
    #[Then('/^(this customer) with name "([^"]*)" should appear in the store$/')]
    public function the_customer_with_name_should_appear_in_the_store(Customer_Interface $customer, string $name): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::CUSTOMERS, (string) $customer->get_id()), 'fullName', $name));
    }
    #[Then('this customer should be subscribed to the newsletter')]
    #[Then('I should see that this customer is subscribed to the newsletter')]
    public function this_customer_should_be_subscribed_to_the_newsletter(): void
    {
        Assert::true($this->response_checker->get_value($this->client->get_last_response(), 'subscribedToNewsletter'));
    }
    #[Then('this customer should have :customerGroup as their group')]
    public function this_customer_should_have_as_their_group(Customer_Group_Interface $customer_group): void
    {
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'group'), $this->iri_converter->get_iri_from_resource($customer_group));
    }
    #[Then('the customer with this email should still exist')]
    public function customer_should_still_exist(): void
    {
        /** @var CustomerInterface $customer */
        $customer = $this->shared_storage->get('customer');
        $this->client->show(Resources::CUSTOMERS, (string) $customer->get_id());
        Assert::same($this->client->get_last_response()->get_status_code(), 200);
        Assert::same($this->response_checker->get_value($this->client->get_last_response(), 'email'), $customer->get_email());
    }
    #[Then('the user account should be deleted')]
    public function account_should_be_deleted(): void
    {
        /** @var CustomerInterface $customer */
        $customer = $this->shared_storage->get('customer');
        $response = $this->client->show(Resources::CUSTOMERS, (string) $customer->get_id());
        Assert::null($this->response_checker->get_value($response, 'user'));
    }
    #[Then('I should not be able to delete it again')]
    public function i_should_not_be_able_to_delete_customer_again(): void
    {
        $customer = $this->shared_storage->get('customer');
        $this->client->delete(sprintf('customer/%s', $customer->get_id()), 'user');
        Assert::same($this->client->get_last_response()->get_status_code(), 404);
    }
    #[Then('/^the (first|last) customer should be "([^"]+)"$/')]
    public function the_first_last_customer_should_be(string $nth, string $email): void
    {
        $customers = $this->response_checker->get_collection($this->client->get_last_response());
        $customer = 'first' === $nth ? reset($customers) : end($customers);
        Assert::same($customer['email'], $email);
    }
}
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
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Shop\Account\Address_Book\Create_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Address_Book\Index_Page_Interface;
use Sylius\Behat\Page\Shop\Account\Address_Book\Update_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Address_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Address_Book_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Repository_Interface $address_repository, private Index_Page_Interface $address_book_index_page, private Create_Page_Interface $address_book_create_page, private Update_Page_Interface $address_book_update_page, private Current_Page_Resolver_Interface $current_page_resolver, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[Given('I am editing the address of :fullName')]
    #[When('I want to edit the address of :fullName')]
    public function i_edit_address_of(string $full_name): void
    {
        $this->shared_storage->set('full_name', $full_name);
        $this->address_book_index_page->open();
        $this->address_book_index_page->edit_address($full_name);
    }
    #[When('I set the address of :fullName as default')]
    public function i_set_the_address_of_as_default($full_name): void
    {
        $this->shared_storage->set('full_name', $full_name);
        $this->address_book_index_page->set_as_default($full_name);
    }
    #[When('I want to add a new address to my address book')]
    public function i_want_to_add_a_new_address_to_my_address_book(): void
    {
        $this->address_book_create_page->open();
    }
    #[Given('I am browsing my address book')]
    #[When('I browse my address book')]
    public function i_browse_my_addresses(): void
    {
        $this->address_book_index_page->open();
    }
    #[When('I specify :provinceName as my province')]
    public function i_specify_as_my_province(string $province_name): void
    {
        $this->address_book_update_page->specify_province($province_name);
    }
    #[When('I choose :provinceName as my province')]
    public function i_choose_as_my_province(string $province_name): void
    {
        $this->address_book_update_page->select_province($province_name);
    }
    #[When('I choose :countryName as my country')]
    public function i_choose_as_my_country($country_name): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->get_current_page();
        $current_page->select_country($country_name);
    }
    #[When('/^I change the ([^"]+) to "([^"]+)"$/')]
    #[When('/^I remove the ([^"]+)$/')]
    public function i_change_my_to(string $field, ?string $value = null): void
    {
        $this->address_book_update_page->fill_field($field, $value);
    }
    #[When('/^I specify the (address as "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)", "([^"]+)")$/')]
    public function i_specify_the_address_as(Address_Interface $address): void
    {
        $this->address_book_create_page->fill_address_data($address);
    }
    #[When('I leave every field empty')]
    public function i_leave_every_field_empty(): void
    {
        // Intentionally left empty
    }
    #[When('I do not specify province')]
    public function i_do_not_specify_province(): void
    {
        // Intentionally left empty
    }
    #[When('I add it')]
    public function i_add_it(): void
    {
        $this->address_book_create_page->add_address();
    }
    #[When('I save my changed address')]
    public function i_save_changed_address(): void
    {
        $this->address_book_update_page->save_changes();
    }
    #[When('I delete the :fullName address')]
    public function i_delete_the_address(string $fullname): void
    {
        $this->address_book_index_page->delete_address($fullname);
    }
    #[When('/^I try to edit the address of "([^"]+)"$/')]
    public function i_try_to_edit_the_address_of($full_name): void
    {
        $address = $this->get_address_of($full_name);
        $this->shared_storage->set('full_name', sprintf('%s %s', $address->get_first_name(), $address->get_last_name()));
        $this->address_book_update_page->try_to_open(['id' => $address->get_id()]);
    }
    #[Then('/^it should contain "([^"]+)"$/')]
    public function it_should_contain(string $value): void
    {
        $full_name = $this->shared_storage->get('full_name');
        Assert::true($this->address_book_index_page->address_of_contains($full_name, $value));
    }
    #[Then('it should contain country :countryName')]
    public function it_should_contain_country(string $country_name): void
    {
        $full_name = $this->shared_storage->get('full_name');
        Assert::true($this->address_book_index_page->address_of_contains($full_name, strtoupper($country_name)));
    }
    #[Then('it should contain province :provinceName')]
    public function it_should_contain_province(string $province_name): void
    {
        $full_name = $this->shared_storage->get('full_name');
        Assert::true($this->address_book_index_page->address_of_contains($full_name, $province_name));
    }
    #[Then('this address should be assigned to :fullName')]
    #[Then('/^the address assigned to "([^"]+)" should (appear|be) in my book$/')]
    public function this_address_should_be_assigned_to(string $full_name): void
    {
        Assert::true($this->address_book_index_page->has_address_of($full_name));
    }
    #[Then('I should still be on the address addition page')]
    public function i_should_still_be_on_address_addition_page(): void
    {
        $this->address_book_create_page->verify();
    }
    #[Then('I should still be on the :fullName address edit page')]
    public function i_should_still_be_on_the_address_edit_page($full_name): void
    {
        $address = $this->get_address_of($full_name);
        Assert::true($this->address_book_update_page->is_open(['id' => $address->get_id()]));
    }
    #[Then('I should still have :value as my specified province')]
    public function i_should_still_have_as_my_specified_province($value): void
    {
        Assert::same($this->address_book_update_page->get_specified_province(), $value);
    }
    #[Then('I should still have :value as my chosen province')]
    public function i_should_still_have_as_my_chosen_province($value): void
    {
        Assert::same($this->address_book_update_page->get_selected_province(), $value);
    }
    #[Then('I should be notified that the province needs to be specified')]
    public function i_should_be_notified_that_the_province_needs_to_be_specified(): void
    {
        Assert::true($this->address_book_create_page->has_province_validation_message());
    }
    #[Then('/^I should be notified about (\d+) errors$/')]
    public function i_should_be_notified_about_errors(int $expected_count): void
    {
        Assert::same($this->address_book_create_page->count_validation_messages(), $expected_count);
    }
    #[Then('there should be no addresses')]
    public function there_should_be_no_addresses(): void
    {
        Assert::true($this->address_book_index_page->has_no_addresses());
    }
    #[Then('I should not see the address assigned to :fullName')]
    public function i_should_not_see_address_of(string $full_name): void
    {
        Assert::false($this->address_book_index_page->has_address_of($full_name));
    }
    #[Then('/^I should(?:| still) have a single address in my address book$/')]
    #[Then('/^I should(?:| still) have (\d+) addresses in my address book$/')]
    public function i_should_have_addresses($count = 1): void
    {
        $this->address_book_index_page->open();
        Assert::same($this->address_book_index_page->get_addresses_count(), (int) $count);
    }
    #[Then('I should be notified that the address has been successfully added')]
    public function i_should_be_notified_that_address_has_been_successfully_added(): void
    {
        $this->notification_checker->check_notification('Address has been successfully added.', Notification_Type::success());
    }
    #[Then('I should be notified that the address has been successfully deleted')]
    public function i_should_be_notified_about_successful_delete(): void
    {
        $this->notification_checker->check_notification('Address has been successfully deleted.', Notification_Type::success());
    }
    #[Then('I should be unable to edit their address')]
    public function i_should_be_unable_to_edit_their_address(): void
    {
        $address = $this->get_address_of($this->shared_storage->get_latest_resource());
        Assert::false($this->address_book_update_page->is_open(['id' => $address->get_id()]));
    }
    #[Then('I should be notified that the address has been successfully updated')]
    public function i_should_be_notified_about_successful_update(): void
    {
        $this->notification_checker->check_notification('Address has been successfully updated.', Notification_Type::success());
    }
    #[Then('I should be notified that the address has been set as default')]
    public function i_should_be_notified_that_address_has_been_set_as_default(): void
    {
        $this->notification_checker->check_notification('Address has been set as default', Notification_Type::success());
    }
    #[Then('I should not have a default address')]
    public function i_should_have_no_default_address(): void
    {
        Assert::true($this->address_book_index_page->has_no_default_address());
    }
    #[Then('/^(address "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+"(?:|, "[^"]+")) should(?:| still) be marked as my default address$/')]
    #[Then('/^(address "[^"]+", "[^"]+", "[^"]+", "[^"]+", "[^"]+"(?:|, "[^"]+")) should(?:| still) be set as my default address$/')]
    public function address_should_be_marked_as_my_default_address(Address_Interface $address): void
    {
        $actual_full_name = $this->address_book_index_page->get_full_name_of_default_address();
        $expected_full_name = sprintf('%s %s', $address->get_first_name(), $address->get_last_name());
        Assert::same($actual_full_name, $expected_full_name);
    }
    #[Then('I should be able to update it without unexpected alert')]
    public function i_should_be_able_to_update_it_without_unexpected_alert(): void
    {
        $this->address_book_update_page->wait_for_form_to_stop_loading();
    }
    /**
     * @param string $fullName
     *
     * @return AddressInterface
     */
    private function get_address_of($full_name)
    {
        [$first_name, $last_name] = explode(' ', $full_name);
        /** @var AddressInterface $address */
        $address = $this->address_repository->find_one_by(['firstName' => $first_name, 'lastName' => $last_name]);
        Assert::not_null($address);
        return $address;
    }
    private function get_current_page(): \Sylius\Behat\Page\Sylius_Page_Interface
    {
        return $this->current_page_resolver->get_current_page_with_form([$this->address_book_create_page, $this->address_book_update_page]);
    }
}
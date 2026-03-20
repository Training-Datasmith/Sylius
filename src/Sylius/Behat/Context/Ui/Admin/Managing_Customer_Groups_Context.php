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
use Sylius\Behat\Element\Admin\Customer_Group\Form_Element_Interface;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Component\Customer\Model\Customer_Group_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Customer_Groups_Context implements Context
{
    public function __construct(private Create_Page_Interface $create_page, private Index_Page_Interface $index_page, private Update_Page_Interface $update_page, private Form_Element_Interface $form_element)
    {
    }
    #[When('I want to create a new customer group')]
    public function i_want_to_create_a_new_customer_group(): void
    {
        $this->create_page->open();
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->form_element->fill_element($code ?? '', 'code');
    }
    #[When('I specify a too long code')]
    public function i_specify_a_too_long_code(): void
    {
        $this->form_element->fill_element(str_repeat('a', 256), 'code');
    }
    #[When('I specify its name as :name')]
    #[When('I remove its name')]
    public function i_specify_its_name_as(?string $name = null): void
    {
        $this->form_element->fill_element($name ?? '', 'name');
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('/^I want to edit (this customer group)$/')]
    public function i_want_to_edit_this_customer_group(Customer_Group_Interface $customer_group): void
    {
        $this->update_page->open(['id' => $customer_group->get_id()]);
    }
    #[When('I check (also) the :customerGroupName customer group')]
    public function i_check_the_customer_group(string $customer_group_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $customer_group_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[When('I browse customer groups')]
    #[When('I want to browse customer groups')]
    public function i_want_to_browse_customer_groups(): void
    {
        $this->index_page->open();
    }
    #[When('/^I sort them by the (code|name) in (asc|desc)ending order$/')]
    public function i_sort_them_by_the_field(string $field, string $order): void
    {
        $this->index_page->sort_by($field, $order);
    }
    #[Then('the customer group :customerGroup should appear in the store')]
    public function the_customer_group_should_appear_in_the_store(Customer_Group_Interface $customer_group): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $customer_group->get_name()]));
    }
    #[Then('this customer group with name :name should appear in the store')]
    #[Then('I should see the customer group :name in the list')]
    public function this_customer_group_with_name_should_appear_in_the_store(string $name): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $name]));
    }
    #[Then('there should be :amountOfCustomerGroups customer groups in the list')]
    public function there_should_be_customer_groups_in_the_list(int $amount_of_customer_groups = 1): void
    {
        Assert::same($this->index_page->count_items(), $amount_of_customer_groups);
    }
    /**
     * This step is a duplicate of the above because some scenarios require to open the index page before checking anything
     */
    #[Then('I should see a single customer group in the list')]
    #[Then('I should see :amountOfCustomerGroups customer groups in the list')]
    public function i_should_see_customer_groups_in_the_list(int $amount_of_customer_groups = 1): void
    {
        $this->index_page->open();
        Assert::same($this->index_page->count_items(), $amount_of_customer_groups);
    }
    #[Then('/^the (\d+)(?:|st|nd|rd|th) customer group on the list should have (name|code) "([^"]+)" and (name|code) "([^"]+)"$/')]
    public function the_first_customer_group_on_the_list_should_have(int $position, string $first_field, string $first_value, string $second_field, string $second_value): void
    {
        $fields = $this->index_page->get_column_fields($first_field);
        Assert::same($fields[$position - 1], $first_value);
        $fields = $this->index_page->get_column_fields($second_field);
        Assert::same($fields[$position - 1], $second_value);
    }
    #[Then('/^(this customer group) should still be named "([^"]+)"$/')]
    public function this_customer_group_should_still_be_named(Customer_Group_Interface $customer_group, string $customer_group_name): void
    {
        $this->i_want_to_browse_customer_groups();
        Assert::same($customer_group->get_name(), $customer_group_name);
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $customer_group_name]));
    }
    #[Then('I should be notified that name is required')]
    public function i_should_be_notified_that_name_is_required(): void
    {
        Assert::same($this->form_element->get_validation_message('name'), 'Please enter a customer group name.');
    }
    #[Then('I should be notified that customer group with this code already exists')]
    public function i_should_be_notified_that_customer_group_with_this_code_already_exists(): void
    {
        Assert::same($this->form_element->get_validation_message('code'), 'Customer group code has to be unique.');
    }
    #[Then('I should be notified that code is too long')]
    public function i_should_be_notified_that_code_is_too_long(): void
    {
        Assert::contains($this->form_element->get_validation_message('code'), 'must not be longer than 255 characters.');
    }
    #[Then('I should be informed that this form contains errors')]
    public function i_should_be_informed_that_this_form_contains_errors(): void
    {
        Assert::true($this->form_element->has_form_error_alert());
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->form_element->is_code_disabled());
    }
    #[When('I delete the :customerGroup customer group')]
    public function i_delete_the_customer_group(Customer_Group_Interface $customer_group): void
    {
        $this->i_want_to_browse_customer_groups();
        $this->index_page->delete_resource_on_page(['name' => $customer_group->get_name()]);
    }
    #[Then('/^(this customer group) should no longer exist in the registry$/')]
    public function this_customer_group_should_no_longer_exist_in_the_registry(Customer_Group_Interface $customer_group): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['name' => $customer_group->get_name()]), sprintf('Customer group %s should no longer exist in the registry', $customer_group->get_name()));
    }
}
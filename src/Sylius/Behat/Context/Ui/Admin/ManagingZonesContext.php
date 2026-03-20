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
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Admin\Zone\Form_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Zones_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page, private Create_Page_Interface $create_page, private Update_Page_Interface $update_page, private Form_Element_Interface $form_element, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[When('I want to create a new zone consisting of :memberType')]
    public function i_want_to_create_a_new_zone_with_members(string $member_type): void
    {
        $this->create_page->open(['type' => $member_type]);
    }
    #[When('I browse zones')]
    #[When('I want to see all zones in store')]
    public function i_want_to_see_all_zones_in_store(): void
    {
        $this->index_page->open();
    }
    #[When('/^I want to modify the (zone named "[^"]+")$/')]
    public function i_want_to_modify_a_zone_named(Zone_Interface $zone): void
    {
        $this->update_page->open(['id' => $zone->get_id()]);
    }
    #[When('/^I(?:| try to) delete the (zone named "([^"]+)")$/')]
    public function i_delete_zone_named(Zone_Interface $zone): void
    {
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['name' => $zone->get_name(), 'code' => $zone->get_code()]);
    }
    #[When('/^I(?:| also) remove the "([^"]+)" (?:country|province|zone) member$/')]
    #[When('/^I(?:| also) remove the "([^"]+)", "([^"]+)" and "([^"]+)" (?:country|province|zone) members$/')]
    public function i_remove_members(string ...$members): void
    {
        foreach ($members as $member) {
            $this->form_element->remove_member($member);
        }
    }
    #[When('I name it :name')]
    #[When('I rename it to :name')]
    #[When('I do not specify its name')]
    public function i_name_it(string $name = ''): void
    {
        $this->form_element->name_it($name);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(string $code = ''): void
    {
        $this->form_element->specify_code($code);
    }
    #[When('I specify a too long code')]
    public function i_specify_a_too_long(): void
    {
        $this->form_element->specify_code(str_repeat('a', 256));
    }
    #[When('I do not add a country member')]
    public function i_do_not_add_a_country_member(): void
    {
        // Intentionally left blank to fulfill context expectation
    }
    #[When('/^I add a (?:country|province|zone) "([^"]+)"$/')]
    public function i_add_a_zone_member(string $name): void
    {
        $this->form_element->add_member();
        $this->form_element->choose_member($name);
    }
    #[When('I select its scope as :scope')]
    public function i_select_its_scope_as(string $scope): void
    {
        $this->form_element->select_scope($scope);
    }
    #[When('I set its priority to :priority')]
    public function i_set_its_priority_to(int $priority): void
    {
        $this->form_element->prioritize_it($priority);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I check (also) the :zoneName zone')]
    public function i_check_the_zone(string $zone_name): void
    {
        $this->index_page->check_resource_on_page(['name' => $zone_name]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[Then('/^the (zone named "[^"]+") with the "([^"]+)" (?:country|province|zone) member should appear in the registry$/')]
    public function the_zone_with_the_country_should_appear_in_the_registry(Zone_Interface $zone, string $member): void
    {
        $this->update_page->open(['id' => $zone->get_id()]);
        Assert::same($this->form_element->get_name(), $zone->get_name());
        Assert::true($this->form_element->has_member($member), sprintf('Zone %s has no member %s', $zone->get_name(), $member));
    }
    #[Given('its scope should be :scope')]
    public function its_scope_should_be(string $scope): void
    {
        Assert::same($this->form_element->get_scope(), $scope);
    }
    #[Then('/^(this zone) should have only the "([^"]*)" (?:country|province|zone) member$/')]
    public function this_zone_should_have_only_the_member(Zone_Interface $zone, string $member): void
    {
        $this->update_page->open(['id' => $zone->get_id()]);
        Assert::same($this->form_element->count_members(), 1);
        Assert::true($this->form_element->has_member($member), sprintf('Zone %s has no member %s', $zone->get_name(), $member));
    }
    #[Then('/^(this zone) name should be "([^"]*)"/')]
    public function this_zone_name_should_be(Zone_Interface $zone, string $name): void
    {
        $this->update_page->open(['id' => $zone->get_id()]);
        Assert::same($this->form_element->get_name(), $name);
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->form_element->is_code_disabled());
    }
    #[Then('/^I should be notified that zone with this code already exists$/')]
    public function i_should_be_notified_that_zone_with_this_code_already_exists(): void
    {
        Assert::same($this->form_element->get_validation_message('code'), 'Zone code must be unique.');
    }
    #[Then('/^there should still be only one zone with code "([^"]*)"$/')]
    public function there_should_still_be_only_one_zone_with_code(string $code): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $code]));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        Assert::same($this->form_element->get_validation_message($element), sprintf('Please enter zone %s.', $element));
    }
    #[Then('zone with :element :value should not be added')]
    public function zone_with_name_should_not_be_added(string $element, string $value): void
    {
        $this->index_page->open();
        Assert::false($this->index_page->is_single_resource_on_page([$element => $value]));
    }
    #[Then('/^I should be notified that at least one zone member is required$/')]
    public function i_should_be_notified_that_at_least_one_zone_member_is_required(): void
    {
        Assert::same($this->form_element->get_form_validation_message(), 'Please add at least 1 zone member.');
    }
    #[Then('I should not be able to edit its type')]
    public function i_should_not_be_able_to_edit_its_type(): void
    {
        Assert::true($this->form_element->is_type_field_disabled());
    }
    #[Then('it should be of :type type')]
    public function it_should_be_of_type(string $type): void
    {
        Assert::same(strtolower($this->form_element->get_type()), strtolower($type));
    }
    #[Then('the zone named :zoneName should no longer exist in the registry')]
    public function this_zone_should_no_longer_exist_in_the_registry(string $zone_name): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['name' => $zone_name]));
    }
    #[Then('I should see a single zone in the list')]
    #[Then('I should see :amount zones in the list')]
    public function i_should_see_zones_in_the_list(int $amount = 1): void
    {
        Assert::same($this->index_page->count_items(), $amount);
    }
    #[Then('/^I should(?:| still) see the (zone named "([^"]+)") in the list$/')]
    public function i_should_see_the_zone_named_in_the_list(Zone_Interface $zone): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $zone->get_code(), 'name' => $zone->get_name()]));
    }
    #[Then('I should be notified that the zone is in use and cannot be deleted')]
    #[Then('I should be notified that this zone cannot be deleted')]
    public function i_should_be_notified_that_the_zone_is_in_use_and_cannot_be_deleted(): void
    {
        $this->notification_checker->check_notification('Error Cannot delete, the Zone is in use.', Notification_Type::failure());
    }
    #[Then('I should be notified that code is too long')]
    public function i_should_be_notified_that_code_is_too_long(): void
    {
        Assert::contains($this->form_element->get_validation_message('code'), 'must not be longer than 255 characters.');
    }
    #[Then('/^I should not be able to add the "([^"]+)" (?:country|province|zone) as a member$/')]
    public function i_should_not_be_able_to_add_the_member(string $name): void
    {
        $this->form_element->add_member();
        try {
            $this->form_element->choose_member($name);
        } catch (Element_Not_Found_Exception) {
            return;
        }
        throw new \InvalidArgumentException(sprintf('Member "%s" should not be selectable.', $name));
    }
    #[Then('the first zone on the list should have :field :value')]
    public function the_first_zone_on_the_list_should_have(string $field, string $value): void
    {
        $fields = $this->index_page->get_column_fields($field);
        $actual_value = reset($fields);
        Assert::same($actual_value, $value, sprintf('Expected first zone\'s %s to be "%s", but it is "%s".', $field, $value, $actual_value));
    }
    #[Then('the last zone on the list should have :field :value')]
    public function the_last_zone_on_the_list_should_have(string $field, string $value): void
    {
        $fields = $this->index_page->get_column_fields($field);
        $actual_value = end($fields);
        Assert::same($actual_value, $value, sprintf('Expected last zone\'s %s to be "%s", but it is "%s".', $field, $value, $actual_value));
    }
    #[Given('the :zone zone should have priority :priority')]
    public function the_zone_should_have_priority(Zone_Interface $zone, int $priority): void
    {
        $this->i_want_to_modify_a_zone_named($zone);
        Assert::same($this->form_element->get_priority(), $priority);
    }
}
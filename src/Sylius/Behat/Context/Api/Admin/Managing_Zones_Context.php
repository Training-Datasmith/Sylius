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

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Admin\Helper\Validation_Trait;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Addressing\Model\Zone_Member_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Zones_Context implements Context
{
    use Validation_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I want to create a new zone consisting of :memberType')]
    public function i_want_to_create_a_new_zone_consisting_of_country(string $member_type): void
    {
        $this->client->build_create_request(Resources::ZONES);
        $this->client->add_request_data('type', $member_type);
    }
    #[When('I name it :name')]
    #[When('I rename it to :name')]
    public function i_name_it(string $name): void
    {
        $this->client->add_request_data('name', $name);
    }
    #[When('I specify its code as :code')]
    public function i_specify_its_code_as(string $code): void
    {
        $this->client->add_request_data('code', $code);
    }
    #[When('I do not specify its :type')]
    #[When('I do not add a country member')]
    public function i_do_not_specify_its_field(): void
    {
        // Intentionally left blank
    }
    #[When('I add a country :country')]
    public function i_add_a_country(Country_Interface $country): void
    {
        $this->client->add_sub_resource_data('members', ['code' => $country->get_code()]);
    }
    #[When('I add a province :province')]
    public function i_add_a_province(Province_Interface $province): void
    {
        $this->client->add_sub_resource_data('members', ['code' => $province->get_code()]);
    }
    #[When('I add a zone :zone')]
    public function i_add_a_zone(Zone_Interface $zone): void
    {
        $this->client->add_sub_resource_data('members', ['code' => $zone->get_code()]);
    }
    #[When('I add a member with a code :code')]
    public function i_add_a_member_with_a_code(string $code): void
    {
        $this->client->add_sub_resource_data('members', ['code' => $code]);
    }
    #[When('I provide a too long zone member code')]
    public function i_provide_a_too_long_zone_member_code(): void
    {
        $this->client->add_sub_resource_data('members', ['code' => str_repeat('a', $this->get_max_code_length() + 1)]);
    }
    #[When('I select its scope as :scope')]
    public function i_select_its_scope_as(string $scope): void
    {
        $this->client->add_request_data('scope', $scope);
    }
    #[When('I set its priority to :priority')]
    public function i_sets_its_priority_to(int $priority): void
    {
        $this->client->add_request_data('priority', $priority);
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I want to see all zones in store')]
    #[When('I browse zones')]
    public function i_want_to_see_all_zones_in_store(): void
    {
        $this->client->index(Resources::ZONES);
    }
    #[When('/^I(?:| try to) delete the (zone named "([^"]*)")$/')]
    public function i_delete_zone_named(Zone_Interface $zone): void
    {
        $this->client->delete(Resources::ZONES, $zone->get_code());
    }
    #[When('I want to modify the zone named :zone')]
    public function i_want_to_modify_the_zone_named(Zone_Interface $zone): void
    {
        $this->shared_storage->set('zone', $zone);
        $this->client->build_update_request(Resources::ZONES, $zone->get_code());
    }
    #[When('/^I(?:| also) remove the ("([^"]+)" country) member$/')]
    public function i_remove_the_country_member(Country_Interface $country): void
    {
        $this->remove_zone_member($country);
    }
    #[When('/^I(?:| also) remove the ("([^"]+)", "([^"]+)" and "([^"]+)" country) members$/')]
    public function i_remove_country_members(array $countries): void
    {
        foreach ($countries as $country) {
            $this->remove_zone_member($country);
        }
    }
    #[When('I remove the :province province member')]
    public function i_remove_the_province_member(Province_Interface $province): void
    {
        $this->remove_zone_member($province);
    }
    #[When('I remove the :zone zone member')]
    public function i_remove_the_zone_member(Zone_Interface $zone): void
    {
        $this->remove_zone_member($zone);
    }
    #[When('I add the country :country again')]
    public function i_add_the_country_to_the_zone_named_again(Country_Interface $country): void
    {
        $this->i_want_to_modify_the_zone_named($this->shared_storage->get('zone'));
        $this->client->add_sub_resource_data('members', ['code' => $country->get_code()]);
        $this->client->update();
    }
    #[Then('the zone named :zone with the :country country member should appear in the registry')]
    public function the_zone_named_with_the_country_member_should_appear_in_the_registry(Zone_Interface $zone, Country_Interface $country): void
    {
        $members = $this->response_checker->get_value($this->client->get_last_response(), 'members');
        Assert::in_array($country->get_code(), array_column($members, 'code'));
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        $this->client->add_request_data('code', 'NEW_CODE');
        Assert::false($this->response_checker->has_value($this->client->update(), 'code', 'NEW_CODE'), 'The code field with value NEW_CODE exists');
    }
    #[Then('/^I should not be able to add the ("[^"]+" zone) as a member$/')]
    public function i_should_not_be_able_to_add_zone_as_a_member(Zone_Interface $zone): void
    {
        $this->client->add_sub_resource_data('members', ['code' => $zone->get_code()]);
        Assert::contains($this->response_checker->get_error($this->client->update()), 'members: Zone member cannot be the same as a zone.');
    }
    #[Then('the zone named :zone with the :province province member should appear in the registry')]
    public function the_zone_named_with_the_province_member_should_appear_in_the_registry(Zone_Interface $zone, Province_Interface $province): void
    {
        $members = $this->response_checker->get_value($this->client->get_last_response(), 'members');
        Assert::in_array($province->get_code(), array_column($members, 'code'));
    }
    #[Then('the zone named :zone with the :otherZone zone member should appear in the registry')]
    public function the_zone_named_with_the_zone_member_should_appear_in_the_registry(Zone_Interface $zone, Zone_Interface $other_zone): void
    {
        $members = $this->response_checker->get_value($this->client->get_last_response(), 'members');
        Assert::in_array($other_zone->get_code(), array_column($members, 'code'));
    }
    #[Then('its scope should be :scope')]
    public function its_scope_should_be(string $scope): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::ZONES, 'EU'), 'scope', $scope), sprintf('Its Zone does not have %s scope', $scope));
    }
    #[Then('I should see :count zones in the list')]
    #[Then('I should see a single zone in the list')]
    public function i_should_see_zones_in_the_list(int $count = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->index(Resources::ZONES)), $count);
    }
    #[Then('I should see the zone named :name in the list')]
    #[Then('I should still see the zone named :name in the list')]
    public function i_should_see_the_zone_named_in_the_list(string $name): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::ZONES), 'name', $name), sprintf('There is no zone with name "%s"', $name));
    }
    #[Then('there should still be only one zone with code :code')]
    public function there_should_still_be_only_one_zone_with_code(string $code): void
    {
        Assert::count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::ZONES), 'code', $code), 1, sprintf('There should be only one zone with code "%s"', $code));
    }
    #[Then('the zone named :name should no longer exist in the registry')]
    public function the_zone_named_should_no_longer_exist_in_the_registry(string $name): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::ZONES), 'name', $name), sprintf('Zone with name %s exists', $name));
    }
    #[Then('/^zone with (code|name) "([^"]*)" should not be added$/')]
    public function zone_should_not_be_added(string $field, string $value): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::ZONES), $field, $value), sprintf('Zone with %s %s exists', $field, $value));
    }
    #[Then('/^(this zone) should have only (the "([^"]*)" (?:country|province|zone) member)$/')]
    public function this_zone_should_have_only_the_province_member(Zone_Interface $zone, Zone_Member_Interface $zone_member): void
    {
        $members = $this->response_checker->get_value($this->client->get_last_response(), 'members');
        Assert::in_array($zone_member->get_code(), array_column($members, 'code'));
        Assert::count($members, 1);
    }
    #[Then('/^(this zone) should have ("([^"]+)" and "([^"]+)" country members)$/')]
    public function this_zone_should_have_the_country_and_the_province_members(Zone_Interface $zone, array $zone_members): void
    {
        $response = $this->client->sub_resource_index(Resources::ZONES, 'members', $zone->get_code());
        foreach ($zone_members as $zone_member) {
            Assert::true($this->response_checker->has_item_with_value($response, 'code', $zone_member->get_code()));
        }
        Assert::same($this->response_checker->count_collection_items($response), 2);
    }
    #[Then('/^(this zone) name should be "([^"]*)"$/')]
    public function this_zone_name_should_be(Zone_Interface $zone, string $name): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::ZONES, $zone->get_code()), 'name', $name), sprintf('Its Zone does not have name %s.', $name));
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Zone could not be created');
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Zone could not be deleted');
    }
    #[Then('I should be notified that this zone cannot be deleted')]
    #[Then('I should be notified that the zone is in use and cannot be deleted')]
    public function i_should_be_notified_that_this_zone_cannot_be_deleted(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot delete, the zone is in use.');
    }
    #[Then('I should be notified that zone with this code already exists')]
    public function i_should_be_notified_that_zone_with_this_code_already_exists(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'code: Zone code must be unique.');
    }
    #[Then('/^I should be notified that (code|name) is required$/')]
    public function i_should_be_notified_that_is_required(string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Please enter zone %s.', $element));
    }
    #[Then('I should be notified that at least one zone member is required')]
    public function i_should_be_notified_that_at_least_one_zone_member_is_required(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'members: Please add at least 1 zone member.');
    }
    #[Then('I should be informed that the provided zone member code is too long')]
    public function i_should_be_notified_that_the_zone_member_code_is_too_long(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'The zone member code must not be longer than');
    }
    #[Then('/^I should be notified that "([^"]*)" is not a valid (country|province|zone) code$/')]
    public function i_should_be_notified_that_is_not_a_valid_element_code(string $code, string $element): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s with code %s does not exist.', ucfirst($element), $code));
    }
    #[Then('I should be notified that :type is not a valid zone type')]
    public function i_should_be_notified_that_is_not_a_valid_zone_type(string $type): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Type "%s" is invalid. Allowed types are:', $type));
    }
    #[Then('/^the (first|last) zone on the list should have ([^"]+) "([^"]+)"$/')]
    public function the_first_zone_on_the_list_should_have(string $toggle_position, string $field, string $value): void
    {
        $items = $this->response_checker->get_value($this->client->get_last_response(), 'hydra:member');
        if ('first' === $toggle_position) {
            $item = reset($items);
        } else {
            $item = end($items);
        }
        Assert::same($item[$field], $value);
    }
    #[Then('the :zone zone should have priority :priority')]
    public function the_zone_should_have_priority(Zone_Interface $zone, int $priority): void
    {
        Assert::true($this->response_checker->has_item_with_values($this->client->index(Resources::ZONES), ['name' => $zone->get_name(), 'priority' => $priority]));
    }
    private function remove_zone_member(Country_Interface|Province_Interface|Zone_Interface $object_to_remove): void
    {
        $members = $this->client->get_content()['members'];
        foreach ($members as $key => $member) {
            if ($member['code'] === $object_to_remove->get_code()) {
                unset($members[$key]);
                break;
            }
        }
        $this->client->set_sub_resource_data('members', $members);
    }
}
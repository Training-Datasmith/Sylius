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
use Sylius\Behat\Element\Admin\Top_Bar_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Administrator\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Administrator\Update_Page_Interface;
use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Administrators_Context implements Context
{
    public function __construct(private Create_Page_Interface $create_page, private Index_Page_Interface $index_page, private Update_Page_Interface $update_page, private Top_Bar_Element_Interface $top_bar_element, private Notification_Checker_Interface $notification_checker, private Repository_Interface $admin_user_repository, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I want to create a new administrator')]
    public function i_want_to_create_a_new_administrator(): void
    {
        $this->create_page->open();
    }
    #[Given('/^I am editing (my) details$/')]
    #[When('/^I want to edit (this administrator)$/')]
    public function i_want_to_edit_this_administrator(Admin_User_Interface $admin_user): void
    {
        $this->update_page->open(['id' => $admin_user->get_id()]);
    }
    #[When('I browse administrators')]
    #[When('I want to browse administrators')]
    public function i_want_to_browse_administrators(): void
    {
        $this->index_page->open();
    }
    #[When('I try to browse administrators')]
    public function i_try_to_browse_administrators(): void
    {
        $this->index_page->try_to_open();
    }
    #[When('I specify its name as :username')]
    #[When('I do not specify its name')]
    #[When('I change its name to :username')]
    public function i_specify_its_name_as($username = null): void
    {
        $this->create_page->set_username($username ?? '');
    }
    #[When('I specify its :field as too long string')]
    public function i_specify_its_field_as_too_long_string(string $field): void
    {
        match ($field) {
            'first name' => $this->create_page->set_first_name($this->get_too_long_string()),
            'last name' => $this->create_page->set_last_name($this->get_too_long_string()),
            'username' => $this->create_page->set_username($this->get_too_long_string()),
        };
    }
    #[When('I specify its email as :email')]
    #[When('I do not specify its email')]
    #[When('I change its email to :email')]
    public function i_specify_its_email_as($email = null): void
    {
        $this->create_page->set_email($email ?? '');
    }
    #[When('I specify its locale as :localeCode')]
    public function i_specify_its_locale_as(string $locale_code): void
    {
        $this->create_page->set_locale($locale_code);
    }
    #[When('I set my locale to :localeCode')]
    public function i_set_my_locale_to(string $locale_code): void
    {
        $this->update_page->set_locale($locale_code);
        $this->update_page->save_changes();
    }
    #[When('I specify its password as :password')]
    #[When('I do not specify its password')]
    #[When('I change its password to :password')]
    public function i_specify_its_password_as($password = null): void
    {
        $this->create_page->set_password($password ?? '');
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->create_page->enable();
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I delete administrator with email :email')]
    public function i_delete_administrator_with_email($email): void
    {
        $this->index_page->delete_resource_on_page(['email' => $email]);
    }
    #[When('I check (also) the :email administrator')]
    public function i_check_the_administrator(string $email): void
    {
        $this->index_page->check_resource_on_page(['email' => $email]);
    }
    #[When('I delete them')]
    public function i_delete_them(): void
    {
        $this->index_page->bulk_delete();
    }
    #[When('I upload the :avatar image as the avatar')]
    public function i_upload_the_image_as_the_avatar(string $avatar): void
    {
        $this->create_page->attach_avatar($avatar);
    }
    #[When('/^I (?:upload|update) the "([^"]+)" image as (my) avatar$/')]
    public function i_upload_the_image_as_my_avatar(string $avatar, Admin_User_Interface $administrator): void
    {
        $path = $this->update_avatar($avatar, $administrator);
        $this->shared_storage->set($avatar, $path);
    }
    #[Then('the administrator :email should appear in the store')]
    #[Then('I should see the administrator :email in the list')]
    #[Then('there should still be only one administrator with an email :email')]
    public function the_administrator_should_appear_in_the_store($email): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['email' => $email]));
    }
    #[Then('this administrator with name :username should appear in the store')]
    #[Then('there should still be only one administrator with name :username')]
    public function this_administrator_with_name_should_appear_in_the_store($username): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['username' => $username]));
    }
    #[Then('I should see a single administrator in the list')]
    #[Then('/^there should be (\d+) administrators in the list$/')]
    public function i_should_see_administrators_in_the_list(int $number = 1): void
    {
        Assert::same($this->index_page->count_items(), $number);
    }
    #[When('I remove the avatar')]
    public function i_remove_the_avatar_image(): void
    {
        $this->update_page->remove_avatar();
    }
    #[Then('I should be notified that email must be unique')]
    public function i_should_be_notified_that_email_must_be_unique(): void
    {
        Assert::same($this->create_page->get_validation_message('field_email'), 'This email is already used.');
    }
    #[Then('I should be notified that name must be unique')]
    public function i_should_be_notified_that_name_must_be_unique(): void
    {
        Assert::same($this->create_page->get_validation_message('field_username'), 'This username is already used.');
    }
    #[Then('I should be notified that the :elementName is required')]
    public function i_should_be_notified_that_first_name_is_required($element_name): void
    {
        Assert::same($this->create_page->get_validation_message(sprintf('%s_%s', 'field', $element_name)), sprintf('Please enter your %s.', $element_name));
    }
    #[Then('I should be notified that this email is not valid')]
    public function i_should_be_notified_that_email_is_not_valid(): void
    {
        Assert::same($this->create_page->get_validation_message('field_email'), 'This email is invalid.');
    }
    #[Then('I should be notified that this :field is too long')]
    public function i_should_be_notified_that_this_field_is_too_long(string $field): void
    {
        match ($field) {
            'first name' => Assert::same($this->create_page->get_validation_message('field_first_name'), 'First name must not be longer than 255 characters.'),
            'last name' => Assert::same($this->create_page->get_validation_message('field_last_name'), 'Last name must not be longer than 255 characters.'),
            'username' => Assert::same($this->create_page->get_validation_message('field_username'), 'Username must not be longer than 255 characters.'),
        };
    }
    #[Then('there should not be :email administrator anymore')]
    public function there_should_be_no_anymore($email): void
    {
        Assert::false($this->index_page->is_single_resource_on_page(['email' => $email]));
    }
    #[Then('I should be notified that it cannot be deleted')]
    public function i_should_be_notified_that_it_cannot_be_deleted(): void
    {
        $this->notification_checker->check_notification('Cannot remove currently logged in user.', Notification_Type::failure());
    }
    #[Then('/^I should see the "([^"]*)" image as (my) avatar$/')]
    public function i_should_see_the_image_as_my_avatar(string $avatar, Admin_User_Interface $administrator): void
    {
        /** @var AdminUserInterface $administrator */
        $administrator = $this->admin_user_repository->find_one_by(['id' => $administrator->get_id()]);
        $this->update_page->open(['id' => $administrator->get_id()]);
        Assert::same($this->shared_storage->get($avatar), $administrator->get_avatar()->get_path());
    }
    #[Then('/^I should see the "([^"]*)" avatar image in the top bar next to my name$/')]
    public function i_should_see_the_avatar_image_in_the_top_bar_next_to_my_name(string $avatar): void
    {
        Assert::true($this->top_bar_element->has_avatar_in_main_bar($avatar));
    }
    #[Then('I should not see the :avatar avatar image in the additional information section of my account')]
    public function i_should_not_see_the_avatar_image_in_the_additional_information_section_of_my_account(string $avatar): void
    {
        $avatar_path = $this->shared_storage->get($avatar);
        Assert::false($this->update_page->has_avatar($avatar_path));
    }
    #[Then('I should not see the :avatar avatar image in the top bar next to my name')]
    public function i_should_not_see_the_avatar_image_in_the_top_bar_next_to_my_name(string $avatar): void
    {
        $avatar_path = $this->shared_storage->get($avatar);
        Assert::false($this->top_bar_element->has_avatar_in_main_bar($avatar_path), 'Avatar should not be present in the top bar');
        Assert::true($this->top_bar_element->has_default_avatar_in_main_bar(), 'Default avatar should be present in the top bar');
    }
    #[Then('I should not see any image as the avatar')]
    public function i_should_not_see_any_image_as_the_avatar(): void
    {
        Assert::false($this->create_page->is_avatar_attached());
    }
    private function get_administrator(Admin_User_Interface $administrator): Admin_User_Interface
    {
        /** @var AdminUserInterface $administrator */
        $administrator = $this->admin_user_repository->find_one_by(['id' => $administrator->get_id()]);
        return $administrator;
    }
    private function get_path(Admin_User_Interface $administrator): string
    {
        $administrator = $this->get_administrator($administrator);
        $avatar = $administrator->get_avatar();
        if (null === $avatar) {
            return '';
        }
        return $avatar->get_path() ?? '';
    }
    private function update_avatar(string $avatar, Admin_User_Interface $administrator): string
    {
        $this->update_page->attach_avatar($avatar);
        $this->update_page->save_changes();
        return $this->get_path($administrator);
    }
    private function get_too_long_string(): string
    {
        return str_repeat('a', 256);
    }
}
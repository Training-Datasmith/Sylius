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
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Request_Builder;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Context\Ui\Admin\Helper\Secure_Password_Trait;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Symfony\Component\Http_Foundation\File\Uploaded_File;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Contracts\Translation\Translator_Interface;
use Webmozart\Assert\Assert;
final class Managing_Administrators_Context implements Context
{
    use Secure_Password_Trait;
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage, private \ArrayAccess $mink_parameters, private Translator_Interface $translator)
    {
    }
    #[Given('/^I am editing (my) details$/')]
    #[When('/^I want to edit (this administrator)$/')]
    public function i_want_to_edit_this_administrator(Admin_User_Interface $admin_user): void
    {
        $this->client->build_update_request(Resources::ADMINISTRATORS, (string) $admin_user->get_id());
    }
    #[When('I browse administrators')]
    #[When('I want to browse administrators')]
    #[When('I try to browse administrators')]
    public function i_browse_administrators(): void
    {
        $this->client->index(Resources::ADMINISTRATORS);
        $this->shared_storage->set('last_response', $this->client->get_last_response());
    }
    #[When('I want to create a new administrator')]
    public function i_want_to_create_a_new_administrator(): void
    {
        $this->client->build_create_request(Resources::ADMINISTRATORS);
    }
    #[When('I specify its email as :email')]
    #[When('I do not specify its email')]
    #[When('I change its email to :email')]
    public function i_specify_its_email_as(?string $email = null): void
    {
        if ($email !== null) {
            $this->client->add_request_data('email', $email);
        }
    }
    #[When('I specify its name as :username')]
    #[When('I do not specify its name')]
    #[When('I change its name to :username')]
    public function i_specify_its_name_as(?string $username = null): void
    {
        if ($username !== null) {
            $this->client->add_request_data('username', $username);
        }
    }
    #[When('I specify its :field as too long string')]
    public function i_specify_its_field_as_too_long_string(string $field): void
    {
        $this->client->add_request_data(String_Inflector::name_to_camel_case(lcfirst(trim(ucwords($field)))), str_repeat('a', 256));
    }
    #[When('I specify its password as :password')]
    #[When('I do not specify its password')]
    #[When('I change its password to :password')]
    public function i_specify_its_password_as(?string $password = null): void
    {
        if ($password !== null) {
            $this->client->add_request_data('plainPassword', $this->replace_with_secure_password($password));
        }
    }
    #[When('I specify its locale as :localeCode')]
    public function i_specify_its_locale_as(string $locale_code): void
    {
        $this->client->add_request_data('localeCode', $locale_code);
    }
    #[When('I specify its locale as a wrong code')]
    public function i_specify_its_locale_as_wrong_code(): void
    {
        $this->client->add_request_data('localeCode', 'wr_ONG');
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->client->add_request_data('enabled', true);
    }
    #[When('I (try to) add it')]
    public function i_add_it(): void
    {
        $this->client->create();
    }
    #[When('I delete administrator with email :adminUser')]
    public function i_delete_administrator_with_email(Admin_User_Interface $admin_user): void
    {
        $this->client->delete(Resources::ADMINISTRATORS, (string) $admin_user->get_id());
    }
    #[When('/^I (?:upload|update) the "([^"]+)" image as (my) avatar$/')]
    public function i_upload_the_image_as_my_avatar(string $avatar, Admin_User_Interface $administrator): void
    {
        $builder = Request_Builder::create_post(sprintf('/api/v2/admin/%s/%s/%s', Resources::ADMINISTRATORS, $administrator->get_id(), Resources::AVATAR_IMAGE));
        $builder->with_header('CONTENT_TYPE', 'multipart/form-data');
        $builder->with_header('HTTP_ACCEPT', 'application/ld+json');
        $builder->with_header('HTTP_Authorization', 'Bearer ' . $this->shared_storage->get('token'));
        $builder->with_file('file', new Uploaded_File($this->mink_parameters['files_path'] . $avatar, basename($avatar)));
        $response = $this->client->request($builder->build());
        $this->shared_storage->set(String_Inflector::name_to_code($avatar), $this->response_checker->get_value($response, '@id'));
    }
    #[When('I remove the avatar')]
    public function i_remove_the_avatar_image(): void
    {
        /** @var AdminUserInterface $administrator */
        $administrator = $this->shared_storage->get('administrator');
        $avatar = $administrator->get_avatar();
        Assert::not_null($avatar);
        $this->client->custom_action(sprintf('/api/v2/admin/administrators/%s/%s', $administrator->get_id(), Resources::AVATAR_IMAGE), Request::METHOD_DELETE);
    }
    #[Then('I should see a single administrator in the list')]
    #[Then('there should be :count administrators in the list')]
    public function i_should_see_administrators_in_the_list(int $count = 1): void
    {
        Assert::same($this->response_checker->count_collection_items($this->client->get_last_response()), $count);
    }
    #[Then('the administrator :email should appear in the store')]
    #[Then('I should see the administrator :email in the list')]
    public function the_administrator_should_appear_in_the_store(string $email): void
    {
        Assert::true($this->response_checker->has_item_with_value($this->client->index(Resources::ADMINISTRATORS), 'email', $email), sprintf('Administrator with email %s does not exist', $email));
    }
    #[Then('there should not be :email administrator anymore')]
    public function there_should_not_be_administrator_anymore(string $email): void
    {
        Assert::false($this->response_checker->has_item_with_value($this->client->index(Resources::ADMINISTRATORS), 'email', $email), sprintf('Administrator with email %s exists, but it should not', $email));
    }
    #[Then('there should still be only one administrator with an email :email')]
    public function there_should_still_be_only_one_administrator_with_an_email(string $email): void
    {
        Assert::count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::ADMINISTRATORS), 'email', $email), 1, sprintf('There is more than one administrator with email %s', $email));
    }
    #[Then('there should still be only one administrator with name :username')]
    #[Then('this administrator with name :username should appear in the store')]
    public function this_administrator_with_name_should_appear_in_the_store(string $username): void
    {
        Assert::count($this->response_checker->get_collection_items_with_value($this->client->index(Resources::ADMINISTRATORS), 'username', $username), 1, sprintf('There is more than one administrator with username %s', $username));
    }
    #[Then('I should be notified that it has been successfully created')]
    public function i_should_be_notified_that_it_has_been_successfully_created(): void
    {
        Assert::true($this->response_checker->is_creation_successful($this->client->get_last_response()), 'Administrator could not be created');
    }
    #[Then('I should be notified that it has been successfully deleted')]
    public function i_should_be_notified_that_it_has_been_successfully_deleted(): void
    {
        Assert::true($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Administrator could not be deleted');
    }
    #[Then('I should be notified that email must be unique')]
    public function i_should_be_notified_that_email_must_be_unique(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'email: This email is already used.');
    }
    #[Then('I should be notified that name must be unique')]
    public function i_should_be_notified_that_name_must_be_unique(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'username: This username is already used.');
    }
    #[Then('I should be notified that the :elementName is required')]
    public function i_should_be_notified_that_first_name_is_required(string $element_name): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('Please enter your %s.', $element_name));
    }
    #[Then('I should be notified that this email is not valid')]
    public function i_should_be_notified_that_email_is_not_valid(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'email: This email is invalid.');
    }
    #[Then('I should be notified that this :field is too long')]
    public function i_should_be_notified_that_this_field_is_too_long(string $field): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), sprintf('%s must not be longer than 255 characters.', ucfirst($field)));
    }
    #[Then('I should be notified that this value is not valid locale')]
    public function i_should_be_notified_that_this_value_is_not_valid_locale(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'localeCode: This value is not a valid locale.');
    }
    #[Then('I should be notified that it cannot be deleted')]
    public function i_should_be_notified_that_it_cannot_be_deleted(): void
    {
        Assert::false($this->response_checker->is_deletion_successful($this->client->get_last_response()), 'Administrator could be deleted');
        Assert::same($this->response_checker->get_error($this->client->get_last_response()), 'Cannot remove currently logged in user.');
    }
    #[Then('/^I should see the "([^"]*)" image as (my) avatar$/')]
    public function i_should_see_the_image_as_my_avatar(string $avatar, Admin_User_Interface $administrator): void
    {
        Assert::true($this->response_checker->has_value($this->client->show(Resources::ADMINISTRATORS, (string) $administrator->get_id()), 'avatar', $this->shared_storage->get(String_Inflector::name_to_code($avatar))));
    }
    #[Then('I should not see the :avatar avatar image in the additional information section of my account')]
    public function i_should_not_see_the_avatar_image(string $avatar): void
    {
        /** @var AdminUserInterface $administrator */
        $administrator = $this->shared_storage->get('administrator');
        Assert::true($this->response_checker->has_value($this->client->show(Resources::ADMINISTRATORS, (string) $administrator->get_id()), 'avatar', null));
    }
    #[Then('I should be notified that this email is not valid in :locale locale')]
    public function i_should_be_notified_that_email_is_not_valid_in_locale(Locale_Interface $locale): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), $this->translator->trans('sylius.user.email.invalid', [], 'validators', $locale->get_code()), 'Email validation message is not displayed in the correct locale');
    }
    #[Then('I should see the :avatar avatar image in the top bar next to my name')]
    #[Then('I should not see the :avatar avatar image in the top bar next to my name')]
    public function i_should_see_the_avatar_image_in_the_top_bar_next_to_my_name(string $avatar): void
    {
        // intentionally left blank, as it is ui step
    }
}
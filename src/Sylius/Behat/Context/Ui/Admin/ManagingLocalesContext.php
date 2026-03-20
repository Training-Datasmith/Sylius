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
use Sylius\Behat\Element\Admin\Locale\Form_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Locale\Index_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Locales_Context implements Context
{
    public function __construct(private Create_Page_Interface $create_page, private Index_Page_Interface $index_page, private Form_Element_Interface $form_element, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[Given('I am browsing locales')]
    public function i_am_browsing_locales(): void
    {
        $this->index_page->open();
    }
    #[When('I want to create a new locale')]
    #[When('I want to add a new locale')]
    public function i_want_to_create_new_locale(): void
    {
        $this->create_page->open();
    }
    #[When('I choose :name')]
    public function i_choose(string $name): void
    {
        $this->form_element->choose_locale($name);
    }
    #[When('I add it')]
    public function i_add(): void
    {
        $this->create_page->create();
    }
    #[When('I remove :localeCode locale')]
    public function i_remove_locale(string $locale_code): void
    {
        $this->index_page->open();
        $this->index_page->delete_resource_on_page(['code' => $locale_code]);
    }
    #[When('I filter by code containing :phrase')]
    public function i_filter_by_code_containing(string $phrase): void
    {
        $this->index_page->filter_by_code($phrase);
        $this->index_page->filter();
    }
    #[Then('the store should be available in the :name language')]
    public function store_should_be_available_in_language(string $name): void
    {
        $does_locale_exist = $this->index_page->is_single_resource_on_page(['name' => $name]);
        Assert::true($does_locale_exist);
    }
    #[Then('I should not be able to choose :name')]
    public function i_should_not_be_able_to_choose(string $name): void
    {
        Assert::false($this->form_element->is_locale_available($name));
    }
    #[Then('I should be informed that locale :localeCode has been deleted')]
    public function i_should_be_informed_that_locale_has_been_deleted(string $locale_code): void
    {
        $this->notification_checker->check_notification('Locale has been successfully deleted.', Notification_Type::success());
    }
    #[Then('only the :localeCode locale should be present in the system')]
    public function only_the_locale_should_be_present_in_the_system(string $locale_code): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $locale_code]));
        Assert::true($this->index_page->count_items() === 1);
    }
    #[Then('I should be informed that locale :localeCode is in use and cannot be deleted')]
    public function i_should_be_informed_that_locale_is_in_use_and_cannot_be_deleted(string $locale_code): void
    {
        $this->notification_checker->check_notification('Cannot delete the locale, as it is used by at least one translation.', Notification_Type::failure());
    }
    #[Then('the :localeCode locale should be still present in the system')]
    public function the_locale_should_be_still_present_in_the_system(string $locale_code): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $locale_code]));
    }
    #[Then('I should see a single locale in the list')]
    public function i_should_see_locale_in_the_list(): void
    {
        Assert::same($this->index_page->count_items(), 1);
    }
    #[Then('I should see the locale :localeName')]
    public function i_should_see_the_locale(string $locale_name): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $locale_name]));
    }
}
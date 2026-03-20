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
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Country\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Country\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Country\Update_Page_Interface;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Resolver\Current_Page_Resolver_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Countries_Context implements Context
{
    private const MAX_PROVINCE_CODE_LENGTH = 255;
    public function __construct(private Index_Page_Interface $index_page, private Create_Page_Interface $create_page, private Update_Page_Interface $update_page, private Current_Page_Resolver_Interface $current_page_resolver, private Notification_Checker_Interface $notification_checker)
    {
    }
    #[When('I want to add a new country')]
    public function i_want_to_add_new_country(): void
    {
        $this->create_page->open();
    }
    #[When('/^I want to edit (this country)$/')]
    #[When('/^I am editing (this country)$/')]
    public function i_want_to_edit_this_country(Country_Interface $country): void
    {
        $this->update_page->open(['id' => $country->get_id()]);
    }
    #[When('I choose :countryName')]
    public function i_choose(string $country_name): void
    {
        $this->create_page->select_country($country_name);
    }
    #[When('I add the :provinceName province with :provinceCode code')]
    #[When('I add the :provinceName province with :provinceCode code and :provinceAbbreviation abbreviation')]
    public function i_add_province_with_code(string $province_name, string $province_code, ?string $province_abbreviation = null): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $current_page = $this->current_page_resolver->get_current_page_with_form([$this->create_page, $this->update_page]);
        $current_page->add_province();
        $current_page->specify_province_name($province_name);
        $current_page->specify_province_code($province_code);
        if (null !== $province_abbreviation) {
            $current_page->specify_province_abbreviation($province_abbreviation);
        }
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->update_page->enable();
    }
    #[When('I disable it')]
    public function i_disable_it(): void
    {
        $this->update_page->disable();
    }
    #[Then('/^the (country "([^"]+)") should appear in the store$/')]
    public function country_should_appear_in_the_store(Country_Interface $country): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $country->get_code()]));
    }
    #[Then('/^(this country) should be enabled$/')]
    public function this_country_should_be_enabled(Country_Interface $country): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_country_enabled($country));
    }
    #[Then('/^(this country) should be disabled$/')]
    public function this_country_should_be_disabled(Country_Interface $country): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_country_disabled($country));
    }
    #[Then('I should not be able to choose :name')]
    public function i_should_not_be_able_to_choose(string $name): void
    {
        try {
            $this->create_page->select_country($name);
        } catch (Element_Not_Found_Exception) {
            return;
        }
        throw new \DomainException('Choose name should throw an exception!');
    }
    #[Then('I should not be able to edit its code')]
    public function the_code_field_should_be_disabled(): void
    {
        Assert::true($this->update_page->is_code_field_disabled());
    }
    #[Then('/^(this country) should(?:| still) have the "([^"]*)" province$/')]
    #[Then('/^(this country) should(?:| still) have the "([^"]*)" and "([^"]*)" provinces$/')]
    #[Then('/^the (country "[^"]*") should(?:| still) have the "([^"]*)" province$/')]
    public function country_should_have_province(Country_Interface $country, string ...$province_names): void
    {
        $this->i_want_to_edit_this_country($country);
        foreach ($province_names as $province_name) {
            Assert::true($this->update_page->is_there_province($province_name));
        }
    }
    #[Then('/^(this country) should not have the "([^"]*)" province$/')]
    public function this_country_should_not_have_the_province(Country_Interface $country, string $province_name): void
    {
        $this->i_want_to_edit_this_country($country);
        Assert::false($this->update_page->is_there_province($province_name));
    }
    #[Then('/^the province should still be named "([^"]*)" in (this country)$/')]
    public function this_province_should_still_be_named(string $province_name, Country_Interface $country): void
    {
        $this->update_page->open(['id' => $country->get_id()]);
        Assert::true($this->update_page->is_there_province($province_name));
    }
    #[Then('/^province with name "([^"]*)" should not be added in (this country)$/')]
    public function province_with_name_should_not_be_added(string $province_name, Country_Interface $country): void
    {
        $this->update_page->open(['id' => $country->get_id()]);
        Assert::false($this->update_page->is_there_province($province_name));
    }
    #[Then('/^province with code "([^"]*)" should not be added in (this country)$/')]
    public function province_with_code_should_not_be_added(string $province_code, Country_Interface $country): void
    {
        $this->update_page->open(['id' => $country->get_id()]);
        Assert::false($this->update_page->is_there_province_with_code($province_code));
    }
    #[When('/^I(?:| also) delete the "([^"]*)" province of this country$/')]
    public function i_delete_the_province_of_country(string $province_name): void
    {
        $this->update_page->remove_province($province_name);
    }
    #[When('/^I want to create a new province in (country "([^"]*)")$/')]
    public function i_want_to_create_a_new_province_in_country(Country_Interface $country): void
    {
        $this->update_page->open(['id' => $country->get_id()]);
        $this->update_page->add_province();
    }
    #[When('I name the province :provinceName')]
    #[When('I do not name the province')]
    public function i_name_the_province($province_name = null): void
    {
        $this->update_page->specify_province_name($province_name ?? '');
    }
    #[When('I do not specify the province code')]
    #[When('I specify the province code as :provinceCode')]
    public function i_specify_the_province_code($province_code = null): void
    {
        $this->update_page->specify_province_code($province_code ?? '');
    }
    #[When('I provide a too long province code')]
    public function i_provide_too_long_province_code(): void
    {
        $this->i_specify_the_province_code(sprintf('US-%s', str_repeat('A', self::MAX_PROVINCE_CODE_LENGTH)));
    }
    #[Then('I should be notified that :element is required')]
    public function i_should_be_notified_that_element_is_required(string $element): void
    {
        Assert::same($this->update_page->get_validation_message($element), sprintf('Please enter province %s.', $element));
    }
    #[When('I remove :provinceName province name')]
    public function i_remove_province_name(string $province_name): void
    {
        $this->update_page->remove_province_name($province_name);
        $this->update_page->save_changes();
    }
    #[Then('/^I should be notified that province (code|name) must be unique$/')]
    public function i_should_be_notified_that_province_code_must_be_unique(string $field): void
    {
        Assert::same($this->update_page->get_validation_message($field), sprintf('Province %s must be unique.', $field));
    }
    #[Then('I should be notified that all province codes and names within this country need to be unique')]
    public function i_should_be_notified_that_all_province_codes_and_names_within_this_country_need_to_be_unique(): void
    {
        Assert::in_array('All provinces within this country need to have unique codes and names.', $this->update_page->get_form_validation_errors());
    }
    #[Then('I should be notified that name of the province is required')]
    public function i_should_be_notified_that_name_of_the_province_is_required(): void
    {
        Assert::same($this->update_page->get_validation_message('name'), 'Please enter province name.');
    }
    #[Then('I should be informed that the provided province code is too long')]
    public function i_should_be_informed_that_the_code_is_too_long(): void
    {
        Assert::contains($this->update_page->get_validation_message('code'), 'The code must not be longer than');
    }
    #[Then('I should be notified that provinces that are in use cannot be deleted')]
    public function i_should_be_notified_that_provinces_that_are_in_use_cannot_be_deleted(): void
    {
        $this->notification_checker->check_notification('Error Cannot delete, the Province is in use.', Notification_Type::failure());
    }
}
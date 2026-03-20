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
use Sylius\Behat\Element\Admin\Product\Taxonomy_Form_Element_Interface;
use Sylius\Behat\Element\Admin\Taxon\Form_Element_Interface;
use Sylius\Behat\Element\Admin\Taxon\Image_Form_Element_Interface;
use Sylius\Behat\Element\Admin\Taxon\Tree_Element_Interface;
use Sylius\Behat\Notification_Type;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface as BaseCreatePageInterface;
use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface;
use Sylius\Behat\Page\Admin\Product\Update_Simple_Product_Page_Interface;
use Sylius\Behat\Service\Helper\Java_Script_Test_Helper;
use Sylius\Behat\Service\Notification_Checker_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Taxons_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Base_Create_Page_Interface $create_page, private Base_Create_Page_Interface $create_for_parent_page, private Update_Page_Interface $update_page, private Form_Element_Interface $form_element, private Image_Form_Element_Interface $image_form_element, private Tree_Element_Interface $tree_element, private Notification_Checker_Interface $notification_checker, private Java_Script_Test_Helper $test_helper, private Update_Simple_Product_Page_Interface $update_simple_product_page, private Taxonomy_Form_Element_Interface $product_taxonomy_form_element)
    {
    }
    #[When('I want to create a new taxon')]
    #[When('I want to see all taxons in store')]
    public function i_want_to_create_a_new_taxon(): void
    {
        $this->create_page->open();
    }
    #[When('I want to create a new taxon for :taxon')]
    public function i_want_to_create_a_new_taxon_for_parent(Taxon_Interface $taxon): void
    {
        $this->test_helper->wait_until_page_opens($this->create_for_parent_page, ['id' => $taxon->get_id()]);
    }
    #[When('/^I want to modify the ("[^"]+" taxon)$/')]
    public function i_want_to_modify_a_taxon(Taxon_Interface $taxon): void
    {
        $this->shared_storage->set('taxon', $taxon);
        $this->test_helper->wait_until_page_opens($this->update_page, ['id' => $taxon->get_id()]);
    }
    #[When('I specify its code as :code')]
    #[When('I do not specify its code')]
    public function i_specify_its_code_as(?string $code = null): void
    {
        $this->form_element->specify_code($code ?? '');
    }
    #[When('I specify a too long code')]
    public function i_specify_a_too_long(): void
    {
        $this->form_element->specify_code(str_repeat('a', 256));
    }
    #[When('I name it :name in :localeCode')]
    #[When('I rename it to :name in :localeCode')]
    #[When('I do not specify its name')]
    public function i_name_it_in(?string $name = null, ?string $locale_code = 'en_US'): void
    {
        $this->form_element->name_it($name ?? '', $locale_code);
    }
    #[When('I set its slug to :slug')]
    #[When('I do not specify its slug')]
    #[When('I set its slug to :slug in :localeCode')]
    public function i_set_its_slug_to_in(?string $slug = null, ?string $locale_code = 'en_US'): void
    {
        $this->form_element->slug_it($slug ?? '', $locale_code);
    }
    #[When('I generate its slug in :localeCode')]
    public function i_generate_its_slug_in(string $locale_code): void
    {
        $this->form_element->generate_slug($locale_code);
    }
    #[When('I change its description to :description in :localeCode')]
    public function i_change_its_description_to_in(string $description, string $locale_code): void
    {
        $this->form_element->describe_it_as($description, $locale_code);
    }
    #[When('I describe it as :description in :localeCode')]
    public function i_describe_it_as(string $description, string $locale_code): void
    {
        $this->form_element->describe_it_as($description, $locale_code);
    }
    #[When('/^I set its (parent taxon to "[^"]+")$/')]
    public function i_set_its_parent_taxon_to(Taxon_Interface $taxon): void
    {
        $this->form_element->choose_parent($taxon);
    }
    #[When('/^I change its (parent taxon to "[^"]+")$/')]
    #[Then('/^I should be able to change its (parent taxon to "[^"]+")$/')]
    public function i_change_its_parent_taxon_to(Taxon_Interface $taxon): void
    {
        $this->form_element->remove_current_parent();
        $this->form_element->choose_parent($taxon);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[When('I attach the :path image with :type type')]
    #[When('I attach the :path image with :type type to this taxon')]
    #[When('I attach the :path image')]
    #[When('I attach the :path image to this taxon')]
    public function i_attach_image_with_type(string $path, ?string $type = null): void
    {
        $this->image_form_element->attach_image($path, $type);
    }
    #[When('/^I(?:| also) remove an image with "([^"]*)" type$/')]
    public function i_remove_an_image_with_type(string $type): void
    {
        $this->image_form_element->remove_image_with_type($type);
    }
    #[When('I remove the first image')]
    public function i_remove_the_first_image(): void
    {
        $this->image_form_element->remove_first_image();
    }
    #[When('I move up :taxonName taxon')]
    public function i_move_up_taxon(string $taxon_name): void
    {
        $this->tree_element->move_up_taxon($taxon_name);
    }
    #[When('I move down :taxonName taxon')]
    public function i_move_down_taxon(string $taxon_name): void
    {
        $this->tree_element->move_down_taxon($taxon_name);
    }
    #[When('I enable it')]
    public function i_enable_it(): void
    {
        $this->form_element->enable();
    }
    #[When('I disable it')]
    public function i_disable_it(): void
    {
        $this->form_element->disable();
    }
    #[Then('/^the ("[^"]+" taxon) should appear in the registry$/')]
    public function the_taxon_should_appear_in_the_registry(Taxon_Interface $taxon): void
    {
        $this->update_page->open(['id' => $taxon->get_id()]);
        Assert::same($this->form_element->get_code(), $taxon->get_code());
    }
    #[When('/^I search for parent taxon "([^"]*)"$/')]
    public function i_search_for_parent_taxon(string $search_term): void
    {
        $this->shared_storage->set('autocompleteSearchResults', $this->form_element->search_parent_taxon($search_term));
    }
    #[Then('this taxon :element should be :value')]
    #[Then('this taxon :element should be :value in :localeCode')]
    #[Then('this taxon should have :element :value in :localeCode')]
    public function this_taxon_element_should_be(string $element, string $value, ?string $locale_code = 'en_US'): void
    {
        Assert::same($this->form_element->get_translation_field_value($element, $locale_code), $value);
    }
    #[Then('the slug of the :taxonName taxon should( still) be :slug')]
    public function the_slug_of_the_taxon_should_be(string $taxon_name, string $slug): void
    {
        $this->this_taxon_element_should_be('slug', $slug);
    }
    #[Then('I should not be able to edit its code')]
    public function i_should_not_be_able_to_edit_its_code(): void
    {
        Assert::true($this->form_element->is_code_disabled());
    }
    #[Then('the product :product should no longer have a main taxon')]
    public function the_product_should_no_longer_have_a_main_taxon(Product_Interface $product): void
    {
        $this->update_simple_product_page->open(['id' => $product->get_id()]);
        Assert::null($this->product_taxonomy_form_element->get_main_taxon());
    }
    #[Then('/^this taxon should (belongs to "[^"]+")$/')]
    public function this_taxon_should_belongs_to(Taxon_Interface $taxon): void
    {
        Assert::same($this->form_element->get_parent(), $taxon->get_name());
    }
    #[Then('it should not belong to any other taxon')]
    public function it_should_not_belong_to_any_other_taxon(): void
    {
        Assert::is_empty($this->form_element->get_parent());
    }
    #[Then('I should be notified that taxon with this code already exists')]
    public function i_should_be_notified_that_taxon_with_this_code_already_exists(): void
    {
        Assert::same($this->form_element->get_validation_message('code'), 'Taxon with given code already exists.');
    }
    #[Then('I should be notified that taxon slug must be unique')]
    public function i_should_be_notified_that_taxon_slug_must_be_unique(): void
    {
        Assert::same($this->form_element->get_validation_message('slug', ['%locale_code%' => 'en_US']), 'Taxon slug must be unique.');
    }
    #[Then('/^I should be notified that (name|slug) is required$/')]
    public function i_should_be_notified_that_translation_field_is_required(string $element): void
    {
        Assert::same($this->form_element->get_validation_message($element, ['%locale_code%' => 'en_US']), sprintf('Please enter taxon %s.', $element));
    }
    #[Then('I should be notified that code is required')]
    public function i_should_be_notified_that_code_is_required(): void
    {
        Assert::same($this->form_element->get_validation_message('code', ['%locale_code%' => 'en_US']), 'Please enter taxon code.');
    }
    #[Then('I should be notified that code is too long')]
    public function i_should_be_notified_that_code_is_too_long(): void
    {
        Assert::contains($this->form_element->get_validation_message('code'), 'must not be longer than 255 characters.');
    }
    #[Then('/^there should(?:| still) be only one taxon with code "([^"]+)"$/')]
    public function there_should_still_be_only_one_taxon_with_code(string $code): void
    {
        Assert::same($this->form_element->get_code(), $code);
    }
    #[Then('/^taxon named "([^"]+)" should not be added$/')]
    #[Then('the taxon named :name should no longer exist in the registry')]
    public function taxon_named_should_not_be_added(string $name): void
    {
        if (!$this->create_page->is_open()) {
            $this->create_page->open();
        }
        Assert::false($this->tree_element->is_taxon_on_the_list($name));
    }
    #[Then('/^I should see (\d+) taxons on the list$/')]
    public function i_should_see_taxons_in_the_list(int $number): void
    {
        Assert::same($this->tree_element->count_taxons(), $number);
    }
    #[Then('I should see the taxon named :name in the list')]
    public function i_should_see_the_taxon_named_in_the_list(string $name): void
    {
        Assert::true($this->tree_element->is_taxon_on_the_list($name));
    }
    #[Then('the order of taxons should be :firstTaxon, :secondTaxon, :thirdTaxon and :fourthTaxon')]
    public function the_order_of_taxons_should_be(string ...$taxons_names): void
    {
        $taxons = $this->tree_element->get_taxons_names();
        Assert::same($taxons, $taxons_names);
    }
    #[Then('/^(?:it|this taxon) should(?:| also) have an image with "([^"]*)" type$/')]
    public function this_taxon_should_have_an_image_with_type(string $type): void
    {
        Assert::true($this->image_form_element->is_image_with_type_displayed($type));
    }
    #[Then('/^(?:this taxon|it) should not have(?:| also) any images with "([^"]*)" type$/')]
    public function this_taxon_should_not_have_an_image_with_type(string $code): void
    {
        Assert::false($this->image_form_element->is_image_with_type_displayed($code));
    }
    #[Then('/^(this taxon) should not have any images$/')]
    public function this_taxon_should_not_have_any_images(Taxon_Interface $taxon): void
    {
        $this->i_want_to_modify_a_taxon($taxon);
        Assert::same($this->image_form_element->count_images(), 0);
    }
    #[When('I change the image with the :type type to :path')]
    public function i_change_its_image_to_path_for_the_type(string $path, string $type): void
    {
        $this->image_form_element->change_image_with_type($type, $path);
    }
    #[When('I change the first image type to :type')]
    public function i_change_the_first_image_type_to(string $type): void
    {
        $this->image_form_element->modify_first_image_type($type);
    }
    #[Then('/^(this taxon) should have only one image$/')]
    #[Then('/^(this taxon) should(?:| still) have (\d+) images?$/')]
    public function there_should_still_be_only_one_image_in_this_taxon(Taxon_Interface $taxon, int $count = 1): void
    {
        $this->i_want_to_modify_a_taxon($taxon);
        Assert::same($this->image_form_element->count_images(), $count);
    }
    #[Then('I should be notified that I cannot delete a menu taxon of any channel')]
    public function i_should_be_notified_that_i_cannot_delete_a_menu_taxon_of_any_channel(): void
    {
        $this->notification_checker->check_notification('You cannot delete a menu taxon of any channel.', Notification_Type::failure());
    }
    #[Then('I should be notified that I cannot delete a taxon in use')]
    public function i_should_be_notified_that_i_cannot_delete_a_taxon_in_use(): void
    {
        $this->notification_checker->check_notification('Cannot delete, the Taxon is in use.', Notification_Type::failure());
    }
    #[Then('the first taxon on the list should be :taxonName')]
    public function the_first_taxon_on_the_list_should_be(string $taxon_name): void
    {
        Assert::same($this->tree_element->get_first_taxon_on_the_list(), $taxon_name);
    }
    #[Then('the last taxon on the list should be :taxonName')]
    public function the_last_taxon_on_the_list_should_be(string $taxon_name): void
    {
        Assert::same($this->tree_element->get_last_taxon_on_the_list(), $taxon_name);
    }
    #[Then('/^(?:this taxon|it) should be enabled$/')]
    public function it_should_be_enabled(): void
    {
        Assert::true($this->form_element->is_enabled());
    }
    #[Then('/^(?:this taxon|it) should be disabled$/')]
    public function it_should_be_disabled(): void
    {
        Assert::false($this->form_element->is_enabled());
    }
    #[Then('/^I should see "([^"]*)" in the found results$/')]
    public function i_should_see_in_the_found_results(string $taxon_name): void
    {
        $autocomplete_search_results = $this->shared_storage->get('autocompleteSearchResults');
        $found = false;
        foreach ($autocomplete_search_results as $result) {
            if (str_contains((string) $result, $taxon_name)) {
                $found = true;
                break;
            }
        }
        Assert::true($found, sprintf('Expected to see "%s" in autocomplete results, but found: %s', $taxon_name, implode(', ', $autocomplete_search_results)));
    }
}
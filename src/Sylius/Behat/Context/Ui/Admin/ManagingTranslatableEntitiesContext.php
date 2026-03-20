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
use Sylius\Behat\Element\Admin\Taxon\Form_Element_Interface;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Translatable_Entities_Context implements Context
{
    public function __construct(private Create_Page_Interface $taxon_create_page, private Form_Element_Interface $taxon_form_element)
    {
    }
    #[When('I want to create a new translatable entity')]
    public function i_want_to_create_a_new_translatable_entity(): void
    {
        $this->taxon_create_page->open();
    }
    #[Then('I should be able to translate it in :localeCode')]
    public function i_should_be_able_to_translate_it_in(string $locale_code): void
    {
        $this->taxon_form_element->describe_it_as('Description', $locale_code);
    }
    #[Then('I should not be able to translate it in :localeCode')]
    public function i_should_not_be_able_to_translate_it_in(string $locale_code): void
    {
        Assert::throws(fn() => $this->taxon_form_element->describe_it_as('Description', $locale_code), Element_Not_Found_Exception::class);
    }
}
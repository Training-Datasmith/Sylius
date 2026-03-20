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
use Sylius\Behat\Element\Admin\Currency\Form_Element_Interface;
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Currency\Index_Page_Interface;
use Sylius\Component\Currency\Model\Currency_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Currencies_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page, private Create_Page_Interface $create_page, private Form_Element_Interface $form_element)
    {
    }
    #[When('I want to add a new currency')]
    public function i_want_to_add_new_currency(): void
    {
        $this->create_page->open();
    }
    #[When('I choose :currencyName')]
    public function i_choose(string $currency_name): void
    {
        $this->form_element->choose_currency($currency_name);
    }
    #[When('I add it')]
    #[When('I try to add it')]
    public function i_add_it(): void
    {
        $this->create_page->create();
    }
    #[Then('the currency :currency should appear in the store')]
    #[Then('I should see the currency :currency on the list')]
    public function currency_should_appear_in_the_store(Currency_Interface $currency): void
    {
        $this->index_page->open();
        Assert::true($this->index_page->is_single_resource_on_page(['code' => $currency->get_code()]));
    }
    #[When('I want to browse currencies of the store')]
    public function i_want_to_see_all_currencies_in_store(): void
    {
        $this->index_page->open();
    }
    #[Then('/^I should see (\d+) currencies on the list$/')]
    public function i_should_see_currencies_in_the_list(int $amount_of_currencies): void
    {
        Assert::same($this->index_page->count_items(), $amount_of_currencies);
    }
    #[Then('I should not be able to choose :name')]
    public function i_should_not_be_able_to_choose(string $name): void
    {
        Assert::false($this->form_element->is_currency_available($name));
    }
}
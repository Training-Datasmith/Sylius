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
namespace Sylius\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Shop\Menu_Element_Interface;
use Sylius\Behat\Page\Shop\Home_Page_Interface;
use Webmozart\Assert\Assert;
final readonly class Homepage_Context implements Context
{
    public function __construct(private Home_Page_Interface $home_page, private Menu_Element_Interface $menu_element)
    {
    }
    #[When('I check latest products')]
    #[When('I check available taxons')]
    #[When('I check latest deals')]
    public function i_check_latest_products(): void
    {
        $this->home_page->open();
    }
    #[Then('I should be redirected to the homepage')]
    public function i_should_be_redirected_to_the_homepage(): void
    {
        $this->home_page->verify();
    }
    #[Then('I should see :numberOfProducts products in the list')]
    public function i_should_see_products_in_the_list(int $number_of_products): void
    {
        Assert::same(count($this->home_page->get_latest_products_names()), $number_of_products);
    }
    #[Then('I should see :productName product')]
    public function i_should_see_product(string $product_name): void
    {
        Assert::in_array($product_name, $this->home_page->get_latest_products_names());
    }
    #[Then('I should not see :productName product')]
    public function i_should_not_see_product(string $product_name): void
    {
        Assert::true(!in_array($product_name, $this->home_page->get_latest_products_names()));
    }
    #[Then('I should see :firstMenuItem in the menu')]
    #[Then('I should see :firstMenuItem and :secondMenuItem in the menu')]
    public function i_should_see_and_in_the_menu(string ...$menu_items): void
    {
        Assert::all_one_of($menu_items, $this->menu_element->get_menu_items());
    }
    #[Then('I should not see :firstMenuItem and :secondMenuItem in the menu')]
    #[Then('I should not see :firstMenuItem, :secondMenuItem and :thirdMenuItem in the menu')]
    #[Then('I should not see :firstMenuItem, :secondMenuItem, :thirdMenuItem and :fourthMenuItem in the menu')]
    public function i_should_not_see_and_in_the_menu(string ...$menu_items): void
    {
        $actual_menu_items = $this->menu_element->get_menu_items();
        foreach ($menu_items as $menu_item) {
            if (in_array($menu_item, $actual_menu_items)) {
                throw new \InvalidArgumentException(sprintf('Menu should not contain %s element', $menu_item));
            }
        }
    }
    #[Then('I should be logged in')]
    public function i_should_be_logged_in(): void
    {
        $this->home_page->verify();
        Assert::true($this->home_page->has_logout_button());
    }
    #[Then('I should see :numberOfProducts products in the latest deals list')]
    public function i_should_see_products_in_the_deals_list(int $number_of_products): void
    {
        Assert::same(count($this->home_page->get_latest_deals_names()), $number_of_products);
    }
}
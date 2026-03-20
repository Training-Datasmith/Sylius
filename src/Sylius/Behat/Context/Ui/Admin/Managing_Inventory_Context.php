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
use Sylius\Behat\Page\Admin\Inventory\Index_Page_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Inventory_Context implements Context
{
    public function __construct(private Index_Page_Interface $index_page)
    {
    }
    #[Given('I am browsing inventory')]
    #[When('I want to browse inventory')]
    public function i_want_to_browse_inventory(): void
    {
        $this->index_page->open();
    }
    #[When('/^I filter tracked variants with (code|name) containing "([^"]+)"/')]
    public function i_filter_tracked_variants_with_code_containing(string $field, string $value): void
    {
        $this->index_page->specify_filter_type($field, 'Contains');
        $this->index_page->specify_filter_value($field, $value);
        $this->index_page->filter();
    }
    #[When('I filter tracked variants by :productName product')]
    public function i_filter_tracked_variants_by_product(string $product_name): void
    {
        $this->index_page->filter_by_product($product_name);
        $this->index_page->filter();
    }
    #[When('I sort the tracked variants :sortingOrder by :field')]
    public function i_sort_tracked_variants_by(string $sorting_order, string $field): void
    {
        $this->index_page->sort_by($field, $sorting_order === 'descending' ? 'desc' : 'asc');
    }
    #[Then('I should see only one tracked variant in the list')]
    #[Then('I should see :count tracked variants in the list')]
    public function i_should_see_tracked_variants_in_the_list(int $count = 1): void
    {
        Assert::same($this->index_page->count_items(), $count);
    }
    #[Then('I should see that the :productVariantName variant has :quantity quantity on hand')]
    public function i_should_see_that_the_product_variant_has_quantity_on_hand($product_variant_name, string $quantity): void
    {
        Assert::true($this->index_page->is_single_resource_on_page(['name' => $product_variant_name, 'inventory' => sprintf('%s Available on hand', $quantity)]));
    }
    #[Then('the first variant on the list should have :field :name')]
    public function the_first_variant_on_the_list_should_have(string $field, string $variant_name): void
    {
        $names = $this->index_page->get_column_fields($field);
        Assert::contains(reset($names), $variant_name);
    }
    #[Then('the last variant on the list should have :field :name')]
    public function the_last_variant_on_the_list_should_have(string $field, string $variant_name): void
    {
        $names = $this->index_page->get_column_fields($field);
        Assert::contains(end($names), $variant_name);
    }
}
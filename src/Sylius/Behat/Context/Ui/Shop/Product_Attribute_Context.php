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
use Behat\Mink\Element\Node_Element;
use Behat\Step\Then;
use Sylius\Behat\Page\Shop\Product\Show_Page_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Attribute_Context implements Context
{
    public function __construct(private Show_Page_Interface $show_page)
    {
    }
    #[Then('I should (also) see the product attribute :attributeName with value :expectedAttribute')]
    public function i_should_see_the_product_attribute_with_value(string $attribute_name, string $expected_attribute): void
    {
        Assert::same($this->show_page->get_attribute_by_name($attribute_name), $expected_attribute);
    }
    #[Then('/^I should(?:| also) see the product attribute "([^"]+)" with (positive|negative) value$/')]
    public function i_should_see_the_product_attribute_with_boolean(string $attribute_name, string $expected_attribute): void
    {
        Assert::same($this->show_page->get_attribute_by_name($attribute_name), 'positive' === $expected_attribute ? 'Yes' : 'No');
    }
    #[Then('I should (also) see the product attribute :attributeName with value :expectedAttribute on the list')]
    public function i_should_see_the_product_attribute_with_value_on_the_list(string $attribute_name, string $expected_attribute): void
    {
        Assert::in_array($expected_attribute, $this->show_page->get_attribute_list_by_name($attribute_name));
    }
    #[Then('I should not see the product attribute :attributeName')]
    public function i_should_not_see_the_product_attribute(string $attribute_name): void
    {
        $this->show_page->get_attribute_by_name($attribute_name);
    }
    #[Then('I should (also) see the product attribute :attributeName with date :expectedAttribute')]
    public function i_should_see_the_product_attribute_with_date(string $attribute_name, string $expected_attribute): void
    {
        Assert::eq(new \DateTime($this->show_page->get_attribute_by_name($attribute_name)), new \DateTime($expected_attribute));
    }
    #[Then('/^I should(?:| also) see the product attribute "([^"]+)" with value ([^"]+)%$/')]
    public function i_should_see_the_product_attribute_with_percentage(string $attribute_name, int $expected_attribute): void
    {
        Assert::eq($this->show_page->get_attribute_by_name($attribute_name), sprintf('%d %%', $expected_attribute));
    }
    #[Then('I should see :count attributes')]
    public function i_should_see_attributes(int $count): void
    {
        Assert::count($this->get_product_attributes(), $count);
    }
    #[Then('the first attribute should be :name')]
    public function the_first_attribute_should_be(string $name): void
    {
        $attributes = $this->get_product_attributes();
        Assert::same(reset($attributes)->get_text(), $name);
    }
    #[Then('the last attribute should be :name')]
    public function the_last_attribute_should_be(string $name): void
    {
        $attributes = $this->get_product_attributes();
        Assert::same(end($attributes)->get_text(), $name);
    }
    /**
     * @return NodeElement[]
     *
     * @throws \InvalidArgumentException
     */
    private function get_product_attributes(): array
    {
        $attributes = $this->show_page->get_attributes();
        Assert::not_null($attributes, 'The product has no attributes.');
        return $attributes;
    }
}
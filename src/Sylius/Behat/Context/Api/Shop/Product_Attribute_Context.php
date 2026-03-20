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
namespace Sylius\Behat\Context\Api\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Product\Model\Product_Interface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;
final readonly class Product_Attribute_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Then('I should (also) see the product attribute :attributeName with value :expectedAttribute')]
    public function i_should_see_the_product_attribute_with_value(string $attribute_name, string $expected_attribute): void
    {
        $attribute = $this->get_attribute_by_name($attribute_name);
        $attribute_value = $attribute['value'];
        if (is_array($attribute_value)) {
            Assert::in_array($expected_attribute, $attribute_value);
            return;
        }
        Assert::same($attribute_value, $expected_attribute);
    }
    #[Then('/^I should(?:| also) see the product attribute "([^"]+)" with (positive|negative) value$/')]
    public function i_should_see_the_product_attribute_with_boolean(string $attribute_name, string $expected_attribute): void
    {
        $attribute = $this->get_attribute_by_name($attribute_name);
        Assert::same($attribute['value'], 'positive' === $expected_attribute);
    }
    #[Then('/^I should(?:| also) see the product attribute "([^"]+)" with value ([^"]+)%$/')]
    public function i_should_see_the_product_attribute_with_percentage(string $attribute_name, int $expected_attribute): void
    {
        $attribute = $this->get_attribute_by_name($attribute_name);
        Assert::same($attribute['value'], $expected_attribute / 100);
    }
    #[Then('I should (also) see the product attribute :attributeName with value :expectedAttribute on the list')]
    public function i_should_see_the_product_attribute_with_value_on_the_list(string $attribute_name, string $expected_attribute): void
    {
        $attribute = $this->get_attribute_by_name($attribute_name);
        Assert::in_array($expected_attribute, $attribute['value']);
    }
    #[Then('I should not see the product attribute :attributeName')]
    public function i_should_not_see_the_product_attribute(string $attribute_name): void
    {
        Assert::false($this->has_attribute_by_name($attribute_name));
    }
    #[Then('I should (also) see the product attribute :attributeName with date :expectedAttribute')]
    public function i_should_see_the_product_attribute_with_date(string $attribute_name, string $expected_attribute): void
    {
        $attribute = $this->get_attribute_by_name($attribute_name);
        Assert::true(new \DateTime($attribute['value']) == new \DateTime($expected_attribute));
    }
    #[Then('I should see :count attributes')]
    public function i_should_see_attributes(int $count): void
    {
        Assert::count($this->get_attributes(), $count);
    }
    #[Then('the first attribute should be :name')]
    public function the_first_attribute_should_be(string $name): void
    {
        $attributes = $this->get_attributes();
        $attribute = reset($attributes);
        Assert::is_array($attribute);
        Assert::same($attribute['name'], $name);
    }
    #[Then('the last attribute should be :name')]
    public function the_last_attribute_should_be(string $name): void
    {
        $attributes = $this->get_attributes();
        $attribute = end($attributes);
        Assert::is_array($attribute);
        Assert::same($attribute['name'], $name);
    }
    private function has_attribute_by_name(string $name): bool
    {
        foreach ($this->get_attributes() as $attribute) {
            if ($attribute['name'] === $name) {
                return true;
            }
        }
        return false;
    }
    private function get_attribute_by_name(string $name): array
    {
        foreach ($this->get_attributes() as $attribute) {
            if ($attribute['name'] === $name) {
                return $attribute;
            }
        }
        throw new InvalidArgumentException('Expected a value other than null.');
    }
    private function get_attributes(): array
    {
        /** @var ProductInterface $product */
        $product = $this->shared_storage->get('product');
        try {
            $attributes = $this->shared_storage->get('product_attributes');
        } catch (\InvalidArgumentException) {
            $product_attributes_response = $this->client->sub_resource_index(Resources::PRODUCTS, 'attributes', $product->get_code());
            $attributes = $this->response_checker->get_collection($product_attributes_response);
            $this->shared_storage->set('product_attributes', $attributes);
        }
        return $attributes;
    }
}
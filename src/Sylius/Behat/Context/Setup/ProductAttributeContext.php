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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\When;
use Doctrine\Persistence\Object_Manager;
use Faker\Factory;
use Faker\Generator;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Attribute\Attribute_Type\Date_Attribute_Type;
use Sylius\Component\Attribute\Attribute_Type\Datetime_Attribute_Type;
use Sylius\Component\Attribute\Attribute_Type\Select_Attribute_Type;
use Sylius\Component\Attribute\Factory\Attribute_Factory_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Product\Model\Product_Attribute_Interface;
use Sylius\Component\Product\Model\Product_Attribute_Value_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Product_Attribute_Context implements Context
{
    private Generator $faker;
    public function __construct(private Shared_Storage_Interface $shared_storage, private Repository_Interface $product_attribute_repository, private Attribute_Factory_Interface $product_attribute_factory, private Factory_Interface $product_attribute_value_factory, private Object_Manager $object_manager)
    {
        $this->faker = Factory::create();
    }
    #[Given('the store has a :type product attribute :name with code :code')]
    public function the_store_has_a_product_attribute_with_code(string $type, string $name, ?string $code): void
    {
        $product_attribute = $this->create_product_attribute($type, $name, $code);
        $this->save_product_attribute($product_attribute);
    }
    #[Given('the store has( also) a :type product attribute :name at position :position')]
    public function the_store_has_a_product_attribute_with_position(string $type, string $name, $position): void
    {
        $product_attribute = $this->create_product_attribute($type, $name);
        $product_attribute->set_position((int) $position);
        $this->save_product_attribute($product_attribute);
    }
    #[Given('/^the store has(?:| also)(?:| a| an) (text|textarea|integer|percent|float) product attribute "([^"]+)"$/')]
    public function the_store_has_a_product_attribute(string $type, string $name): void
    {
        $product_attribute = $this->create_product_attribute($type, $name);
        $this->save_product_attribute($product_attribute);
    }
    #[Given('/^the store has(?:| also)(?:| a| an) non-translatable (text|textarea|integer|percent|float) product attribute "([^"]+)"$/')]
    public function the_store_has_a_non_translatable_product_attribute(string $type, string $name): void
    {
        $product_attribute = $this->create_product_attribute($type, $name, null, false);
        $this->save_product_attribute($product_attribute);
    }
    #[Given('/^(this product attribute) is not translatable$/')]
    public function this_product_attribute_is_not_translatable(Product_Attribute_Interface $product_attribute): void
    {
        $product_attribute->set_translatable(false);
        $this->object_manager->flush();
    }
    #[Given('/^(this product attribute) has(?:| also) a value "([^"]+)" in ("[^"]+" locale)$/')]
    public function this_product_attribute_has_a_value_in_locale(Product_Attribute_Interface $product_attribute, string $value, string $locale_code): void
    {
        $choices = [$this->faker->uuid => [$locale_code => $value]];
        $configuration = $product_attribute->get_configuration();
        $configuration['choices'] = array_merge($configuration['choices'], $choices);
        $product_attribute->set_configuration($configuration);
        $this->save_product_attribute($product_attribute);
    }
    #[Given('/^(this product attribute) has(?:| also) a value "([^"]+)" in ("[^"]+" locale) and "([^"]+)" in ("[^"]+" locale)$/')]
    public function this_product_attribute_has_a_value_in_locale_and_in_locale(Product_Attribute_Interface $product_attribute, string $first_value, string $first_locale_code, string $second_value, string $second_locale_code): void
    {
        $choices = [$this->faker->uuid => [$first_locale_code => $first_value, $second_locale_code => $second_value]];
        $configuration = $product_attribute->get_configuration();
        $configuration['choices'] = array_merge($configuration['choices'], $choices);
        $product_attribute->set_configuration($configuration);
        $this->save_product_attribute($product_attribute);
    }
    #[Given('the store has a select product attribute :name')]
    public function the_store_has_a_select_product_attribute(string $name): void
    {
        $this->the_store_has_a_select_product_attribute_with_value($name);
    }
    #[Given('the store has a select product attribute :name with value :value')]
    #[Given('the store has a select product attribute :name with values :firstValue and :secondValue')]
    public function the_store_has_a_select_product_attribute_with_value(string $name, string ...$values): void
    {
        $choices = [];
        foreach ($values as $value) {
            $choices[$this->faker->uuid] = ['en_US' => $value];
        }
        $product_attribute = $this->create_product_attribute(Select_Attribute_Type::TYPE, $name);
        $product_attribute->set_configuration(['multiple' => true, 'choices' => $choices, 'min' => null, 'max' => null]);
        $this->save_product_attribute($product_attribute);
    }
    #[Given('the store has a non-translatable select product attribute :name with value :value')]
    public function the_store_has_a_non_translatable_select_product_attribute_with_value(string $name, string $value): void
    {
        $choices[$this->faker->uuid] = ['en_US' => $value];
        $product_attribute = $this->create_product_attribute(Select_Attribute_Type::TYPE, $name);
        $product_attribute->set_configuration(['multiple' => true, 'choices' => $choices, 'min' => null, 'max' => null]);
        $product_attribute->set_translatable(false);
        $this->save_product_attribute($product_attribute);
    }
    #[Given('the store has a non-translatable date product attribute :name with format :format')]
    public function the_store_has_a_non_translatable_date_product_attribute_with_format(string $name, string $format): void
    {
        $product_attribute = $this->create_product_attribute(Date_Attribute_Type::TYPE, $name);
        $product_attribute->set_configuration(['format' => $format]);
        $product_attribute->set_translatable(false);
        $this->save_product_attribute($product_attribute);
    }
    #[Given('the store has a non-translatable datetime product attribute :name with format :format')]
    public function the_store_has_a_non_translatable_datetime_product_attribute_with_format(string $name, string $format): void
    {
        $product_attribute = $this->create_product_attribute(Datetime_Attribute_Type::TYPE, $name);
        $product_attribute->set_configuration(['format' => $format]);
        $product_attribute->set_translatable(false);
        $this->save_product_attribute($product_attribute);
    }
    #[Given('/^(this product attribute)\'s "([^"]+)" value is labeled "([^"]+)" in the ("[^"]+" locale)$/')]
    public function this_product_attribute_value_is_labeled_in_the_locale(Product_Attribute_Interface $attribute, string $value, string $label, string $locale_code): void
    {
        $uuid = $this->get_select_attribute_value_uuid_by_choice_value($attribute, $value);
        $configuration = $attribute->get_configuration();
        $choices[$uuid] = $configuration['choices'][$uuid] + [$locale_code => $label];
        $configuration['choices'] = $choices;
        $attribute->set_configuration($configuration);
    }
    private function get_select_attribute_value_uuid_by_choice_value(Product_Attribute_Interface $attribute, string $value): string
    {
        $choices = $attribute->get_configuration()['choices'] ?? [];
        foreach ($choices as $uuid => $choice) {
            foreach ($choice as $choice_value) {
                if ($value === $choice_value) {
                    return $uuid;
                }
            }
        }
        throw new \InvalidArgumentException(sprintf('Value "%s" not found in attribute %s', $value, $attribute->get_name()));
    }
    #[Given('/^(this product attribute) has set min value as (\d+) and max value as (\d+)$/')]
    public function this_attribute_has_set_min_value_as_and_max_value_as(Product_Attribute_Interface $attribute, $min, $max): void
    {
        $attribute->set_configuration(['min' => $min, 'max' => $max]);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has(?:| also)(?:| a) select attribute "([^"]+)" with value "([^"]+)"$/')]
    #[Given('/^(this product) has(?:| also)(?:| a) select attribute "([^"]+)" with values "([^"]+)" and "([^"]+)"$/')]
    public function this_product_has_select_attribute_with_values(Product_Interface $product, string $product_attribute_name, string ...$product_attribute_values): void
    {
        $this->create_select_product_attribute_value($product, $product_attribute_name, $product_attribute_values);
    }
    #[Given('/^(this product) has(?:| also)(?:| a) select attribute "([^"]+)" with value "([^"]+)" in ("[^"]+" locale)$/')]
    public function this_product_has_select_attribute_with_value_in_locale(Product_Interface $product, string $product_attribute_name, string $product_attribute_value, string $locale_code): void
    {
        $this->create_select_product_attribute_value($product, $product_attribute_name, [$product_attribute_value], $locale_code);
    }
    #[Given('/^(this product) has a (text|textarea) attribute "([^"]+)" with value "([^"]+)"$/')]
    #[Given('/^(this product) has a (text|textarea) attribute "([^"]+)" with value "([^"]+)" in ("[^"]+" locale)$/')]
    public function this_product_has_attribute_with_value(Product_Interface $product, string $product_attribute_type, string $product_attribute_name, string $value, string $language = 'en_US'): void
    {
        $attribute = $this->provide_product_attribute($product_attribute_type, $product_attribute_name);
        $attribute_value = $this->create_product_attribute_value($value, $attribute, $language);
        $product->add_attribute($attribute_value);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has non-translatable (text|textarea) attribute "([^"]+)" with value "([^"]+)"$/')]
    public function this_product_has_non_translatable_text_attribute_with_value(Product_Interface $product, string $product_attribute_type, string $product_attribute_name, string $value, string $language = 'en_US'): void
    {
        $attribute = $this->provide_product_attribute($product_attribute_type, $product_attribute_name);
        $attribute_value = $this->create_product_attribute_value($value, $attribute, $language, false);
        $product->add_attribute($attribute_value);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has a percent attribute "([^"]+)" with value ([^"]+)%$/')]
    public function this_product_has_percent_attribute_with_value(Product_Interface $product, $product_attribute_name, $value): void
    {
        $attribute = $this->provide_product_attribute('percent', $product_attribute_name);
        $attribute_value = $this->create_product_attribute_value($value / 100, $attribute);
        $product->add_attribute($attribute_value);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has non-translatable percent attribute "([^"]+)" with value ([^"]+)%$/')]
    public function this_product_has_non_translatable_percent_attribute_with_value(Product_Interface $product, string $product_attribute_name, int $value): void
    {
        $attribute = $this->provide_product_attribute('percent', $product_attribute_name);
        $attribute_value = $this->create_product_attribute_value($value / 100, $attribute, null, false);
        $product->add_attribute($attribute_value);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has a "([^"]+)" attribute "([^"]+)" set to "([^"]+)"$/')]
    public function this_product_has_checkbox_attribute_with_value(Product_Interface $product, $product_attribute_type, $product_attribute_name, $value): void
    {
        $attribute = $this->provide_product_attribute($product_attribute_type, $product_attribute_name);
        $boolean_value = 'Yes' === $value;
        $attribute_value = $this->create_product_attribute_value($boolean_value, $attribute);
        $product->add_attribute($attribute_value);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has non-translatable "([^"]+)" attribute "([^"]+)" set to "([^"]+)"$/')]
    public function this_product_has_non_translatable_checkbox_attribute_with_value(Product_Interface $product, string $product_attribute_type, string $product_attribute_name, $value): void
    {
        $attribute = $this->provide_product_attribute($product_attribute_type, $product_attribute_name);
        $boolean_value = 'Yes' === $value;
        $attribute_value = $this->create_product_attribute_value($boolean_value, $attribute, 'en_US', false);
        $product->add_attribute($attribute_value);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has percent attribute "([^"]+)" at position (\d+)$/')]
    public function this_product_has_percent_attribute_with_value_at_position(Product_Interface $product, $product_attribute_name, $position): void
    {
        $attribute = $this->provide_product_attribute('percent', $product_attribute_name);
        $attribute->set_position((int) $position);
        $attribute_value = $this->create_product_attribute_value(random_int(1, 100) / 100, $attribute);
        $product->add_attribute($attribute_value);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has a ([^"]+) attribute "([^"]+)" with date "([^"]+)"$/')]
    public function this_product_has_date_time_attribute_with_date(Product_Interface $product, $product_attribute_type, $product_attribute_name, $date): void
    {
        $attribute = $this->provide_product_attribute($product_attribute_type, $product_attribute_name);
        $attribute_value = $this->create_product_attribute_value(new \DateTime($date), $attribute);
        $product->add_attribute($attribute_value);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has non-translatable ([^"]+) attribute "([^"]+)" with date "([^"]+)"$/')]
    public function this_product_has_non_translatable_date_time_attribute_with_date(Product_Interface $product, string $product_attribute_type, string $product_attribute_name, $date): void
    {
        $attribute = $this->provide_product_attribute($product_attribute_type, $product_attribute_name);
        $attribute_value = $this->create_product_attribute_value(new \DateTime($date), $attribute, 'en_US', false);
        $product->add_attribute($attribute_value);
        $this->object_manager->flush();
    }
    #[When('/^(this product attribute)\'s value changed from "([^"]+)" to "([^"]+)"$/')]
    public function this_attribute_value_changed_from_to(Product_Attribute_Interface $attribute, string $from, string $to): void
    {
        $configuration = $attribute->get_configuration();
        $choices = $configuration['choices'] ?? [];
        foreach ($choices as $uuid => $choice) {
            foreach ($choice as $locale_code => $item) {
                if ($item === $from) {
                    $choices[$uuid][$locale_code] = $to;
                    break 2;
                }
            }
        }
        $configuration['choices'] = $choices;
        $attribute->set_configuration($configuration);
        $this->object_manager->flush();
    }
    #[When('/^(this product attribute)\'s value "([^"]+)" has been removed$/')]
    public function this_attribute_value_has_been_removed(Product_Attribute_Interface $attribute, string $value): void
    {
        $configuration = $attribute->get_configuration();
        $choices = $configuration['choices'] ?? [];
        foreach ($choices as $uuid => $choice) {
            foreach ($choice as $item) {
                if ($value === $item) {
                    unset($choices[$uuid]);
                    break 2;
                }
            }
        }
        $configuration['choices'] = $choices;
        $attribute->set_configuration($configuration);
        $this->object_manager->flush();
    }
    private function create_product_attribute(string $type, string $name, ?string $code = null, bool $translatable = true): Product_Attribute_Interface
    {
        /** @var ProductAttributeInterface $productAttribute */
        $product_attribute = $this->product_attribute_factory->create_typed($type);
        $code = $code ?: String_Inflector::name_to_code($name);
        $product_attribute->set_code($code);
        $product_attribute->set_translatable($translatable);
        $product_attribute->set_name($name);
        return $product_attribute;
    }
    /**
     * @param string $name
     * @param string|null $code
     * @return ProductAttributeInterface
     */
    private function provide_product_attribute(string $type, $name, $code = null)
    {
        $code = $code ?: String_Inflector::name_to_code($name);
        /** @var ProductAttributeInterface $productAttribute */
        $product_attribute = $this->product_attribute_repository->find_one_by(['code' => $code]);
        if (null !== $product_attribute) {
            return $product_attribute;
        }
        $product_attribute = $this->create_product_attribute($type, $name, $code);
        $this->save_product_attribute($product_attribute);
        return $product_attribute;
    }
    private function create_product_attribute_value(string|int|float|bool|\DateTime|array $value, Product_Attribute_Interface $attribute, ?string $locale_code = 'en_US', bool $translatable = true): Product_Attribute_Value_Interface
    {
        $attribute->set_translatable($translatable);
        $this->object_manager->persist($attribute);
        /** @var ProductAttributeValueInterface $attributeValue */
        $attribute_value = $this->product_attribute_value_factory->create_new();
        $attribute_value->set_attribute($attribute);
        $attribute_value->set_value($value);
        $attribute_value->set_locale_code($locale_code);
        $this->object_manager->persist($attribute_value);
        return $attribute_value;
    }
    private function save_product_attribute(Product_Attribute_Interface $product_attribute): void
    {
        $this->product_attribute_repository->add($product_attribute);
        $this->shared_storage->set('product_attribute', $product_attribute);
    }
    private function create_select_product_attribute_value(Product_Interface $product, string $product_attribute_name, array $values, string $locale_code = 'en_US'): void
    {
        $attribute = $this->provide_product_attribute(Select_Attribute_Type::TYPE, $product_attribute_name);
        $choices = $attribute->get_configuration()['choices'];
        $choice_keys = [];
        foreach ($values as $value) {
            foreach ($choices as $choice_key => $choice_values) {
                $key = array_search($value, $choice_values);
                if ($locale_code === $key) {
                    $choice_keys[] = $choice_key;
                }
            }
        }
        $attribute_value = $this->create_product_attribute_value($choice_keys, $attribute, $locale_code);
        $product->add_attribute($attribute_value);
        $this->object_manager->flush();
    }
}
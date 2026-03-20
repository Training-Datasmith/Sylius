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
use Behat\Gherkin\Node\Table_Node;
use Behat\Mink\Element\Node_Element;
use Behat\Step\Given;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Event\Product_Updated;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Channel_Pricing_Interface;
use Sylius\Component\Core\Model\Product_Image_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Product_Taxon_Interface;
use Sylius\Component\Core\Model\Product_Translation_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Core\Repository\Product_Repository_Interface;
use Sylius\Component\Core\Repository\Product_Variant_Repository_Interface;
use Sylius\Component\Core\Uploader\Image_Uploader_Interface;
use Sylius\Component\Product\Factory\Product_Factory_Interface;
use Sylius\Component\Product\Generator\Product_Variant_Generator_Interface;
use Sylius\Component\Product\Generator\Slug_Generator_Interface;
use Sylius\Component\Product\Model\Product_Option;
use Sylius\Component\Product\Model\Product_Option_Interface;
use Sylius\Component\Product\Model\Product_Option_Value_Interface;
use Sylius\Component\Product\Model\Product_Variant_Translation_Interface;
use Sylius\Component\Product\Resolver\Product_Variant_Resolver_Interface;
use Sylius\Component\Shipping\Model\Shipping_Category_Interface;
use Sylius\Component\Taxation\Model\Tax_Category_Interface;
use Sylius\Resource\Factory\Factory_Interface;
use Symfony\Component\Http_Foundation\File\Uploaded_File;
use Symfony\Component\Messenger\Message_Bus_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Context implements Context
{
    /**
     * @param ProductRepositoryInterface<ProductInterface> $productRepository
     * @param ProductFactoryInterface<ProductInterface> $productFactory
     * @param FactoryInterface<ProductTranslationInterface> $productTranslationFactory
     * @param FactoryInterface<ProductVariantInterface> $productVariantFactory
     * @param FactoryInterface<ProductVariantTranslationInterface> $productVariantTranslationFactory
     * @param FactoryInterface<ChannelPricingInterface> $channelPricingFactory
     * @param FactoryInterface<ProductOptionInterface> $productOptionFactory
     * @param FactoryInterface<ProductOptionValueInterface> $productOptionValueFactory
     * @param FactoryInterface<ProductImageInterface> $productImageFactory
     * @param FactoryInterface<ProductTaxonInterface> $productTaxonFactory
     * @param ProductVariantRepositoryInterface<ProductVariantInterface> $productVariantRepository
     */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Product_Repository_Interface $product_repository, private Product_Factory_Interface $product_factory, private Factory_Interface $product_translation_factory, private Factory_Interface $product_variant_factory, private Factory_Interface $product_variant_translation_factory, private Factory_Interface $channel_pricing_factory, private Factory_Interface $product_option_factory, private Factory_Interface $product_option_value_factory, private Factory_Interface $product_image_factory, private Factory_Interface $product_taxon_factory, private Object_Manager $object_manager, private Product_Variant_Generator_Interface $product_variant_generator, private Product_Variant_Repository_Interface $product_variant_repository, private Product_Variant_Resolver_Interface $default_variant_resolver, private Image_Uploader_Interface $image_uploader, private Slug_Generator_Interface $slug_generator, private \ArrayAccess $mink_parameters, private Message_Bus_Interface $event_bus, private Product_Taxon_Context $product_taxon_context)
    {
    }
    #[Given('/^the store(?:| also) has a product "([^"]+)" priced at ("[^"]+")$/')]
    #[Given('the store has a product :productName')]
    #[Given('the store has a :productName product')]
    #[Given('I added a product :productName')]
    #[Given('/^the store(?:| also) has a product "([^"]+)" priced at ("[^"]+") in ("[^"]+" channel)$/')]
    public function store_has_a_product_priced_at(string $product_name, int $price = 100, ?Channel_Interface $channel = null): void
    {
        $product = $this->create_product($product_name, $price, $channel);
        $this->save_product($product);
    }
    #[Given('/^the store(?:| also) has a product "([^"]+)" priced at ("[^"]+") belonging to the ("[^"]+" taxon)$/')]
    public function store_has_a_product_priced_at_belonging_to_the_taxon(string $product_name, int $price, Taxon_Interface $taxon): void
    {
        $product = $this->create_product($product_name, $price);
        $this->product_taxon_context->it_belongs_to($product, $taxon);
        $this->save_product($product);
    }
    #[Given('/^the store(?:| also) has a product "([^"]+)" belonging to the ("[^"]+" taxon)$/')]
    #[Given('/^the store(?:| also) has a product "([^"]+)" belonging to (this taxon)$/')]
    public function store_has_a_product_belonging_to_the_taxon(string $product_name, Taxon_Interface $taxon): void
    {
        $product = $this->create_product($product_name);
        $this->product_taxon_context->it_belongs_to($product, $taxon);
        $this->save_product($product);
    }
    #[Given('/^the store(?:| also) has a product "([^"]+)" in the ("[^"]+" taxon) at (\d+)(?:st|nd|rd|th) position$/')]
    public function the_store_has_a_product_in_the_taxon_at_position(string $product_name, Taxon_Interface $taxon, int $position): void
    {
        $product = $this->create_product($product_name);
        $product_taxon = $this->create_product_taxon($taxon, $product, $position);
        $product->add_product_taxon($product_taxon);
        $this->save_product($product);
    }
    #[Given('the store has :numberOfProducts products')]
    public function store_has_more_products(int $number_of_products): void
    {
        for ($i = 0; $i < $number_of_products; ++$i) {
            $product = $this->create_product('TEST' . $i);
            $this->save_product($product);
        }
    }
    #[Given('/^(this product) is originally priced at ("[^"]+") in ("[^"]+" channel)$/')]
    public function this_product_has_originally_price_in_channel(Product_Interface $product, int $original_price, Channel_Interface $channel): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->default_variant_resolver->get_variant($product);
        /** @var ChannelPricingInterface $channelPricing */
        $channel_pricing = $product_variant->get_channel_pricing_for_channel($channel);
        $channel_pricing->set_original_price($original_price);
        $this->save_product($product);
    }
    #[Given('/^(this product) is(?:| also) priced at ("[^"]+") in ("[^"]+" channel)$/')]
    public function this_product_is_also_priced_at_in_channel(Product_Interface $product, int $price, Channel_Interface $channel): void
    {
        $product->add_channel($channel);
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->default_variant_resolver->get_variant($product);
        if (!$product_variant->has_channel_pricing_for_channel($channel)) {
            $product_variant->add_channel_pricing($this->create_channel_pricing_for_channel($price, $channel));
        }
        $this->object_manager->flush();
    }
    #[Given('/^(this product) is(?:| also) available in ("[^"]+" channel)$/')]
    #[Given('/^(this product) is(?:| also) available in the ("[^"]+" channel)$/')]
    public function this_product_is_also_available_in_channel(Product_Interface $product, Channel_Interface $channel): void
    {
        $this->this_product_is_also_priced_at_in_channel($product, 0, $channel);
    }
    #[Given('/^(this product) is(?:| also) unavailable in ("[^"]+" channel)$/')]
    #[Given('/^(this product) is disabled in ("[^"]+" channel)$/')]
    public function this_product_is_also_unavailable_in_channel(Product_Interface $product, Channel_Interface $channel): void
    {
        $product->remove_channel($channel);
        $this->object_manager->flush();
    }
    #[Given('the store( also) has a product :productName with code :code')]
    #[Given('the store( also) has a product :productName with code :code, created at :date')]
    public function store_has_product_with_code(string $product_name, $code, $date = 'now'): void
    {
        $product = $this->create_product($product_name);
        $product->set_created_at(new \DateTime($date));
        $product->set_code($code);
        $this->save_product($product);
    }
    #[Given('/^the store(?:| also) has a product "([^"]+)" priced at ("[^"]+") available in (channel "[^"]+") and (channel "[^"]+")$/')]
    public function store_has_a_product_priced_at_available_in_channels(string $product_name, int $price = 100, ...$channels): void
    {
        $product = $this->create_product($product_name, $price);
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $product->get_enabled_variants()->first();
        foreach ($channels as $channel) {
            $product->add_channel($channel);
            if (!$product_variant->has_channel_pricing_for_channel($channel)) {
                $product_variant->add_channel_pricing($this->create_channel_pricing_for_channel($price, $channel));
            }
        }
        $this->save_product($product);
    }
    #[Given('/^(this product) is named "([^"]+)" (in the "([^"]+)" locale)$/')]
    #[Given('/^the (product "[^"]+") is named "([^"]+)" (in the "([^"]+)" locale)$/')]
    public function this_product_is_named_in(Product_Interface $product, $name, $locale): void
    {
        $this->add_product_translation($product, $name, $locale);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has no translation in the "([^"]+)" locale$/')]
    public function this_product_has_no_translation_in(Product_Interface $product, ?string $locale): void
    {
        $product->remove_translation($product->get_translation($locale));
        $this->object_manager->flush();
    }
    #[Given('/^the store has a product named "([^"]+)" in ("[^"]+" locale) and "([^"]+)" in ("[^"]+" locale)$/')]
    public function the_store_has_product_named_in_and_in(string $first_name, $first_locale, $second_name, $second_locale): void
    {
        $product = $this->create_product($first_name);
        $names = [$first_name => $first_locale, $second_name => $second_locale];
        foreach ($names as $name => $locale) {
            $this->add_product_translation($product, $name, $locale);
        }
        $this->save_product($product);
    }
    #[Given('/^the store has(?:| a| an) "([^"]+)" configurable product$/')]
    #[Given('/^the store has(?:| a| an) "([^"]+)" configurable product with "([^"]+)" slug$/')]
    public function store_has_a_configurable_product($product_name, $slug = null): void
    {
        /** @var ChannelInterface|null $channel */
        $channel = null;
        if ($this->shared_storage->has('channel')) {
            $channel = $this->shared_storage->get('channel');
        }
        /** @var ProductInterface $product */
        $product = $this->product_factory->create_new();
        $product->set_code(String_Inflector::name_to_uppercase_code($product_name));
        if (null !== $channel) {
            $product->add_channel($channel);
            foreach ($channel->get_locales() as $locale) {
                $product->set_fallback_locale($locale->get_code());
                $product->set_current_locale($locale->get_code());
                $product->set_name($product_name);
                $product->set_slug($slug ?: $this->slug_generator->generate($product_name));
            }
        }
        $this->save_product($product);
    }
    #[Given('the store has( also) :firstProductName and :secondProductName products')]
    #[Given('the store has( also) :firstProductName, :secondProductName and :thirdProductName products')]
    #[Given('the store has( also) :firstProductName, :secondProductName, :thirdProductName and :fourthProductName products')]
    public function the_store_has_products(...$products_names): void
    {
        foreach ($products_names as $product_name) {
            $this->save_product($this->create_product($product_name));
        }
    }
    #[Given('/^(this channel) has "([^"]+)", "([^"]+)", "([^"]+)" and "([^"]+)" products$/')]
    #[Given('/^the ("[^"]+" channel) has a product "([^"]+)"$/')]
    #[Given('/^the ("[^"]+" channel) has "([^"]+)" and "([^"]+)" products$/')]
    #[Given('/^the ("[^"]+" channel) has "([^"]+)", "([^"]+)", "([^"]+)" and "([^"]+)" products$/')]
    public function this_channel_has_products(Channel_Interface $channel, ...$products_names): void
    {
        foreach ($products_names as $product_name) {
            $product = $this->create_product($product_name, 0, $channel);
            $this->save_product($product);
        }
    }
    #[Given('/^the (product "[^"]+") has(?:| a) "([^"]+)" variant priced at ("[^"]+")$/')]
    #[Given('/^(this product)(?:| also) has "([^"]+)" variant priced at ("[^"]+")$/')]
    #[Given('/^(this product) has "([^"]+)" variant priced at ("[^"]+") in ("([^"]+)" channel)$/')]
    public function the_product_has_variant_priced_at(Product_Interface $product, $product_variant_name, $price, ?Channel_Interface $channel = null): void
    {
        $this->create_product_variant($product, $product_variant_name, $price, String_Inflector::name_to_uppercase_code($product_variant_name), $channel ?? $this->shared_storage->get('channel'));
    }
    #[Given('/^the (product "[^"]+") has(?:| a) "([^"]+)" variant priced at ("[^"]+") configured with ("[^"]+" option value)$/')]
    #[Given('/^(this product) has "([^"]+)" variant priced at ("[^"]+") configured with ("[^"]+" option value)$/')]
    public function the_product_has_variant_priced_at_configured_with_option_value(Product_Interface $product, string $product_variant_name, int $price, Product_Option_Value_Interface $option_value): void
    {
        $this->create_product_variant($product, $product_variant_name, $price, String_Inflector::name_to_uppercase_code($product_variant_name), $this->shared_storage->get('channel'), optionValue: $option_value);
    }
    #[Given('/^("[^"]+" variant) priced at ("[^"]+") in ("[^"]+" channel)$/')]
    public function variant_priced_at_in_channel(Product_Variant_Interface $product_variant, int $price, Channel_Interface $channel): void
    {
        $product_variant->add_channel_pricing($this->create_channel_pricing_for_channel($price, $channel));
        $this->shared_storage->set('variant', $product_variant);
    }
    #[Given('/^("[^"]+" variant) is originally priced at ("[^"]+") in ("[^"]+" channel)$/')]
    public function variant_is_original_priced_at_in_channel(Product_Variant_Interface $product_variant, int $original_price, Channel_Interface $channel): void
    {
        /** @var ChannelPricingInterface $channelPricing */
        $channel_pricing = $product_variant->get_channel_pricing_for_channel($channel);
        $channel_pricing->set_original_price($original_price);
    }
    #[Given('/^the ("[^"]+" variant) has minimum price of ("[^"]+") in the ("[^"]+" channel)$/')]
    public function variant_has_minimum_price_in_channel(Product_Variant_Interface $product_variant, int $minimum_price, Channel_Interface $channel): void
    {
        /** @var ChannelPricingInterface $channelPricing */
        $channel_pricing = $product_variant->get_channel_pricing_for_channel($channel);
        $channel_pricing->set_minimum_price($minimum_price);
    }
    #[Given('/^the (product "[^"]+") has(?:| a| an) "([^"]+)" variant$/')]
    #[Given('/^(this product) has(?:| a| an) "([^"]+)" variant$/')]
    #[Given('/^(this product) has "([^"]+)" and "([^"]+)" variants$/')]
    #[Given('/^(this product) has "([^"]+)", "([^"]+)" and "([^"]+)" variants$/')]
    public function the_product_has_variants(Product_Interface $product, ...$variant_names): void
    {
        $channel = $this->shared_storage->get('channel');
        foreach ($variant_names as $name) {
            $this->create_product_variant($product, $name, 0, String_Inflector::name_to_uppercase_code($name), $channel);
        }
    }
    #[Given('/^the (product "[^"]+")(?:| also) has a nameless variant with code "([^"]+)"$/')]
    #[Given('/^(this product)(?:| also) has a nameless variant with code "([^"]+)"$/')]
    #[Given('/^(it)(?:| also) has a nameless variant with code "([^"]+)"$/')]
    public function the_product_has_nameless_variant_with_code(Product_Interface $product, $variant_code): void
    {
        $channel = $this->shared_storage->get('channel');
        $this->create_product_variant($product, null, 0, $variant_code, $channel);
    }
    #[Given('/^the (product "[^"]+")(?:| also) has(?:| a| an) "([^"]+)" variant with code "([^"]+)"$/')]
    #[Given('/^(this product)(?:| also) has(?:| a| an) "([^"]+)" variant with code "([^"]+)"$/')]
    #[Given('/^(it)(?:| also) has(?:| a| an) "([^"]+)" variant with code "([^"]+)"$/')]
    public function the_product_has_variant_with_code(Product_Interface $product, $variant_name, $variant_code): void
    {
        $channel = $this->shared_storage->get('channel');
        $this->create_product_variant($product, $variant_name, 0, $variant_code, $channel);
    }
    #[Given('/^(this product) has "([^"]+)" variant priced at ("[^"]+") which does not require shipping$/')]
    public function the_product_has_variant_which_does_not_require_shipping(Product_Interface $product, $product_variant_name, $price): void
    {
        $this->create_product_variant($product, $product_variant_name, $price, String_Inflector::name_to_uppercase_code($product_variant_name), $this->shared_storage->get('channel'), null, false);
    }
    #[Given('/^the (product "[^"]+") has(?:| also)(?:| a| an) "([^"]+)" variant$/')]
    #[Given('/^the (product "[^"]+") has(?:| also)(?:| a| an) "([^"]+)" variant at position ([^"]+)$/')]
    #[Given('/^(this product) has(?:| also)(?:| a| an) "([^"]+)" variant at position ([^"]+)$/')]
    public function the_product_has_variant_at_position(Product_Interface $product, $product_variant_name, $position = null): void
    {
        $this->create_product_variant($product, $product_variant_name, 0, String_Inflector::name_to_uppercase_code($product_variant_name), $this->shared_storage->get('channel'), $position);
    }
    #[Given('/^(this variant) is also priced at ("[^"]+") in ("([^"]+)" channel)$/')]
    public function this_variant_is_also_priced_at_in_channel(Product_Variant_Interface $product_variant, int $price, Channel_Interface $channel): void
    {
        $channel_pricing = $product_variant->get_channel_pricing_for_channel($channel);
        if (null === $channel_pricing) {
            $product_variant->add_channel_pricing($this->create_channel_pricing_for_channel($price, $channel));
        } else {
            $channel_pricing->set_price($price);
        }
        $this->object_manager->flush();
    }
    #[Given('/^(it|this product) has(?:| also) variant named "([^"]+)" in ("[^"]+" locale) and "([^"]+)" in ("[^"]+" locale)$/')]
    public function it_has_variant_named_in_and_in(Product_Interface $product, $first_name, $first_locale, $second_name, $second_locale): void
    {
        $product_variant = $this->create_product_variant($product, $first_name, 100, String_Inflector::name_to_uppercase_code($first_name), $this->shared_storage->get('channel'));
        $names = [$first_name => $first_locale, $second_name => $second_locale];
        foreach ($names as $name => $locale) {
            $this->add_product_variant_translation($product_variant, $name, $locale);
        }
        $this->object_manager->flush();
    }
    #[Given('/^(it|this product)(?:| also) has a variant named "([^"]+)" in ("[^"]+" locale)$/')]
    public function it_has_variant_named_in(Product_Interface $product, string $name, string $locale): void
    {
        $product_variant = $this->create_product_variant($product, $name, 100, String_Inflector::name_to_uppercase_code($name), $this->shared_storage->get('channel'));
        $this->add_product_variant_translation($product_variant, $name, $locale);
        $this->object_manager->flush();
    }
    #[Given('/^(this variant) has no translation in ("[^"]+" locale)$/')]
    public function this_variant_has_no_translation_in(Product_Variant_Interface $product_variant, string $locale): void
    {
        $translation = $product_variant->get_translation($locale);
        if ($translation->get_locale() !== $locale) {
            return;
        }
        $product_variant->remove_translation($translation);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has "([^"]+)" variant priced at ("[^"]+") identified by "([^"]+)"$/')]
    public function the_product_has_variant_priced_at_identified_by(Product_Interface $product, $product_variant_name, $price, $code): void
    {
        $this->create_product_variant($product, $product_variant_name, $price, $code, $this->shared_storage->get('channel'));
    }
    #[Given('/^(this product) only variant was renamed to "([^"]+)"$/')]
    public function product_only_variant_was_renamed(Product_Interface $product, ?string $variant_name): void
    {
        Assert::true($product->is_simple());
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $product->get_variants()->first();
        $product_variant->set_name($variant_name);
        $this->object_manager->flush();
    }
    #[Given('/^there is product "([^"]+)" available in ((?:this|that|"[^"]+") channel)$/')]
    #[Given('/^the store has a product "([^"]+)" available in ("([^"]+)" channel)$/')]
    public function there_is_product_available_in_given_channel(string $product_name, Channel_Interface $channel): void
    {
        $product = $this->create_product(productName: $product_name, channel: $channel);
        $this->save_product($product);
    }
    #[Given('/^([^"]+) belongs to ("[^"]+" tax category)$/')]
    #[Given('the product :product belongs to :taxCategory tax category')]
    public function product_belongs_to_tax_category(Product_Interface $product, Tax_Category_Interface $tax_category): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $this->default_variant_resolver->get_variant($product);
        $variant->set_tax_category($tax_category);
        $this->object_manager->flush();
    }
    #[Given('/^(it) comes in the following variations:$/')]
    public function it_comes_in_the_following_variations(Product_Interface $product, Table_Node $table): void
    {
        $channel = $this->shared_storage->get('channel');
        foreach ($table->get_hash() as $variant_hash) {
            /** @var ProductVariantInterface $variant */
            $variant = $this->product_variant_factory->create_new();
            $variant->set_name($variant_hash['name']);
            $variant->set_code(String_Inflector::name_to_uppercase_code($variant_hash['name']));
            $variant->add_channel_pricing($this->create_channel_pricing_for_channel($this->get_price_from_string(str_replace(['$', '€', '£'], '', $variant_hash['price'])), $channel));
            $variant->set_product($product);
            $product->add_variant($variant);
        }
        $this->object_manager->flush();
    }
    #[Given('/^("[^"]+" variant of product "[^"]+") belongs to ("[^"]+" tax category)$/')]
    public function product_variant_belongs_to_tax_category(Product_Variant_Interface $product_variant, Tax_Category_Interface $tax_category): void
    {
        $product_variant->set_tax_category($tax_category);
        $this->object_manager->persist($product_variant);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has option "([^"]+)" with values "([^"]+)" and "([^"]+)"$/')]
    #[Given('/^(this product) has option "([^"]+)" with values "([^"]+)", "([^"]+)" and "([^"]+)"$/')]
    public function this_product_has_option_with_values(Product_Interface $product, string $option_name, ...$values): void
    {
        $this->add_option_to_product($product, $option_name, $values);
    }
    #[Given('/^(this product) has an option "([^"]*)" without any values$/')]
    public function this_product_has_an_option_without_any_values(Product_Interface $product, string $option_name): void
    {
        $this->add_option_to_product($product, $option_name, []);
    }
    #[Given('/^there (?:is|are) (\d+) unit(?:|s) of (product "([^"]+)") available in the inventory$/')]
    public function there_is_quantity_of_product_available_in_the_inventory(int $quantity, Product_Interface $product): void
    {
        $this->update_on_hand($product, $quantity);
    }
    #[Given('/^there (?:is|are) (\d+) unit(?:|s) of tracked (product "([^"]+)") available in the inventory$/')]
    public function there_is_quantity_of_tracked_product_available_in_the_inventory(int $quantity, Product_Interface $product): void
    {
        $this->update_on_hand($product, $quantity, true);
    }
    #[Given('/^the (product "([^"]+)") is out of stock$/')]
    public function the_product_is_out_of_stock(Product_Interface $product): void
    {
        $this->update_on_hand($product, 0, true);
    }
    #[Given('other customer has bought :quantity :product products by this time')]
    public function other_customer_has_bought_products_by_this_time(int $quantity, Product_Interface $product): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->default_variant_resolver->get_variant($product);
        $product_variant->set_on_hand($product_variant->get_on_hand() - $quantity);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) is tracked by the inventory$/')]
    #[Given('/^(?:|the )("[^"]+" product) is(?:| also) tracked by the inventory$/')]
    public function this_product_is_tracked_by_the_inventory(Product_Interface $product): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->default_variant_resolver->get_variant($product);
        $product_variant->set_tracked(true);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) is available in "([^"]+)" ([^"]+) priced at ("[^"]+")$/')]
    public function this_product_is_available_in_size(Product_Interface $product, string $option_value_name, string $option_name, int $price): void
    {
        $this->create_product_variant_with_option($product, $option_name, $option_value_name, $price);
    }
    #[Given('/^(this product) with "([^"]+)" option "([^"]+)" is priced at ("[^"]+")$/')]
    public function this_product_with_option_is_priced_at(Product_Interface $product, string $option_name, string $option_value_name, int $price): void
    {
        $this->create_product_variant_with_option($product, $option_name, $option_value_name, $price);
    }
    #[Given('the :product product\'s :optionValueName size belongs to :shippingCategory shipping category')]
    public function this_product_size_belongs_to_shipping_category(Product_Interface $product, $option_value_name, Shipping_Category_Interface $shipping_category): void
    {
        $code = sprintf('%s_%s', $product->get_code(), $option_value_name);
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $product->get_variants()->filter(fn($variant): bool => $code === $variant->get_code())->first();
        Assert::not_null($product_variant, sprintf('Product variant with given code %s not exists!', $code));
        $product_variant->set_shipping_category($shipping_category);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has (this product option)$/')]
    #[Given('/^(this product) has (?:a|an) ("[^"]+" option)$/')]
    public function this_product_has_this_product_option(Product_Interface $product, Product_Option_Interface $option): void
    {
        $product->add_option($option);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has all possible variants$/')]
    public function this_product_has_all_possible_variants(Product_Interface $product): void
    {
        try {
            foreach ($product->get_variants() as $product_variant) {
                $product->remove_variant($product_variant);
            }
            $this->product_variant_generator->generate($product);
        } catch (\Exception) {
            /** @var ProductVariantInterface $productVariant */
            $product_variant = $this->product_variant_factory->create_new();
            $product->add_variant($product_variant);
        }
        $i = 0;
        /** @var ProductVariantInterface $productVariant */
        foreach ($product->get_variants() as $product_variant) {
            $product_variant->set_code(sprintf('%s-variant-%d', $product->get_code(), $i));
            foreach ($product->get_channels() as $channel) {
                $product_variant->add_channel_pricing($this->create_channel_pricing_for_channel(1000, $channel));
            }
            ++$i;
        }
        $this->object_manager->flush();
    }
    #[Given('/^there are ([^"]+) units of ("[^"]+" variant of product "[^"]+") available in the inventory$/')]
    public function there_are_items_of_product_in_variant_available_in_the_inventory($quantity, Product_Variant_Interface $product_variant): void
    {
        $product_variant->set_tracked(true);
        $product_variant->set_on_hand((int) $quantity);
        $this->object_manager->flush();
    }
    #[Given('/^the ("[^"]+" product variant) is tracked by the inventory$/')]
    public function the_product_variant_is_tracked_by_the_inventory(Product_Variant_Interface $product_variant): void
    {
        $product_variant->set_tracked(true);
        $this->object_manager->flush();
    }
    #[Given('/^(this product)\'s price is ("[^"]+")$/')]
    #[Given('/^the (product "[^"]+") changed its price to ("[^"]+")$/')]
    #[Given('/^(this product) price has been changed to ("[^"]+")$/')]
    public function the_product_changed_its_price_to(Product_Interface $product, int $price): void
    {
        /** @var false|ProductInterface $productVariant */
        $product_variant = $product->get_variants()->first();
        Assert::is_instance_of($product_variant, Product_Variant_Interface::class);
        $product_variant_id = $product_variant->get_id();
        $product_variant = $this->product_variant_repository->find($product_variant_id);
        $channel_pricing = $product_variant->get_channel_pricing_for_channel($this->shared_storage->get('channel'));
        $channel_pricing->set_price($price);
        $this->object_manager->flush();
    }
    #[Given('/^(this product)\'s weight is (\d+(?:\.\d+)?)$/')]
    public function the_product_changed_its_weight_to(Product_Interface $product, float $weight): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->default_variant_resolver->get_variant($product);
        $product_variant->set_weight($weight);
        $this->object_manager->flush();
    }
    #[Given('/^(this product)\'s price in ("[^"]+" channel) is ("[^"]+")$/')]
    public function the_product_price_in_channel_is(Product_Interface $product, Channel_Interface $channel, int $price): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->default_variant_resolver->get_variant($product);
        $channel_pricing = $product_variant->get_channel_pricing_for_channel($channel);
        $channel_pricing->set_price($price);
        $this->object_manager->flush();
    }
    #[Given('/^(this product)(?:| also) has an image "([^"]+)" with "([^"]+)" type$/')]
    #[Given('/^the ("[^"]+" product)(?:| also) has an image "([^"]+)" with "([^"]+)" type$/')]
    #[Given('/^(it)(?:| also) has an image "([^"]+)" with "([^"]+)" type$/')]
    public function this_product_has_an_image_with_type(Product_Interface $product, string $image_path, string $image_type): void
    {
        $this->create_product_image($product, $image_path, $image_type);
    }
    #[Given('/^(this product) has an image "([^"]+)" with "([^"]+)" type at position (\d+)$/')]
    public function this_product_has_an_image_with_type_at_position(Product_Interface $product, string $image_path, string $image_type, int $position): void
    {
        $this->create_product_image($product, $image_path, $image_type, null, $position);
    }
    #[Given('/^(this product) has an image "([^"]+)" with "([^"]+)" type at position (\d+) for ("[^"]+" variant)$/')]
    public function this_product_has_an_image_with_type_for_variant(Product_Interface $product, string $image_path, string $image_type, int $position, Product_Variant_Interface $variant): void
    {
        $this->create_product_image($product, $image_path, $image_type, $variant, $position);
    }
    #[Given('/^(this product) belongs to ("([^"]+)" shipping category)$/')]
    #[Given('product :product belongs to :shippingCategory shipping category')]
    #[Given('product :product shipping category has been changed to :shippingCategory')]
    public function this_product_belongs_to_shipping_category(Product_Interface $product, Shipping_Category_Interface $shipping_category): void
    {
        $product->get_variants()->first()->set_shipping_category($shipping_category);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) has been disabled$/')]
    #[Given('the product :product has been disabled')]
    public function this_product_has_been_disabled(Product_Interface $product): void
    {
        $product->disable();
        $this->object_manager->flush();
    }
    #[Given('the product :product was renamed to :productName')]
    public function the_product_was_renamed_to(Product_Interface $product, string $product_name): void
    {
        $product->set_name($product_name);
        $this->object_manager->flush();
    }
    #[Given('/^(this product) does not require shipping$/')]
    public function this_product_does_not_require_shipping(Product_Interface $product): void
    {
        /** @var ProductVariantInterface $variant */
        foreach ($product->get_variants() as $variant) {
            $variant->set_shipping_required(false);
        }
        $this->object_manager->flush();
    }
    #[Given('product\'s :product code is :code')]
    public function product_code_is(Product_Interface $product, string $code): void
    {
        $product->set_code($code);
        $this->object_manager->flush();
    }
    #[Given('the product :product has height :height, width :width, depth :depth, weight :weight')]
    public function product_has_dimensions(Product_Interface $product, float $height, float $width, float $depth, float $weight): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->default_variant_resolver->get_variant($product);
        $product_variant->set_width($width);
        $product_variant->set_height($height);
        $product_variant->set_depth($depth);
        $product_variant->set_weight($weight);
        $this->object_manager->flush();
    }
    #[Given('the product :product has the slug :slug')]
    public function product_has_slug(Product_Interface $product, string $slug): void
    {
        $product->set_slug($slug);
        $this->object_manager->flush();
    }
    #[Given('the description of product :product is :description')]
    public function description_of_product_is(Product_Interface $product, string $description): void
    {
        $product->set_description($description);
        $this->object_manager->flush();
    }
    #[Given('the meta keywords of product :product is :metaKeywords')]
    public function meta_keywords_of_product_is(Product_Interface $product, string $meta_keywords): void
    {
        $product->get_translation()->set_meta_keywords($meta_keywords);
        $this->object_manager->flush();
    }
    #[Given('the short description of product :product is :shortDescription')]
    public function short_description_of_product_is(Product_Interface $product, string $short_description): void
    {
        $product->get_translation()->set_short_description($short_description);
        $this->object_manager->flush();
    }
    #[Given('the product :product has original price :originalPrice')]
    public function the_product_has_original_price(Product_Interface $product, string $original_price): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->default_variant_resolver->get_variant($product);
        /** @var ChannelPricingInterface $channelPricing */
        $channel_pricing = $product_variant->get_channel_pricings()->first();
        $channel_pricing->set_original_price($this->get_price_from_string($original_price));
        $this->object_manager->flush();
    }
    #[Given('the product :product has option :productOption named :optionValue with code :optionCode')]
    public function product_has_option(Product_Interface $product, Product_Option $product_option, string $option_value, string $option_code): void
    {
        /** @var ProductOptionValueInterface $productOptionValue */
        $product_option_value = $this->product_option_value_factory->create_new();
        $product_option_value->set_code($option_code);
        $product_option_value->set_option($product_option);
        $product_option_value->set_value($option_value);
        $product_option->add_value($product_option_value);
        $product->add_option($product_option);
        $this->object_manager->flush();
    }
    #[Given('the product :product has :productVariantName variant with code :code, price :price, current stock :currentStock')]
    public function product_has_variant(Product_Interface $product, string $product_variant_name, string $code, string $price, int $current_stock): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->shared_storage->get('channel');
        $price_value = $this->get_price_from_string($price);
        $this->create_product_variant($product, $product_variant_name, $price_value, $code, $channel, null, true, $current_stock);
    }
    #[Given('/^the ("[^"]+" product variant) has original price at ("[^"]+")$/')]
    public function product_variant_has_original_price(Product_Variant_Interface $product_variant, int $price): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->shared_storage->get('channel');
        $product_variant->get_channel_pricing_for_channel($channel)->set_original_price($price);
        $this->object_manager->flush();
    }
    #[Given('the store has a product :productName in channel :channel')]
    #[Given('the store also has a product :productName in channel :channel')]
    public function the_store_has_a_product_with_channel(string $product_name, Channel_Interface $channel): void
    {
        $product = $this->create_product($product_name, 0, $channel);
        $this->save_product($product);
    }
    #[Given('/^the ("[^"]+" product variant) is enabled$/')]
    public function the_product_variant_is_enabled(Product_Variant_Interface $product_variant): void
    {
        $product_variant->set_enabled(true);
        $this->object_manager->flush();
    }
    #[Given('/^the ("([^"]*)" product variant) is disabled$/')]
    public function the_product_variant_is_disabled(Product_Variant_Interface $product_variant): void
    {
        $product_variant->set_enabled(false);
        $this->object_manager->flush();
    }
    #[Given('/^the ("([^"]*)" product) is enabled$/')]
    public function the_product_is_enabled(Product_Interface $product): void
    {
        $product->set_enabled(true);
        Assert::count($product->get_variants(), 1);
        /** @var ProductVariantInterface $variant */
        $variant = $product->get_variants()->first();
        $variant->set_enabled(true);
        $this->object_manager->flush();
    }
    #[Given('/^the ("([^"]*)" product) is disabled$/')]
    public function the_product_is_disabled(Product_Interface $product): void
    {
        $product->set_enabled(false);
        Assert::count($product->get_variants(), 1);
        /** @var ProductVariantInterface $variant */
        $variant = $product->get_variants()->first();
        $variant->set_enabled(false);
        $this->object_manager->flush();
    }
    #[Given('/^(products "[^"]+" and "[^"]+") are disabled$/')]
    public function products_are_disabled(array $products): void
    {
        foreach ($products as $product) {
            $this->the_product_is_disabled($product);
        }
    }
    #[Given('/^all (the product) variants with the "([^"]*)" ([^\s]+) are disabled$/')]
    public function all_the_product_variants_with_the_color_are_disabled(Product_Interface $product, string $option_value, string $option_name): void
    {
        foreach ($product->get_variants() as $variant) {
            foreach ($variant->get_option_values() as $variant_option_value) {
                if ($variant_option_value->get_value() === $option_value && $variant_option_value->get_option()->get_code() === String_Inflector::name_to_uppercase_code($option_name)) {
                    $variant->set_enabled(false);
                }
            }
        }
        $this->object_manager->flush();
    }
    #[Given('/^the ("[^"]*" \w+ \/ "[^"]*" \w+ variant of product "[^"]*") is disabled$/')]
    #[Given('/^(this variant) has been disabled$/')]
    public function the_size_color_variant_of_this_product_is_disabled(Product_Variant_Interface $product_variant): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->product_variant_repository->find($product_variant->get_id());
        $product_variant->set_enabled(false);
        $this->object_manager->flush();
    }
    #[Given('/^all variants of (this product) are disabled$/')]
    public function all_variants_of_this_product_are_disabled(Product_Interface $product): void
    {
        foreach ($product->get_variants() as $variant) {
            $variant->set_enabled(false);
        }
        $this->object_manager->flush();
    }
    #[Given('/^(this product) is available in ("[^"]+" channel) and ("[^"]+" channel)$/')]
    public function this_product_is_available_in_channels(Product_Interface $product, Channel_Interface ...$channels): void
    {
        foreach ($channels as $channel) {
            $product->add_channel($channel);
        }
        $this->save_product($product);
    }
    #[Given('/^(this product) is not available in ("[^"]+" channel)$/')]
    public function this_product_is_not_available_in_channel(Product_Interface $product, Channel_Interface $channel): void
    {
        $product->remove_channel($channel);
        $this->save_product($product);
    }
    #[Given('/^(this product) is configured with the option matching selection method$/')]
    public function this_product_is_configured_with_the_option_matching_selection_method(Product_Interface $product): void
    {
        $product->set_variant_selection_method(Product_Interface::VARIANT_SELECTION_MATCH);
        $this->save_product($product);
    }
    #[Given('/^(this product) has all possible variants priced at ("[^"]+") with indexed names$/')]
    public function this_product_has_all_possible_variants_priced_at_with_indexed_names(Product_Interface $product, int $price): void
    {
        try {
            foreach ($product->get_variants() as $product_variant) {
                $product->remove_variant($product_variant);
            }
            $this->product_variant_generator->generate($product);
        } catch (\Exception) {
            /** @var ProductVariantInterface $productVariant */
            $product_variant = $this->product_variant_factory->create_new();
            $product->add_variant($product_variant);
        }
        $i = 0;
        /** @var ProductVariantInterface $productVariant */
        foreach ($product->get_variants() as $product_variant) {
            $product_variant->set_code(sprintf('%s-variant-%d', $product->get_code(), $i));
            $product_variant->set_name(sprintf('%s variant %d', $product->get_name(), $i));
            foreach ($product->get_channels() as $channel) {
                $product_variant->add_channel_pricing($this->create_channel_pricing_for_channel($price, $channel));
            }
            ++$i;
        }
        $this->object_manager->flush();
    }
    #[Given('/^the ("[^"]+" product) is now priced at ("[^"]+") and originally priced at ("[^"]+")$/')]
    public function the_product_is_priced_at_and_originally_priced_at(Product_Interface $product, int $price, int $original_price): void
    {
        $channel_pricing = $this->get_channel_pricing_from_product($product);
        $channel_pricing->set_price($price);
        $channel_pricing->set_original_price($original_price);
        $this->save_product($product);
    }
    #[Given('/^the (product "[^"]+") has a "([^"]+)" variant priced at ("[^"]+") and originally priced at ("[^"]+")$/')]
    public function the_product_has_variant_priced_at_and_originally_priced_at(Product_Interface $product, string $product_variant_name, int $price, int $original_price): void
    {
        /** @var ChannelPricingInterface $channelPricing */
        $channel_pricing = $this->channel_pricing_factory->create_new();
        $channel_pricing->set_price($price);
        $channel_pricing->set_original_price($original_price);
        $channel_pricing->set_channel_code($this->shared_storage->get('channel')->get_code());
        /** @var ProductVariantInterface $variant */
        $variant = $this->product_variant_factory->create_new();
        $variant->set_name($product_variant_name);
        $variant->set_code(String_Inflector::name_to_uppercase_code($product_variant_name));
        $variant->set_product($product);
        $variant->set_on_hand(0);
        $variant->add_channel_pricing($channel_pricing);
        $variant->set_shipping_required(true);
        $product->set_variant_selection_method(Product_Interface::VARIANT_SELECTION_CHOICE);
        $product->add_variant($variant);
        $this->save_product($product);
    }
    #[Given('/^(this product)\'s price changed to ("[^"]+")$/')]
    public function this_products_price_changed_to(Product_Interface $product, int $price): void
    {
        $channel_pricing = $this->get_channel_pricing_from_product($product);
        $channel_pricing->set_price($price);
        $this->save_product($product);
    }
    #[Given('/^(this product)\'s price changed to ("[^"]+") and original price changed to ("[^"]+")$/')]
    public function this_products_price_changed_to_and_original_price_changed_to(Product_Interface $product, int $price, int $original_price): void
    {
        $channel_pricing = $this->get_channel_pricing_from_product($product);
        $channel_pricing->set_price($price);
        $channel_pricing->set_original_price($original_price);
        $this->save_product($product);
    }
    #[Given('/^(this variant)\'s price changed to ("[^"]+") and original price changed to ("[^"]+")$/')]
    public function this_variants_price_changed_to_and_original_price_changed_to(Product_Variant_Interface $product_variant, int $price, int $original_price): void
    {
        $channel_pricing = $this->get_channel_pricing_from_variant($product_variant);
        $channel_pricing->set_price($price);
        $channel_pricing->set_original_price($original_price);
        /** @var ProductInterface $product */
        $product = $product_variant->get_product();
        $this->save_product($product);
    }
    #[Given('/^(this product)\'s price changed to ("[^"]+") and original price was removed$/')]
    public function this_products_price_changed_to_and_original_price_was_removed(Product_Interface $product, ?int $price): void
    {
        $channel_pricing = $this->get_channel_pricing_from_product($product);
        $channel_pricing->set_price($price);
        $channel_pricing->set_original_price(null);
        $this->save_product($product);
    }
    private function get_channel_pricing_from_product(Product_Interface $product): Channel_Pricing_Interface
    {
        $variant = $this->default_variant_resolver->get_variant($product);
        Assert::not_null($variant);
        return $this->get_channel_pricing_from_variant($variant);
    }
    private function get_channel_pricing_from_variant(Product_Variant_Interface $product_variant): Channel_Pricing_Interface
    {
        $channel_pricing = $product_variant->get_channel_pricings()->first();
        Assert::is_instance_of($channel_pricing, Channel_Pricing_Interface::class);
        return $channel_pricing;
    }
    #[Given('/^(this product) has no slug in the ("[^"]+" locale)$/')]
    public function this_product_has_no_slug_in_the_locale(Product_Interface $product, string $locale_code): void
    {
        $product_translation = $product->get_translation($locale_code);
        $product_translation->set_slug('');
        $this->save_product($product);
    }
    #[Given('/^(this product) has no translations with a defined slug$/')]
    public function this_product_has_no_translations_with_a_defined_slug(Product_Interface $product): void
    {
        /** @var ProductTranslationInterface $productTranslation */
        foreach ($product->get_translations() as $product_translation) {
            $product_translation->set_slug('');
        }
        $this->save_product($product);
    }
    private function get_price_from_string(string $price): int
    {
        return (int) round((float) str_replace(['€', '£', '$'], '', $price) * 100, 2);
    }
    private function create_product(string $product_name, int $price = 100, ?Channel_Interface $channel = null): Product_Interface
    {
        if (null === $channel && $this->shared_storage->has('channel')) {
            $channel = $this->shared_storage->get('channel');
        }
        /** @var ProductInterface $product */
        $product = $this->product_factory->create_with_variant();
        $product->set_code(String_Inflector::name_to_uppercase_code($product_name));
        $product->set_name($product_name);
        $product->set_slug($this->slug_generator->generate($product_name));
        if (null !== $channel) {
            $product->add_channel($channel);
            foreach ($channel->get_locales() as $locale) {
                $product->set_fallback_locale($locale->get_code());
                $product->set_current_locale($locale->get_code());
                $product->set_name($product_name);
                $product->set_slug($this->slug_generator->generate($product_name));
            }
        }
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $product->get_enabled_variants()->first();
        if (null !== $channel) {
            $product_variant->add_channel_pricing($this->create_channel_pricing_for_channel($price, $channel));
        }
        $product_variant->set_code($product->get_code());
        $product_variant->set_name($product->get_name());
        return $product;
    }
    /**
     * @param string $value
     *
     * @return ProductOptionValueInterface
     */
    private function add_product_option(Product_Option_Interface $option, ?string $value, string $code)
    {
        /** @var ProductOptionValueInterface $optionValue */
        $option_value = $this->product_option_value_factory->create_new();
        $option_value->set_value($value);
        $option_value->set_code($code);
        $option_value->set_option($option);
        $option->add_value($option_value);
        return $option_value;
    }
    private function save_product(Product_Interface $product): void
    {
        $this->product_repository->add($product);
        $this->event_bus->dispatch(new Product_Updated($product->get_code()));
        $this->shared_storage->set('variant', $product->get_variants()->first());
        $this->shared_storage->set('product', $product);
    }
    /**
     * @return NodeElement
     */
    private function get_parameter(string $name)
    {
        return $this->mink_parameters[$name] ?? null;
    }
    /**
     * @param string $productVariantName
     * @param string $code
     * @param int $position
     *
     * @return ProductVariantInterface
     */
    private function create_product_variant(Product_Interface $product, ?string $product_variant_name, int $price, $code, ?Channel_Interface $channel = null, $position = null, bool $shipping_required = true, int $current_stock = 0, ?Product_Option_Value_Interface $option_value = null)
    {
        $product->set_variant_selection_method(Product_Interface::VARIANT_SELECTION_CHOICE);
        /** @var ProductVariantInterface $variant */
        $variant = $this->product_variant_factory->create_new();
        $variant->set_name($product_variant_name);
        $variant->set_code($code);
        $variant->set_product($product);
        $variant->set_on_hand($current_stock);
        $variant->add_channel_pricing($this->create_channel_pricing_for_channel($price, $channel));
        $variant->set_position(null === $position ? null : (int) $position);
        $variant->set_shipping_required($shipping_required);
        if (null !== $option_value) {
            $variant->add_option_value($option_value);
        }
        $product->add_variant($variant);
        $this->object_manager->flush();
        $this->shared_storage->set('variant', $variant);
        return $variant;
    }
    private function create_product_variant_with_option(Product_Interface $product, string $option_name, string $option_value_name, int $price): void
    {
        $variant = $this->product_variant_factory->create_new();
        $option_value = $this->shared_storage->get(sprintf('%s_option_%s_value', $option_value_name, $option_name));
        $variant->add_option_value($option_value);
        $variant->add_channel_pricing($this->create_channel_pricing_for_channel($price, $this->shared_storage->get('channel')));
        $variant->set_code(sprintf('%s_%s', $product->get_code(), $option_value_name));
        $variant->set_name($product->get_name());
        $product->add_variant($variant);
        $this->object_manager->flush();
    }
    /**
     * @param string $name
     * @param string $locale
     */
    private function add_product_translation(Product_Interface $product, ?string $name, $locale): void
    {
        /** @var ProductTranslationInterface $translation */
        $translation = $product->get_translation($locale);
        if ($translation->get_locale() !== $locale) {
            /** @var ProductTranslationInterface $translation */
            $translation = $this->product_translation_factory->create_new();
        }
        $translation->set_locale($locale);
        $translation->set_name($name);
        $translation->set_slug($this->slug_generator->generate($name));
        $product->add_translation($translation);
    }
    /**
     * @param string $name
     * @param string $locale
     */
    private function add_product_variant_translation(Product_Variant_Interface $product_variant, int|string $name, $locale): void
    {
        /** @var ProductVariantTranslationInterface $translation */
        $translation = $this->product_variant_translation_factory->create_new();
        $translation->set_locale($locale);
        $translation->set_name($name);
        $product_variant->add_translation($translation);
    }
    private function create_channel_pricing_for_channel(int $price, ?Channel_Interface $channel = null): Channel_Pricing_Interface
    {
        /** @var ChannelPricingInterface $channelPricing */
        $channel_pricing = $this->channel_pricing_factory->create_new();
        $channel_pricing->set_price($price);
        $channel_pricing->set_channel_code($channel->get_code());
        return $channel_pricing;
    }
    private function add_option_to_product(Product_Interface $product, string $option_name, array $values): void
    {
        /** @var ProductOptionInterface $option */
        $option = $this->product_option_factory->create_new();
        $option->set_name($option_name);
        $option->set_code(String_Inflector::name_to_uppercase_code($option_name));
        $this->shared_storage->set(sprintf('%s_option', String_Inflector::name_to_lowercase_code($option_name)), $option);
        foreach ($values as $value) {
            $option_value = $this->add_product_option($option, $value, String_Inflector::name_to_code($value));
            $this->shared_storage->set(sprintf('%s_option_%s_value', $value, strtolower($option_name)), $option_value);
        }
        $product->add_option($option);
        $product->set_variant_selection_method(Product_Interface::VARIANT_SELECTION_MATCH);
        $this->object_manager->persist($option);
        $this->object_manager->flush();
    }
    private function create_product_image(Product_Interface $product, string $image_path, string $image_type, ?Product_Variant_Interface $variant = null, ?int $position = 0): void
    {
        $files_path = $this->get_parameter('files_path');
        /** @var ProductImageInterface $productImage */
        $product_image = $this->product_image_factory->create_new();
        $product_image->set_file(new Uploaded_File($files_path . $image_path, basename($image_path)));
        $product_image->set_type($image_type);
        $product_image->set_position($position);
        if (null !== $variant) {
            $product_image->add_product_variant($variant);
        }
        $this->image_uploader->upload($product_image);
        $product->add_image($product_image);
        $this->object_manager->persist($product);
        $this->object_manager->flush();
    }
    private function create_product_taxon(Taxon_Interface $taxon, Product_Interface $product, ?int $position = null): Product_Taxon_Interface
    {
        /** @var ProductTaxonInterface $productTaxon */
        $product_taxon = $this->product_taxon_factory->create_new();
        $product_taxon->set_product($product);
        $product_taxon->set_taxon($taxon);
        if (null !== $position) {
            $product_taxon->set_position($position);
        }
        return $product_taxon;
    }
    private function update_on_hand(Product_Interface $product, int $on_hand, ?bool $tracked = null): void
    {
        /** @var ProductVariantInterface $productVariant */
        $product_variant = $this->default_variant_resolver->get_variant($product);
        $product_variant->set_on_hand($on_hand);
        if ($tracked !== null) {
            $product_variant->set_tracked($tracked);
        }
        $this->object_manager->flush();
    }
}
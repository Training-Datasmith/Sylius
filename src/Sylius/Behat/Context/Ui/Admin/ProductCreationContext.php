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
use Behat\Step\When;
use Sylius\Behat\Element\Admin\Product\Channel_Pricings_Form_Element_Interface;
use Sylius\Behat\Element\Admin\Product\Taxonomy_Form_Element_Interface;
use Sylius\Behat\Element\Admin\Product\Translations_Form_Element_Interface;
use Sylius\Behat\Page\Admin\Product\Create_Simple_Product_Page_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
final readonly class Product_Creation_Context implements Context
{
    public function __construct(private Create_Simple_Product_Page_Interface $create_page, private Translations_Form_Element_Interface $product_translations_form_element, private Channel_Pricings_Form_Element_Interface $product_channel_pricings_form_element, private Taxonomy_Form_Element_Interface $product_taxonomy_form_element)
    {
    }
    #[When('/^I create a new simple product ("[^"]+") priced at "(?:€|£|\$)([^"]+)" with ("[^"]+" taxon) in the ("[^"]+" channel)$/')]
    public function i_create_a_new_simple_product_priced_at_with_taxon_in_the_channel(string $name, string $price, Taxon_Interface $taxon, Channel_Interface $channel): void
    {
        $locale_code = $channel->get_default_locale()->get_code();
        $this->create_page->open();
        $this->product_translations_form_element->name_it_in(str_replace('"', '', $name), $locale_code);
        $this->product_translations_form_element->specify_slug_in(String_Inflector::name_to_slug($name), $locale_code);
        $this->create_page->specify_code(str_replace('"', '', String_Inflector::name_to_uppercase_code($name)));
        $this->product_channel_pricings_form_element->specify_price($channel, $price);
        $this->create_page->check_channel($channel->get_code());
        $this->product_taxonomy_form_element->select_main_taxon($taxon->get_name());
        $this->product_taxonomy_form_element->check_product_taxon($taxon);
        $this->create_page->create();
    }
}
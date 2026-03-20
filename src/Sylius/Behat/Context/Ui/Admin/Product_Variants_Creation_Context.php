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
use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
final readonly class Product_Variants_Creation_Context implements Context
{
    public function __construct(private Create_Page_Interface $create_page)
    {
    }
    #[When('/^I create a new "([^"]+)" variant priced at "(?:€|£|\$)([^"]+)" for ("[^"]+" product) in the ("[^"]+" channel)$/')]
    public function i_create_a_new_variant_priced_at_for_product_in_the_channel(string $name, string $price, Product_Interface $product, Channel_Interface $channel): void
    {
        $this->create_page->open(['productId' => $product->get_id()]);
        $this->create_page->specify_code(str_replace('"', '', String_Inflector::name_to_uppercase_code($name)));
        $this->create_page->name_it_in($name, $channel->get_default_locale()->get_code());
        $this->create_page->specify_price($price, $channel);
        $this->create_page->create();
    }
}
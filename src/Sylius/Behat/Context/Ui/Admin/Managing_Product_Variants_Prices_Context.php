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
use Sylius\Behat\Page\Admin\Product_Variant\Update_Page_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
final readonly class Managing_Product_Variants_Prices_Context implements Context
{
    public function __construct(private Update_Page_Interface $update_page)
    {
    }
    #[When('/^I change the price of the ("[^"]+" product variant) to "(?:€|£|\$)([^"]+)" in ("[^"]+" channel)$/')]
    public function i_change_the_price_of_the_product_variant_in_channel(Product_Variant_Interface $variant, int $price, Channel_Interface $channel): void
    {
        $this->update_page->open(['productId' => $variant->get_product()->get_id(), 'id' => $variant->get_id()]);
        $this->update_page->specify_price($price, $channel);
        $this->update_page->save_changes();
    }
    #[When('/^I change the original price of the ("[^"]+" product variant) to "(?:€|£|\$)([^"]+)" in ("[^"]+" channel)$/')]
    public function i_change_the_original_price_of_the_product_variant_in_channel(Product_Variant_Interface $variant, int $original_price, Channel_Interface $channel): void
    {
        $this->update_page->open(['productId' => $variant->get_product()->get_id(), 'id' => $variant->get_id()]);
        $this->update_page->specify_original_price($original_price, $channel);
        $this->update_page->save_changes();
    }
    #[When('/^I remove the original price of the ("[^"]+" product variant) in ("[^"]+" channel)$/')]
    public function i_remove_the_original_price_of_the_product_variant_in_channel(Product_Variant_Interface $variant, Channel_Interface $channel): void
    {
        $this->update_page->open(['productId' => $variant->get_product()->get_id(), 'id' => $variant->get_id()]);
        $this->update_page->specify_original_price(null, $channel);
        $this->update_page->save_changes();
    }
}
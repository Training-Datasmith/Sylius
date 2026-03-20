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
namespace Sylius\Behat\Context\Hybrid\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\When;
use Sylius\Behat\Context\Api\Shop\Cart_Context as ApiShopCartContext;
use Sylius\Behat\Context\Ui\Shop\Cart_Context as UiCartContext;
use Sylius\Component\Core\Model\Product_Interface;
class Cart_Context implements Context
{
    public function __construct(private readonly Api_Shop_Cart_Context $api_cart_context, private readonly Ui_Cart_Context $ui_cart_context)
    {
    }
    #[When('I add :product to the cart on the web store')]
    public function i_add_product_to_the_cart_on_the_web_store(Product_Interface $product): void
    {
        $this->ui_cart_context->i_add_product_to_the_cart($product);
    }
    #[When('I check items in my cart using API')]
    public function i_check_items_in_my_cart_using_api(): void
    {
        $this->api_cart_context->i_pick_up_my_cart();
    }
}
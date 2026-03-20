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
use Sylius\Behat\Page\Shop\Product\Show_Page_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Webmozart\Assert\Assert;
final readonly class Browsing_Product_Context implements Context
{
    public function __construct(private Show_Page_Interface $show_page)
    {
    }
    #[Then('/^I should see (this product) in the ("([^"]*)" channel) in the shop$/')]
    public function i_should_see_this_product_in_the_channel_in_shop(Product_Interface $product, Channel_Interface $channel): void
    {
        Assert::true(null !== strpos($this->show_page->get_current_url(), (string) $channel->get_hostname()));
        Assert::same($this->show_page->get_name(), $product->get_name());
    }
}
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
namespace Sylius\Behat\Page\Admin\Product;

use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface as BaseCreatePageInterface;
use Sylius\Behat\Page\Admin\Show_Page_Button_Checker_Interface;
interface Create_Simple_Product_Page_Interface extends Base_Create_Page_Interface, Show_Page_Button_Checker_Interface
{
    public function check_channel(string $channel_code): void;
    public function specify_code(string $code): void;
    public function select_shipping_category(string $shipping_category_name): void;
    public function set_shipping_required(bool $is_shipping_required): void;
    public function cancel_changes(): void;
}
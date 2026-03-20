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
namespace Sylius\Behat\Element\Admin\Product;

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
use Sylius\Component\Core\Model\Channel_Interface;
interface Channel_Pricings_Form_Element_Interface extends Base_Form_Element_Interface
{
    public function specify_price(Channel_Interface $channel, string $price): void;
    public function specify_original_price(Channel_Interface $channel, int $original_price): void;
    public function get_price_for_channel(Channel_Interface $channel): string;
    public function get_original_price_for_channel(Channel_Interface $channel): string;
    public function has_no_price_for_channel(string $channel_name): bool;
}
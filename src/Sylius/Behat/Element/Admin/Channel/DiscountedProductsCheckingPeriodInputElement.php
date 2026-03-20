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
namespace Sylius\Behat\Element\Admin\Channel;

use Sylius\Behat\Element\Sylius_Element;
class Discounted_Products_Checking_Period_Input_Element extends Sylius_Element implements Discounted_Products_Checking_Period_Input_Element_Interface
{
    public function specify_period(int $period): void
    {
        $this->get_element('discounted_products_checking_period')->set_value($period);
    }
    public function get_period(): int
    {
        return (int) $this->get_element('discounted_products_checking_period')->get_value();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['discounted_products_checking_period' => '#sylius_admin_channel_channelPriceHistoryConfig_lowestPriceForDiscountedProductsCheckingPeriod']);
    }
}
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
namespace Sylius\Behat\Page\Admin\Exchange_Rate;

use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function choose_currency_filter(string $currency_name): void
    {
        $this->get_element('filter_currency')->select_option($currency_name);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['filter_currency' => '#criteria_currency']);
    }
}
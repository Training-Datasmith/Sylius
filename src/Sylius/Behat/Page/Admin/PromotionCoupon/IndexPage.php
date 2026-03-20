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
namespace Sylius\Behat\Page\Admin\Promotion_Coupon;

use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function filter_by_code(string $code): void
    {
        $this->get_element('code_filter')->set_value($code);
    }
    public function get_used_number(string $promotion_coupon_code): int
    {
        $used = $this->get_cell_for_resource('used', ['code' => $promotion_coupon_code]);
        return (int) $used->find('css', '[data-test-used]')->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code_filter' => '#criteria_code_value']);
    }
}
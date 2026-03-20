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
namespace Sylius\Behat\Page\Admin\Promotion;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Sylius\Component\Promotion\Model\Promotion_Interface;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function get_usage_number(Promotion_Interface $promotion): int
    {
        $usage = $this->get_promotion_fields_with_header($promotion, 'usage');
        return (int) $usage->find('css', '[data-test-used]')->get_text();
    }
    public function is_able_to_manage_coupons_for(Promotion_Interface $promotion): bool
    {
        $actions = $this->get_promotion_fields_with_header($promotion, 'actions');
        return $actions->has_link('List coupons');
    }
    public function is_coupon_based_for(Promotion_Interface $promotion): bool
    {
        $coupons = $this->get_promotion_fields_with_header($promotion, 'couponBased');
        $is_coupon_based = $coupons->find('css', '[data-test-status-enabled]');
        return $is_coupon_based !== null;
    }
    public function specify_filter_type(string $field, string $type): void
    {
        $this->get_document()->fill_field(sprintf('criteria_%s_type', $field), $type);
    }
    public function specify_filter_value(string $field, string $value): void
    {
        $this->get_document()->fill_field(sprintf('criteria_%s_value', $field), $value);
    }
    public function choose_archival(string $is_archival): void
    {
        $this->get_element('filter_archival')->select_option($is_archival);
    }
    public function is_archival_filter_enabled(): bool
    {
        return '1' === $this->get_element('filter_archival')->get_value();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['filter_archival' => '#criteria_archival']);
    }
    protected function get_promotion_fields_with_header(Promotion_Interface $promotion, string $header): Node_Element
    {
        return $this->get_cell_for_resource($header, ['code' => $promotion->get_code()]);
    }
}
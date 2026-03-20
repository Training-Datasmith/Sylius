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

use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as BaseIndexPageInterface;
use Sylius\Component\Promotion\Model\Promotion_Interface;
interface Index_Page_Interface extends Base_Index_Page_Interface
{
    public function get_usage_number(Promotion_Interface $promotion): int;
    public function is_able_to_manage_coupons_for(Promotion_Interface $promotion): bool;
    public function is_coupon_based_for(Promotion_Interface $promotion): bool;
    public function choose_archival(string $is_archival): void;
    public function is_archival_filter_enabled(): bool;
}
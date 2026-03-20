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

use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface as BasePageInterface;
interface Generate_Page_Interface extends Base_Page_Interface
{
    public function generate(): void;
    public function specify_prefix(string $prefix): void;
    public function specify_code_length(?int $code_length): void;
    public function specify_suffix(string $suffix): void;
    public function specify_amount(?int $amount): void;
    public function set_expires_at(\DateTimeInterface $date): void;
    public function set_usage_limit(int $limit): void;
    public function get_form_validation_message(): string;
}
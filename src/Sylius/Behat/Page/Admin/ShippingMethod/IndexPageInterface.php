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
namespace Sylius\Behat\Page\Admin\Shipping_Method;

use Sylius\Behat\Page\Admin\Crud\Index_Page_Interface as BaseIndexPageInterface;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
interface Index_Page_Interface extends Base_Index_Page_Interface
{
    public function choose_archival(string $is_archival): void;
    public function is_archival_filter_enabled(): bool;
    public function archive_shipping_method(string $name): void;
    public function restore_shipping_method(string $name): void;
    public function is_shipping_method_enabled(Shipping_Method_Interface $shipping_method): bool;
    public function is_shipping_method_disabled(Shipping_Method_Interface $shipping_method): bool;
}
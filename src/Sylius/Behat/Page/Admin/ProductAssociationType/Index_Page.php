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
namespace Sylius\Behat\Page\Admin\Product_Association_Type;

use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function specify_filter_type(string $field, string $type): void
    {
        $this->get_document()->fill_field(sprintf('criteria_%s_value', $field), $type);
    }
    public function specify_filter_value(string $field, string $value): void
    {
        $this->get_document()->fill_field(sprintf('criteria_%s_value', $field), $value);
    }
}
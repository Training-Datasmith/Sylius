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
namespace Sylius\Behat\Page\Admin\Channel;

use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function get_used_theme_name(string $channel_code): ?string
    {
        $table = $this->get_document()->find('css', 'table');
        $row = $this->get_table_accessor()->get_row_with_fields($table, ['code' => $channel_code]);
        return trim((string) $this->get_table_accessor()->get_field_from_row($table, $row, 'themeName')->get_text());
    }
}
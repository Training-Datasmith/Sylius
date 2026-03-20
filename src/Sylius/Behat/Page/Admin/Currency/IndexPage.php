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
namespace Sylius\Behat\Page\Admin\Currency;

use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Sylius\Component\Currency\Model\Currency_Interface;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function is_currency_disabled(Currency_Interface $currency): bool
    {
        return $this->check_currency_status($currency, 'Disabled');
    }
    public function is_currency_enabled(Currency_Interface $currency): bool
    {
        return $this->check_currency_status($currency, 'Enabled');
    }
    /**
     * @throws \InvalidArgumentException
     */
    protected function check_currency_status(Currency_Interface $currency, string $status): bool
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $row = $table_accessor->get_row_with_fields($table, ['code' => $currency->get_code()]);
        $enabled_field = $table_accessor->get_field_from_row($table, $row, 'enabled');
        return $enabled_field->get_text() === $status;
    }
}
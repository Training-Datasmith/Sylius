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
namespace Sylius\Behat\Page\Admin\Country;

use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Sylius\Component\Addressing\Model\Country_Interface;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function is_country_disabled(Country_Interface $country): bool
    {
        return $this->check_country_status($country, 'disabled');
    }
    public function is_country_enabled(Country_Interface $country): bool
    {
        return $this->check_country_status($country, 'enabled');
    }
    protected function check_country_status(Country_Interface $country, string $status): bool
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $row = $table_accessor->get_row_with_fields($table, ['code' => $country->get_code()]);
        $enabled_field = $table_accessor->get_field_from_row($table, $row, 'enabled');
        return $enabled_field->has('css', sprintf('[data-test-status-%s]', $status));
    }
}
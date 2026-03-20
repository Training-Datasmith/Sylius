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
namespace Sylius\Behat\Element\Admin\Tax_Rate;

use Sylius\Behat\Element\Sylius_Element;
class Filter_Element extends Sylius_Element implements Filter_Element_Interface
{
    public function specify_date_from(string $date_type, string $date): void
    {
        $this->get_element(sprintf('%s_date_from', $date_type))->set_value($date);
    }
    public function specify_date_to(string $date_type, string $date): void
    {
        $this->get_element(sprintf('%s_date_to', $date_type))->set_value($date);
    }
    public function filter(): void
    {
        $this->get_element('filter')->click();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['end_date_from' => '#criteria_endDate_from_date', 'end_date_to' => '#criteria_endDate_to_date', 'filter' => 'button[type="submit"]:contains("Filter")', 'start_date_from' => '#criteria_startDate_from_date', 'start_date_to' => '#criteria_startDate_to_date']);
    }
}
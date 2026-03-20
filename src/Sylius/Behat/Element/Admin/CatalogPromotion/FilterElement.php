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
namespace Sylius\Behat\Element\Admin\Catalog_Promotion;

use Sylius\Behat\Element\Sylius_Element;
use Sylius\Component\Core\Model\Channel_Interface;
class Filter_Element extends Sylius_Element implements Filter_Element_Interface
{
    protected const BOOLEAN_FILTER_TRUE = 'Yes';
    public function choose_channel(Channel_Interface $channel): void
    {
        $this->get_element('channel')->select_option($channel->get_name());
    }
    public function choose_enabled(): void
    {
        $this->get_element('enabled')->select_option(self::BOOLEAN_FILTER_TRUE);
    }
    public function choose_state(string $state): void
    {
        $this->get_element('state')->select_option($state);
    }
    public function specify_start_date_from(string $date): void
    {
        $this->get_element('start_date_from')->set_value($date);
    }
    public function specify_start_date_to(string $date): void
    {
        $this->get_element('start_date_to')->set_value($date);
    }
    public function specify_end_date_from(string $date): void
    {
        $this->get_element('end_date_from')->set_value($date);
    }
    public function specify_end_date_to(string $date): void
    {
        $this->get_element('end_date_to')->set_value($date);
    }
    public function filter(): void
    {
        $this->get_element('filter')->click();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['channel' => '#criteria_channel', 'enabled' => '#criteria_enabled', 'end_date_from' => '#criteria_endDate_from_date', 'end_date_to' => '#criteria_endDate_to_date', 'filter' => 'button[type="submit"]:contains("Filter")', 'start_date_from' => '#criteria_startDate_from_date', 'start_date_to' => '#criteria_startDate_to_date', 'state' => '#criteria_state']);
    }
}
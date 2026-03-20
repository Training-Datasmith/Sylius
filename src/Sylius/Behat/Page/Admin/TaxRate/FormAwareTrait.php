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
namespace Sylius\Behat\Page\Admin\Tax_Rate;

use Sylius\Behat\Behaviour\Names_It;
trait Form_Aware_Trait
{
    use Names_It;
    public function choose_zone(string $name): void
    {
        $this->get_element('field_zone')->select_option($name);
    }
    public function choose_category(string $name): void
    {
        $this->get_element('field_category')->select_option($name);
    }
    public function choose_calculator(string $name): void
    {
        $this->get_element('field_calculator')->select_option($name);
    }
    public function specify_amount(string $amount): void
    {
        $this->get_element('field_amount')->set_value($amount);
    }
    public function specify_start_date(\DateTimeInterface $start_date): void
    {
        $timestamp = $start_date->get_timestamp();
        $this->get_element('field_start_date')->set_value(date('Y-m-d', $timestamp));
        $this->get_element('field_start_date_time')->set_value(date('H:i', $timestamp));
    }
    public function specify_end_date(\DateTimeInterface $end_date): void
    {
        $timestamp = $end_date->get_timestamp();
        $this->get_element('field_end_date')->set_value(date('Y-m-d', $timestamp));
        $this->get_element('field_end_date_time')->set_value(date('H:i', $timestamp));
    }
    /** @return array<string, string> */
    protected function get_defined_form_elements(): array
    {
        return ['field_amount' => '#sylius_admin_tax_rate_amount', 'field_calculator' => '#sylius_admin_tax_rate_calculator', 'field_category' => '#sylius_admin_tax_rate_category', 'field_code' => '#sylius_admin_tax_rate_code', 'field_end_date' => '#sylius_admin_tax_rate_endDate_date', 'field_end_date_time' => '#sylius_admin_tax_rate_endDate_time', 'field_included_in_price' => '#sylius_admin_tax_rate_includedInPrice', 'field_name' => '#sylius_admin_tax_rate_name', 'field_start_date' => '#sylius_admin_tax_rate_startDate_date', 'field_start_date_time' => '#sylius_admin_tax_rate_startDate_time', 'field_zone' => '#sylius_admin_tax_rate_zone'];
    }
}
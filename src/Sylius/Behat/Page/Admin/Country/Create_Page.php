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

use Sylius\Behat\Page\Admin\Crud\Create_Page as BaseCreatePage;
use Webmozart\Assert\Assert;
class Create_Page extends Base_Create_Page implements Create_Page_Interface
{
    public function select_country(string $country_name): void
    {
        $this->get_element('code')->select_option($country_name);
    }
    public function add_province(): void
    {
        $count = count($this->get_province_items());
        $this->get_element('add_province')->click();
        $this->get_document()->wait_for(5, fn(): bool => $count + 1 === count($this->get_province_items()));
    }
    public function specify_province_name(string $name): void
    {
        $province = $this->get_element('last_province');
        $province->find('css', '[data-test-province-name]')->set_value($name);
    }
    public function specify_province_code(string $code): void
    {
        $province = $this->get_element('last_province');
        $province->find('css', '[data-test-province-code]')->set_value($code);
    }
    public function specify_province_abbreviation(string $abbreviation): void
    {
        $province = $this->get_element('last_province');
        $province->find('css', '[data-test-province-abbreviation]')->set_value($abbreviation);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '[data-test-code]', 'provinces' => '[data-test-provinces]', 'last_province' => '[data-test-provinces] [data-test-province]:last-child', 'add_province' => '[data-test-add-province]']);
    }
    protected function get_province_items(): array
    {
        $items = $this->get_element('provinces')->find_all('css', '[data-test-province]');
        Assert::is_array($items);
        return $items;
    }
}
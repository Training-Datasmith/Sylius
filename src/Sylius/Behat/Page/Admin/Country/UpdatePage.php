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

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Behaviour\Toggles;
use Sylius\Behat\Page\Admin\Crud\Update_Page as BaseUpdatePage;
use Webmozart\Assert\Assert;
class Update_Page extends Base_Update_Page implements Update_Page_Interface
{
    use Toggles;
    public function is_code_field_disabled(): bool
    {
        $code_field = $this->get_element('code');
        return $code_field->get_attribute('disabled') === 'disabled';
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
    public function is_there_province(string $province_name): bool
    {
        $provinces = $this->get_element('provinces');
        return $provinces->has('css', '[value = "' . $province_name . '"]');
    }
    public function is_there_province_with_code(string $province_code): bool
    {
        $provinces = $this->get_element('provinces');
        return $provinces->has('css', '[value = "' . $province_code . '"]');
    }
    public function remove_province(string $province_name): void
    {
        if ($this->is_there_province($province_name)) {
            $province = $this->get_province_element($province_name);
            $province->find('css', '[data-test-delete-province]')->click();
            $this->get_document()->wait_for(5, fn(): false => !$this->is_there_province($province_name));
        }
    }
    public function remove_province_name(string $province_name): void
    {
        if ($this->is_there_province($province_name)) {
            $province = $this->get_province_element($province_name);
            $province->find('css', '[data-test-province-name]')->set_value('');
        }
    }
    public function get_form_validation_errors(): array
    {
        $errors = $this->get_element('form')->find_all('css', '.alert-danger');
        return array_map(fn(Node_Element $element) => $element->get_text(), $errors);
    }
    public function get_validation_message(string $element): string
    {
        $province = $this->get_element('last_province');
        $found_element = $province->find('css', '.invalid-feedback');
        if (null === $found_element) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Tag', 'css', '.invalid-feedback');
        }
        return $found_element->get_text();
    }
    protected function get_toggleable_element(): Node_Element
    {
        return $this->get_element('enabled');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '[data-test-code]', 'enabled' => '[data-test-enabled]', 'provinces' => '[data-test-provinces]', 'last_province' => '[data-test-provinces] [data-test-province]:last-child', 'add_province' => '[data-test-add-province]']);
    }
    protected function get_province_items(): array
    {
        $items = $this->get_element('provinces')->find_all('css', '[data-test-province]');
        Assert::is_array($items);
        return $items;
    }
    protected function get_province_element(string $province_name): Node_Element|null
    {
        return $this->get_document()->find('xpath', sprintf('//*[@data-test-province and .//*[contains(@value, \'%s\')]]', $province_name));
    }
}
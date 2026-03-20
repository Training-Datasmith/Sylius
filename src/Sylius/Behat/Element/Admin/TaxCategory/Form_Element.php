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
namespace Sylius\Behat\Element\Admin\Tax_Category;

use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    public function set_code(string $code): void
    {
        $this->get_element('code')->set_value($code);
    }
    public function is_code_disabled(): bool
    {
        return $this->get_element('code')->has_attribute('disabled');
    }
    public function set_name(string $name): void
    {
        $this->get_element('name')->set_value($name);
    }
    public function set_description(string $description): void
    {
        $this->get_element('description')->set_value($description);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '[data-test-code]', 'description' => '[data-test-description]', 'name' => '[data-test-name]']);
    }
}
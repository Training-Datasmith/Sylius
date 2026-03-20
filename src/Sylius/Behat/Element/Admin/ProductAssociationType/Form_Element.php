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
namespace Sylius\Behat\Element\Admin\Product_Association_Type;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    use Checks_Code_Immutability;
    public function set_code(string $code): void
    {
        $this->get_element('code')->set_value($code);
    }
    public function set_name(string $name, string $locale_code): void
    {
        $this->get_element('name', ['%locale%' => $locale_code])->set_value($name);
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '[data-test-code]', 'name' => '[data-test-name="%locale%"]']);
    }
}
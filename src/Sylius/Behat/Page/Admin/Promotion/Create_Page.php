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
namespace Sylius\Behat\Page\Admin\Promotion;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Behaviour\Names_It;
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Page\Admin\Crud\Create_Page as BaseCreatePage;
class Create_Page extends Base_Create_Page implements Create_Page_Interface
{
    use Names_It;
    use Specifies_Its_Field;
    public function get_validation_message(string $element, array $parameters = []): string
    {
        $found_element = $this->get_field_element($element);
        if (null === $found_element) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Field element');
        }
        $validation_message = $found_element->find('css', '.invalid-feedback');
        if (null === $validation_message) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Validation message', 'css', '.invalid-feedback');
        }
        return $validation_message->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '#sylius_admin_promotion_code', 'rules' => '#sylius_admin_promotion_rules']);
    }
    /** @throws ElementNotFoundException */
    protected function get_field_element(string $element, array $parameters = []): Node_Element
    {
        $element = $this->get_element($element, $parameters);
        while (null !== $element && !$element->has_class('field')) {
            $element = $element->get_parent();
        }
        return $element;
    }
}
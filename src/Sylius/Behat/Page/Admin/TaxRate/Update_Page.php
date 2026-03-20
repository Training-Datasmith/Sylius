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

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Page\Admin\Crud\Update_Page as BaseUpdatePage;
class Update_Page extends Base_Update_Page implements Update_Page_Interface
{
    use Form_Aware_Trait;
    use Checks_Code_Immutability;
    public function remove_zone(): void
    {
        $this->get_element('field_zone')->set_value('');
    }
    public function is_included_in_price(): bool
    {
        return $this->get_element('field_included_in_price')->is_checked();
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('field_code');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), $this->get_defined_form_elements());
    }
}
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
namespace Sylius\Behat\Page\Admin\Shipping_Category;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Page\Admin\Crud\Update_Page as BaseUpdatePage;
class Update_Page extends Base_Update_Page implements Update_Page_Interface
{
    use Checks_Code_Immutability;
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '[data-test-code]', 'description' => '[data-test-description]', 'name' => '[data-test-name]']);
    }
}
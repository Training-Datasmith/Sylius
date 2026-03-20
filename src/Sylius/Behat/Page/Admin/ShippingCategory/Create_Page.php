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

use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Page\Admin\Crud\Create_Page as BaseCreatePage;
class Create_Page extends Base_Create_Page implements Create_Page_Interface
{
    use Specifies_Its_Field;
    public function name_it(string $name): void
    {
        $this->get_element('name')->set_value($name);
    }
    public function specify_code(string $code): void
    {
        $this->get_element('code')->set_value($code);
    }
    public function specify_description(string $description): void
    {
        $this->get_element('description')->set_value($description);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '[data-test-code]', 'description' => '[data-test-description]', 'name' => '[data-test-name]']);
    }
}
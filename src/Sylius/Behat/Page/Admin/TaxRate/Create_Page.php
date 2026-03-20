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
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Page\Admin\Crud\Create_Page as BaseCreatePage;
class Create_Page extends Base_Create_Page implements Create_Page_Interface
{
    use Names_It;
    use Specifies_Its_Field;
    use Form_Aware_Trait;
    public function choose_included_in_price(): void
    {
        $this->get_element('field_included_in_price')->check();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), $this->get_defined_form_elements());
    }
}
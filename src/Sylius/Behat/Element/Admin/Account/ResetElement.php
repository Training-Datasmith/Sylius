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
namespace Sylius\Behat\Element\Admin\Account;

use Sylius\Behat\Element\Sylius_Element;
class Reset_Element extends Sylius_Element implements Reset_Element_Interface
{
    public function reset(): void
    {
        $this->get_element('reset')->click();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['reset' => 'button[type="submit"]:contains("Reset")']);
    }
}
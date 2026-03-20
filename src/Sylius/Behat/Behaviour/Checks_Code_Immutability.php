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
namespace Sylius\Behat\Behaviour;

use Behat\Mink\Element\Node_Element;
trait Checks_Code_Immutability
{
    abstract protected function get_code_element(): Node_Element;
    public function is_code_disabled(): bool
    {
        return 'disabled' === $this->get_code_element()->get_attribute('disabled');
    }
}
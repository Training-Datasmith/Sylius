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
namespace Sylius\Behat\Service\Accessor;

use Behat\Mink\Element\Node_Element;
interface Notification_Accessor_Interface
{
    /**
     * @return array|NodeElement[]
     */
    public function get_message_elements(): array;
}
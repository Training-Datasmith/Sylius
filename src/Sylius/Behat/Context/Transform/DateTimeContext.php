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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
final class Date_Time_Context implements Context
{
    #[Transform(':date')]
    #[Transform(':startsDate')]
    #[Transform(':endsDate')]
    #[Transform('/^on "([^"]+)"$/')]
    public function get_date($date): \DateTime
    {
        return new \DateTime($date);
    }
}
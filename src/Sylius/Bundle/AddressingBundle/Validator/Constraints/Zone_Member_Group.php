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
namespace Sylius\Bundle\Addressing_Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
#[\Attribute]
final class Zone_Member_Group extends Constraint
{
    public function validated_by(): string
    {
        return 'sylius_zone_member_group';
    }
    public function get_targets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
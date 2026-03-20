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

use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Component\Addressing\Model\Zone_Member_Interface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraint_Validator;
use Webmozart\Assert\Assert;
final class Zone_Cannot_Contain_Itself_Validator extends Constraint_Validator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }
        /** @var ZoneCannotContainItself $constraint */
        Assert::is_instance_of($constraint, Zone_Cannot_Contain_Itself::class);
        /** @var ZoneMemberInterface $zoneMember */
        foreach ($value as $zone_member) {
            $zone = $zone_member->get_belongs_to();
            if ($zone->get_type() !== Zone_Interface::TYPE_ZONE) {
                continue;
            }
            if ($zone_member->get_code() === $zone->get_code()) {
                $this->context->add_violation($constraint->message);
            }
        }
    }
}
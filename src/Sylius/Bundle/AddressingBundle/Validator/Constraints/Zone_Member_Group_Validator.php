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

use Sylius\Component\Addressing\Model\Zone_Member_Interface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraint_Validator;
use Symfony\Component\Validator\Exception\Unexpected_Type_Exception;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
final class Zone_Member_Group_Validator extends Constraint_Validator
{
    /** @param array<string, array<array-key, string>> $validationGroups */
    public function __construct(private readonly array $validation_groups)
    {
    }
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Zone_Member_Group) {
            throw new Unexpected_Type_Exception($constraint, Zone_Member_Group::class);
        }
        if (!$value instanceof Zone_Member_Interface) {
            throw new UnexpectedValueException($value, Zone_Member_Interface::class);
        }
        /** @var string[] $groups */
        $groups = $this->validation_groups[$value->get_belongs_to()?->get_type()] ?? $constraint->groups;
        $validator = $this->context->get_validator()->in_context($this->context);
        $validator->validate(value: $value, groups: $groups);
    }
}
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

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Addressing\Model\Province_Interface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraint_Validator;
use Webmozart\Assert\Assert;
final class Unique_Province_Collection_Validator extends Constraint_Validator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var Collection<array-key, ProvinceInterface> $value */
        Assert::all_is_instance_of($value, Province_Interface::class);
        /** @var UniqueProvinceCollection $constraint */
        Assert::is_instance_of($constraint, Unique_Province_Collection::class);
        if ($value->is_empty()) {
            return;
        }
        $provinces_with_any_required_data = $value->filter(fn(Province_Interface $province): bool => null !== $province->get_code() || null !== $province->get_name());
        $codes = [];
        $names = [];
        foreach ($provinces_with_any_required_data as $province) {
            $code = $province->get_code();
            $name = $province->get_name();
            if (isset($code) && in_array($code, $codes) || isset($name) && in_array($name, $names)) {
                $this->context->add_violation($constraint->message);
                return;
            }
            $codes[] = $code;
            $names[] = $name;
        }
    }
}
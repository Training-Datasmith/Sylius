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

use Sylius\Component\Addressing\Model\Address_Interface;
use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraint_Validator;
use Webmozart\Assert\Assert;
class Province_Address_Constraint_Validator extends Constraint_Validator
{
    /**
     * @param RepositoryInterface<CountryInterface> $countryRepository
     * @param RepositoryInterface<ProvinceInterface> $provinceRepository
     */
    public function __construct(private readonly Repository_Interface $country_repository, private readonly Repository_Interface $province_repository)
    {
    }
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof Address_Interface) {
            throw new \InvalidArgumentException('ProvinceAddressConstraintValidator can only validate instances of "Sylius\Component\Addressing\Model\AddressInterface"');
        }
        /** @var ProvinceAddressConstraint $constraint */
        Assert::is_instance_of($constraint, Province_Address_Constraint::class);
        $property_path = $this->context->get_property_path();
        foreach (iterator_to_array($this->context->get_violations()) as $violation) {
            if (str_starts_with((string) $violation->get_property_path(), (string) $property_path)) {
                return;
            }
        }
        if (!$this->is_province_valid($value)) {
            $this->context->build_violation($constraint->message)->at_path('provinceCode')->add_violation();
        }
    }
    protected function is_province_valid(Address_Interface $address): bool
    {
        $country_code = $address->get_country_code();
        /** @var CountryInterface|null $country */
        $country = $this->country_repository->find_one_by(['code' => $country_code]);
        if (null === $country) {
            return true;
        }
        if (!$country->has_provinces() && null !== $address->get_province_code()) {
            return false;
        }
        if (!$country->has_provinces()) {
            return true;
        }
        if (null === $address->get_province_code()) {
            return false;
        }
        /** @var ProvinceInterface|null $province */
        $province = $this->province_repository->find_one_by(['code' => $address->get_province_code()]);
        if (null === $province) {
            return false;
        }
        return $country->has_province($province);
    }
}
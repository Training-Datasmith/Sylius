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
namespace Sylius\Bundle\Addressing_Bundle\Form\Type;

use Sylius\Component\Addressing\Model\Scope as AddressingScope;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Choice_Type;
use Symfony\Component\Options_Resolver\Options;
use Symfony\Component\Options_Resolver\Options_Resolver;
use Webmozart\Assert\Assert;
final class Zone_Choice_Type extends Abstract_Type
{
    /**
     * @param RepositoryInterface<ZoneInterface> $zoneRepository
     * @param array<string, mixed> $scopeTypes
     */
    public function __construct(private readonly Repository_Interface $zone_repository, private readonly array $scope_types)
    {
        Assert::not_empty($this->scope_types, 'There should be at least one scope type.');
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_defaults(['choices' => function (Options $options): iterable {
            $zone_criteria = [];
            if ($options['zone_scope'] !== Addressing_Scope::ALL) {
                $zone_criteria['scope'] = [$options['zone_scope'], Addressing_Scope::ALL];
            }
            return $this->zone_repository->find_by($zone_criteria);
        }, 'choice_value' => 'code', 'choice_label' => 'name', 'choice_translation_domain' => false, 'label' => 'sylius.form.address.zone', 'placeholder' => 'sylius.form.zone.select', 'zone_scope' => Addressing_Scope::ALL]);
        $resolver->set_allowed_values('zone_scope', array_keys($this->scope_types));
    }
    public function get_parent(): string
    {
        return Choice_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_zone_choice';
    }
}
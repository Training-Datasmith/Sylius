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

use Sylius\Bundle\Resource_Bundle\Form\Data_Transformer\Resource_To_Identifier_Transformer;
use Sylius\Component\Addressing\Model\Zone_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Choice_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Reversed_Transformer;
use Symfony\Component\Options_Resolver\Options;
use Symfony\Component\Options_Resolver\Options_Resolver;
final class Zone_Code_Choice_Type extends Abstract_Type
{
    /** @param RepositoryInterface<ZoneInterface> $zoneRepository */
    public function __construct(private readonly Repository_Interface $zone_repository)
    {
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add_model_transformer(new Reversed_Transformer(new Resource_To_Identifier_Transformer($this->zone_repository, 'code')));
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_defaults(['choice_filter' => null, 'choices' => function (Options $options): iterable {
            $zones = $this->zone_repository->find_all();
            if ($options['choice_filter']) {
                return array_filter($zones, $options['choice_filter']);
            }
            return $zones;
        }, 'choice_value' => 'code', 'choice_label' => 'name', 'choice_translation_domain' => false, 'label' => 'sylius.form.zone.types.zone', 'placeholder' => 'sylius.form.zone.select']);
    }
    public function get_parent(): string
    {
        return Choice_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_zone_code_choice';
    }
}